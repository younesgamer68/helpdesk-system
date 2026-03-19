<?php

use App\Http\Controllers\ChatbotFaqController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\QuickRegisterController;
use App\Http\Controllers\TicketsController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ====== HOME ======
Route::get('/', function (Request $request) {
    $host = explode(':', $request->getHost())[0];
    $baseDomain = config('app.domain');

    if ($baseDomain && str_ends_with($host, '.'.$baseDomain)) {
        $protocol = app()->environment('local') ? 'http' : 'https';

        return redirect()->away($protocol.'://'.$baseDomain.'/');
    }

    return view('welcome');
})->name('home');

// ====== CHATBOT ======
Route::middleware(['throttle:30,1'])->group(function () {
    Route::get('/chatbot/faqs', [ChatbotFaqController::class, 'random'])->name('chatbot.faqs');
    Route::post('/chatbot/chat', [ChatbotFaqController::class, 'chat'])->name('chatbot.chat');
});

// ====== AUTH ======
Route::middleware('guest')->group(function () {
    Route::get('/login', function (Request $request) {
        $host = explode(':', $request->getHost())[0];
        $baseDomain = config('app.domain');

        if ($baseDomain && str_ends_with($host, '.'.$baseDomain)) {
            $protocol = app()->environment('local') ? 'http' : 'https';

            return redirect()->away($protocol.'://'.$baseDomain.'/login');
        }

        return view('auth.login');
    })->name('login');
    Route::get('/set-password', App\Livewire\Auth\SetPassword::class)
        ->name('set-password')
        ->middleware('user.pending');

    // Invitation link acceptance
    Route::get('/invitation/{user}', App\Http\Controllers\Auth\InvitationController::class)
        ->name('invitations.accept');

    Route::post('/register/quick', [QuickRegisterController::class, 'store'])
        ->name('register.quick');

    // Google OAuth
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', function () {
    if (Auth::check()) {
        Auth::user()->update(['status' => 'offline']);
    }
    Auth::logout();

    $protocol = app()->environment('local') ? 'http' : 'https';

    return redirect()->away($protocol.'://'.config('app.domain').'/');
})->middleware('auth')->name('logout');

// Setup Company (after Google OAuth + email verified)
Route::get('/setup-company', App\Livewire\Auth\SetupCompany::class)
    ->middleware(['auth', 'verified'])
    ->name('setup-company');

// ====== EMAIL VERIFICATION WITH CODE ======
Route::get('/email/verify', App\Livewire\Auth\VerifyEmailCode::class)
    ->middleware('auth')
    ->name('verification.notice');

// Keep link-based verification as backup
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    $user = Auth::user();

    // Si l'utilisateur a déjà une company → dashboard
    if ($user->company_id && $user->company) {
        return redirect()->to('https://'.$user->company->slug.'.'.config('app.domain').'/dashboard');
    }

    // Sinon → formulaire setup company
    return redirect()->route('setup-company');
})->middleware(['auth', 'signed'])->name('verification.verify');

