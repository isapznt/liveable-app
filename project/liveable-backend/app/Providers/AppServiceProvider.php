<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🚀 RESOLUÇÃO DEFINITIVA PARA O GOOGLE NO WINDOWS LOCAL
        if (config('app.env') === 'local' || (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']))) {

            // Força o Socialite a usar um cliente Guzzle sem validação de SSL para o Google
            Socialite::extend('google', function ($app) {
                $config = $app['config']['services.google'];

                $guzzleClient = new \GuzzleHttp\Client([
                    'verify' => false, // Desativa a verificação de certificado SSL
                    'timeout' => 20,
                ]);

                $provider = Socialite::buildProvider(
                    \Laravel\Socialite\Two\GoogleProvider::class,
                    $config
                );

                // Injeta o cliente HTTP configurado diretamente no provedor do Google
                return $provider->setHttpClient($guzzleClient)->stateless();
            });
        }

        // Seus Gates originais continuam aqui embaixo intactos:
        Gate::define('admin', function ($user) {
            return (bool) $user->role == 'admin';
        });

        Gate::define('adminOrOwner', function ($user) {
            return (bool) $user->role == 'owner' || $user->role === 'admin';
        });
    }
}
