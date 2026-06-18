<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private string $abacateBase;
    private string $abacateToken;

    public function __construct()
    {
        $this->abacateBase  = 'https://api.abacatepay.com/v2';
        $this->abacateToken = config('services.abacatepay.key');
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withoutVerifying()->withToken($this->abacateToken);
    }

    private function limparBase64(string $base64): string
    {
        return str_replace('data:image/png;base64,', '', $base64);
    }

    public function myPayments(Request $request)
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->where('status', 'pending_payment')
            ->with(['property:id,property_title,pricePerDay', 'property.images', 'rent'])
            ->get()
            ->map(fn(Payment $p) => $this->formatPayment($p));

        return response()->json($payments);
    }

    public function simulate(Request $request, Payment $payment)
    {
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        if (!$payment->abacatepay_id) {
            return response()->json(['message' => 'QR Code ainda não gerado.'], 422);
        }

        $res = $this->http()
            ->post("{$this->abacateBase}/transparents/simulate-payment?id={$payment->abacatepay_id}", [
                'metadata' => [],
            ]);

        Log::info('[AbacatePay] Simulate response', [
            'status' => $res->status(),
            'body'   => $res->body(),
        ]);

        if (!$res->successful()) {
            Log::error('[AbacatePay] Falha ao simular', [
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
            return response()->json(['message' => 'Erro ao simular pagamento.'], 502);
        }

        $payment->update(['status' => 'paid']);
        $payment->rent->update(['confirmed' => true]);

        return response()->json(['status' => 'paid']);
    }

    public function getQrCode(Request $request, Payment $payment)
    {
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        if ($payment->status !== 'pending_payment') {
            return response()->json(['message' => 'Este pagamento não está pendente.'], 422);
        }

        if ($payment->br_code) {
            return response()->json([
                'br_code'        => $payment->br_code,
                'br_code_base64' => $this->limparBase64($payment->br_code_base64),
                'expires_at'     => $payment->expires_at,
                'amount'         => $payment->amount,
            ]);
        }

        return $this->gerarQrCode($payment);
    }

    public function checkStatus(Request $request, Payment $payment)
    {
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        if ($payment->status === 'paid') {
            return response()->json(['status' => 'paid']);
        }

        if ($payment->expires_at && now()->isAfter($payment->expires_at)) {
            $payment->update(['status' => 'expired']);
            return response()->json(['status' => 'expired']);
        }

        if ($payment->abacatepay_id) {
            $res = $this->http()->get("{$this->abacateBase}/transparents/check", [
                'id' => $payment->abacatepay_id,
            ]);

            if ($res->successful()) {
                $status = $res->json('data.status');

                if ($status === 'PAID') {
                    $payment->update(['status' => 'paid']);
                    $payment->rent->update(['confirmed' => true]);
                    return response()->json(['status' => 'paid']);
                }
            }
        }

        return response()->json([
            'status'     => $payment->status,
            'expires_at' => $payment->expires_at,
        ]);
    }

    public function webhook(Request $request)
    {
        $event = $request->input('event');
        $data  = $request->input('data', []);

        Log::info('[AbacatePay Webhook]', ['event' => $event, 'data' => $data]);

        if ($event === 'transparent.completed') {
            $abacateId = $data['id'] ?? null;

            if ($abacateId) {
                $payment = Payment::where('abacatepay_id', $abacateId)->first();

                if ($payment && $payment->status !== 'paid') {
                    $payment->update(['status' => 'paid']);
                    $payment->rent->update(['confirmed' => true]);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    private function gerarQrCode(Payment $payment)
    {
        $expiresIn = now()->diffInSeconds($payment->expires_at, false);

        if ($expiresIn <= 0) {
            $payment->update(['status' => 'expired']);
            return response()->json(['message' => 'Prazo de pagamento expirado.'], 422);
        }

        $body = [
            'method' => 'PIX',
            'data'   => [
                'amount'      => (int) $payment->amount,
                'description' => 'Reserva #' . $payment->rent_id . ' - ' . $payment->property->property_title,
                'expiresIn'   => (int) $expiresIn,
                'externalId'  => (string) $payment->id,
                'metadata'    => [
                    'rent_id'    => (string) $payment->rent_id,
                    'payment_id' => (string) $payment->id,
                ],
            ],
        ];

        Log::info('[AbacatePay] Enviando body', ['body' => $body]);

        $res = $this->http()->post("{$this->abacateBase}/transparents/create", $body);

        if (!$res->successful()) {
            Log::error('[AbacatePay] Falha ao criar PIX', [
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
            return response()->json(['message' => 'Erro ao gerar PIX. Tente novamente.'], 502);
        }

        $pixData      = $res->json('data');
        $brCodeBase64 = $this->limparBase64($pixData['brCodeBase64']);

        $payment->update([
            'abacatepay_id'  => $pixData['id'],
            'br_code'        => $pixData['brCode'],
            'br_code_base64' => $brCodeBase64,
        ]);

        return response()->json([
            'br_code'        => $pixData['brCode'],
            'br_code_base64' => $brCodeBase64,
            'expires_at'     => $payment->expires_at,
            'amount'         => $payment->amount,
        ]);
    }

    private function formatPayment(Payment $p): array
    {
        $img = $p->property->images->first();

        return [
            'payment_id' => $p->id,
            'status'     => $p->status,
            'amount'     => $p->amount,
            'expires_at' => $p->expires_at,
            'checkin'    => $p->rent->checkin,
            'checkout'   => $p->rent->checkout,
            'property'   => [
                'id'    => $p->property->id,
                'title' => $p->property->property_title,
                'image' => $img ? asset('storage/' . $img->path) : null,
            ],
        ];
    }
}
