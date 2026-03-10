<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Models\Ticket;
use App\Observers\TicketObserver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Auth\Events\Logout;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class , LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading();
        Ticket::observe(TicketObserver::class);
        $this->configureDefaults();

        Event::listen(\Illuminate\Auth\Events\Logout::class , function (\Illuminate\Auth\Events\Logout $event) {
            if ($event->user) {
                $event->user->update(['status' => 'offline']);
            }
        });
    }

    protected function configureDefaults(): void
    {
        Date::use (CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn(): ?Password => app()->isProduction()
        ?Password::min(12)
        ->mixedCase()
        ->letters()
        ->numbers()
        ->symbols()
        ->uncompromised()
        : null
        );
    }
}
