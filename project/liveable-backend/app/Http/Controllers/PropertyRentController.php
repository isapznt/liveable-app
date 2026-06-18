<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Rent;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class PropertyRentController extends Controller
{
    public function index(Property $property)
    {
        return $property->rents()->get(['checkin', 'checkout']);
    }

    public function store(Request $request, Property $property)
    {
        // Impede duplicata: uma reserva por usuário por propriedade
        $jaExiste = $property->rents()
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($jaExiste) {
            return response()->json([
                'message' => 'Você já possui uma solicitação para este imóvel.',
            ], 422);
        }

        $validated = $request->validate([
            'checkin'      => 'required|date',
            'checkout'     => 'required|date|after:checkin',
            'guests_count' => 'nullable|integer|min:1',
            'has_pet'      => 'nullable|boolean',
            'details'      => 'nullable|string|max:1000',
        ]);

        $rent = $property->rents()->create([
            'user_id'      => $request->user()->id,
            'checkin'      => $validated['checkin'],
            'checkout'     => $validated['checkout'],
            'guests_count' => $validated['guests_count'] ?? null,
            'details'      => $validated['details'] ?? '',
            'has_pet'      => $validated['has_pet'] ?? false,
        ]);

        return response()->json([
            'message' => 'Solicitação enviada com sucesso! Aguarde o dono aceitar.',
            'rent'    => $rent,
        ], 201);
    }

    public function myRent(Property $property)
    {
        $hasRent = $property->rents()
            ->where('user_id', auth()->id())
            ->exists();

        return response()->json(['has_rent' => $hasRent]);
    }

    public function pendingRents(Request $request)
    {
        $user = $request->user();

        $reservas = Rent::whereHas('property', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('confirmed', false)
            ->whereDoesntHave('payment', fn($q) => $q->where('status', 'pending_payment'))
            ->with([
                'property:id,property_title,pricePerDay,user_id',
                'property.images',
                'user:id,name,profile_picture',
            ])
            ->get()
            ->map(fn($rent) => [
                'rent_id'      => $rent->id,
                'checkin'      => $rent->checkin,
                'checkout'     => $rent->checkout,
                'guests_count' => $rent->guests_count,
                'has_pet'      => $rent->has_pet,
                'details'      => $rent->details,
                'confirmed'    => $rent->confirmed,
                'property' => [
                    'id'            => $rent->property->id,
                    'title'         => $rent->property->property_title,
                    'price_per_day' => $rent->property->pricePerDay,
                    'image'         => $rent->property->images->first()?->url ?? null,
                ],
                'requester' => [
                    'id'     => $rent->user->id,
                    'name'   => $rent->user->name,
                    'avatar' => $rent->user->profile_picture ?? null,
                ],
            ]);

        return response()->json($reservas);
    }

    public function updateStatus(Request $request, Rent $rent)
    {
        if ($rent->property->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $request->validate([
            'confirmed' => 'required|boolean',
        ]);

        if (!$request->confirmed) {
            $rent->delete();
            return response()->json(['message' => 'Reserva recusada e removida.']);
        }

        $noites = Carbon::parse($rent->checkin)->diffInDays(Carbon::parse($rent->checkout));
        $noites = max(1, $noites);
        $amount = $rent->property->pricePerDay * $noites * 100;

        $payment = Payment::create([
            'rent_id'     => $rent->id,
            'user_id'     => $rent->user_id,
            'property_id' => $rent->property_id,
            'amount'      => $amount,
            'status'      => 'pending_payment',
            'expires_at'  => now()->addHours(24),
        ]);

        return response()->json([
            'message'    => 'Reserva aceita! Solicitador tem 24h para pagar.',
            'payment_id' => $payment->id,
        ]);
    }

    public function activeRents(Request $request)
    {
        $user = $request->user();

        $rents = Rent::where('confirmed', true)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('property', fn($q2) => $q2->where('user_id', $user->id));
            })
            ->with([
                'property:id,property_title,pricePerDay,user_id',
                'property.images',
                'user:id,name,profile_picture',
            ])
            ->get()
            ->map(fn($rent) => [
                'rent_id'      => $rent->id,
                'checkin'      => $rent->checkin,
                'checkout'     => $rent->checkout,
                'guests_count' => $rent->guests_count,
                'has_pet'      => $rent->has_pet,
                'is_owner'     => $rent->property->user_id === $user->id,
                'property' => [
                    'id'    => $rent->property->id,
                    'title' => $rent->property->property_title,
                    'image' => $rent->property->images->first()?->url ?? null,
                ],
                'requester' => [
                    'id'     => $rent->user->id,
                    'name'   => $rent->user->name,
                    'avatar' => $rent->user->profile_picture ?? null,
                ],
            ]);

        return response()->json($rents);
    }
}
