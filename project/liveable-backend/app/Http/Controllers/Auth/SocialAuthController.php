<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(string $provider)
    {
        // Fallback manual caso o env() falhe por conta de cache de configuração
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            Log::error('Falha na comunicação com o Socialite: ' . $e->getMessage());
            return redirect($frontendUrl . '/login?error=social_auth_failed');
        }

        try {
            $nameParts = explode(' ', $socialUser->getName(), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName  = $nameParts[1] ?? '';

            // Buscamos ou criamos garantindo valores padrão para campos obrigatórios do banco
            $user = User::firstOrCreate(
                [
                    'email' => $socialUser->getEmail()
                ],
                [
                    'name'            => $firstName,
                    'last_name'       => $lastName,
                    'provider'        => $provider,
                    'provider_id'     => $socialUser->getId(),
                    'password'        => bcrypt(Str::random(24)),
                    'profile_picture' => $socialUser->getAvatar(),
                    'role'            => 'user', // Evita quebra se a coluna não for nullable
                    'share_socials'   => false,  // Evita quebra se a coluna não for nullable
                ]
            );

            // Se o usuário já existia por email tradicional, vinculamos o ID social dele
            if (!$user->provider) {
                $user->update([
                    'provider'    => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            }

            // Criação limpa do token via Sanctum
            $token = $user->createToken('social-auth')->plainTextToken;

            return redirect($frontendUrl . '/auth/callback?token=' . $token);

        } catch (\Exception $e) {
            // Se o banco de dados rejeitar algum campo, o erro real será registrado aqui:
            Log::error('Erro ao salvar ou autenticar usuário social: ' . $e->getMessage());

            // Redireciona o Vue com detalhes para você debugar na URL
            return redirect($frontendUrl . '/login?error=database_error&details=' . urlencode($e->getMessage()));
        }
    }
}