// ====== SUBDOMAIN (company) ======
Route::domain('{company}.'.config('app.domain'))->group(function () {
    Route::bind('article', function (string $value) {
        return \App\Models\KbArticle::query()
            ->where('id', $value)
            ->orWhere('slug', $value)
            ->firstOrFail();
    });

    // Public Knowledge Base Portal
    Route::prefix('kb')->name('kb.public.')->group(function () {
        Route::get('/', [\App\Http\Controllers\KbPortalController::class, 'home'])->name('home');
        Route::get('/category/{category}', [\App\Http\Controllers\KbPortalController::class, 'category'])->name('category');
        Route::get('/article/{article:slug}', [\App\Http\Controllers\KbPortalController::class, 'article'])->name('article');
        Route::get('/search', [\App\Http\Controllers\KbPortalController::class, 'search'])->name('search');
        Route::post('/article/{article:slug}/vote', [\App\Http\Controllers\KbPortalController::class, 'vote'])->name('vote');
        Route::get('/widget.js', [\App\Http\Controllers\KbWidgetController::class, 'snippet'])->name('widget');
        Route::get('/widget-demo', [\App\Http\Controllers\KbPortalController::class, 'widgetDemo'])->name('widget-demo');
    });

    Route::middleware(['auth', 'company.access', 'verified'])->group(function () {
        // Onboarding form for the company
        Route::get('/onboarding', \App\Livewire\Onboarding\Wizard::class)->name('onboarding.wizard');

        // Dashboard routes (require onboarding)
        Route::middleware(['company.is_onboarded'])->group(function () {
            Route::get('/dashboard', function () {
                $user = Auth::user();
                if ($user->role === 'admin') {
                    return redirect('/admin/dashboard');
                }

                return redirect('/home');
            })->name('dashboard');

            Route::livewire('home', \App\Livewire\App\AgentDashboard::class)
                ->name('agent.dashboard')
                ->middleware(\App\Http\Middleware\AgentOnly::class);
            Route::livewire('admin/dashboard', \App\Livewire\App\AdminDashboard::class)
                ->name('admin.dashboard')
                ->middleware(\App\Http\Middleware\AdminOnly::class);

            Route::view('tickets', 'app.tickets.index')->name('tickets');
            Route::get('tickets/{ticket}', [TicketsController::class, 'show'])->name('details');
            Route::livewire('notifications', \App\Livewire\Notifications\NotificationsPage::class)->name('notifications');

            Route::get('/customers', fn () => view('app.customers'))
                ->middleware('can:view-operators,App\Models\User')
                ->name('customers');
            Route::get('/customers/{customer}', fn (string $company, string $customer) => view('app.customer-details-page', ['customer' => $customer]))
                ->middleware('can:view-operators,App\Models\User')
                ->name('customers.details');
            Route::get('/operators', fn () => view('app.operators'))
                ->middleware('can:view-operators,App\Models\User')
                ->name('operators');
            Route::get('/teams', fn () => view('app.teams'))
                ->middleware('can:view-operators,App\Models\User')
                ->name('teams');
            Route::get('/operators/{operator}', \App\Livewire\Operators\OperatorProfile::class)
                ->middleware('can:view-operators,App\Models\User')
                ->name('operator.profile');
            Route::get('/categories', fn () => view('app.categories'))
                ->middleware('can:view-operators,App\Models\User')
                ->name('categories');

            Route::prefix('kb')->name('kb.')->group(function () {
                Route::get('/categories', \App\Livewire\Tickets\Kb\Categories::class)->name('categories');
                Route::get('/articles', \App\Livewire\Tickets\Kb\ArticlesList::class)->name('articles');
                Route::get('/articles/create', \App\Livewire\Tickets\Kb\ArticleEditor::class)->name('articles.create');
                Route::get('/articles/{article}/edit', \App\Livewire\Tickets\Kb\ArticleEditor::class)->name('articles.edit');
                Route::get('/media', \App\Livewire\Tickets\Kb\MediaLibrary::class)->name('media');
                Route::get('/api', \App\Livewire\Tickets\Kb\ApiReference::class)->name('api');
            });

            Route::get('/automation', fn () => view('app.automation', ['filterMode' => 'ticket']))
                ->middleware('can:view-operators,App\Models\User')
                ->name('automation');
            Route::get('/automation/ticket-rules', fn () => view('app.automation', ['filterMode' => 'ticket']))
                ->middleware('can:view-operators,App\Models\User')
                ->name('automation.ticket-rules');
            Route::get('/automation/assignment-rules', fn () => view('app.automation', ['filterMode' => 'assignment']))
                ->middleware('can:view-operators,App\Models\User')
                ->name('automation.assignment-rules');
            Route::get('/automation/sla-policy', fn () => view('app.sla-policy'))
                ->middleware('can:view-operators,App\Models\User')
                ->name('automation.sla-policy');

            Route::livewire('reports', \App\Livewire\Reports\ReportsAnalytics::class)
                ->middleware('can:view-operators,App\Models\User')
                ->name('reports');

            Route::livewire('channels', \App\Livewire\Channels\Channels::class)
                ->middleware(\App\Http\Middleware\AdminOnly::class)
                ->name('channels');

            // AI Settings (admin only)
            Route::middleware(\App\Http\Middleware\AdminOnly::class)->prefix('ai')->name('ai.')->group(function () {
                Route::livewire('/training', \App\Livewire\Ai\SuggestedRepliesTraining::class)->name('training');
                Route::livewire('/chat-history', \App\Livewire\Ai\ChatHistory::class)->name('chat-history');
                Route::livewire('/stats', \App\Livewire\Ai\UsageStats::class)->name('stats');
            });
        });
    });
});

require __DIR__.'/settings.php';
