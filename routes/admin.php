<?php

use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailProviderController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\MailController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\Mail\Providers\PHPMailController;
use App\Http\Controllers\Admin\Mail\Providers\MailchimpController;
use App\Http\Controllers\Admin\Mail\Providers\MailgunController;
use App\Http\Controllers\Admin\SerialKeyController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TwoFactorAuthController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\VersionController;
use App\Http\Controllers\Admin\ApiDocumentationController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\ClientExampleController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\EmailVariableController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\OptimizationController;
use Illuminate\Support\Facades\Route;

// Routes d'authentification
Route::middleware('guest:admin')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    // Route directe pour la connexion admin (accessible depuis le frontend)
    Route::get('direct-login', [AdminAuthController::class, 'showLoginForm'])->name('direct.admin.login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('/', [AdminAuthController::class, 'login']); // Route POST explicite pour /admin
    
    // Routes de réinitialisation de mot de passe
    Route::get('password/reset', [AdminAuthController::class, 'showLinkRequestForm'])->name('admin.password.request');
    Route::post('password/email', [AdminAuthController::class, 'sendResetLinkEmail'])->name('admin.password.email');
    Route::get('password/reset/{token}', [AdminAuthController::class, 'showResetForm'])->name('admin.password.reset');
    Route::post('password/reset', [AdminAuthController::class, 'reset'])->name('admin.password.update');
    
    // Routes pour l'authentification à deux facteurs
    Route::get('2fa/verify', [AdminAuthController::class, 'showTwoFactorForm'])->name('admin.2fa.verify');
    Route::post('2fa/verify', [AdminAuthController::class, 'verifyTwoFactor']);
    Route::get('2fa/recovery', function () { return view('auth.admin-2fa-recovery'); })->name('admin.2fa.recovery');
    Route::post('2fa/recovery', [TwoFactorAuthController::class, 'useRecoveryCode']);
});

// Routes de gestion de licence (accessibles sans vérification de licence)
Route::middleware('auth:admin')->group(function () {
    // Routes pour la gestion de licence
    Route::get('settings/license', [\App\Http\Controllers\Admin\LicenseController::class, 'index'])->name('admin.settings.license');
    Route::post('settings/license', [\App\Http\Controllers\Admin\LicenseController::class, 'updateSettings'])->name('admin.settings.license.update');
    Route::get('settings/license/force-check', [\App\Http\Controllers\Admin\LicenseController::class, 'forceCheck'])->name('admin.settings.license.force-check');
    
    // Déconnexion
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// Routes protégées nécessitant une licence valide
Route::middleware(['auth:admin', \App\Http\Middleware\CheckLicenseMiddleware::class])->group(function () {

    // Tableau de bord
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Informations de version
    Route::get('/version', [VersionController::class, 'index'])->name('admin.version');

    // Documentation
    Route::get('/api-documentation', [ApiDocumentationController::class, 'index'])->name('admin.api.documentation');
    
    // Routes de documentation avec noms alternatifs
    Route::get('/licence-documentation', [ApiDocumentationController::class, 'licenceDocumentation'])->name('admin.documentation.licence');
    Route::get('/licence-documentation-alt', [ApiDocumentationController::class, 'licenceDocumentation'])->name('admin.licence.documentation');
    
    Route::get('/email-documentation', [ApiDocumentationController::class, 'emailDocumentation'])->name('admin.documentation.email');
    Route::get('/email-documentation-alt', [ApiDocumentationController::class, 'emailDocumentation'])->name('admin.email.documentation');
    
    // Routes pour les exemples d'intégration des licences
    Route::prefix('examples')->name('admin.examples.')->group(function () {
        Route::get('/javascript', function () { return view('admin.examples.javascript-licence'); })->name('javascript');
        Route::get('/flutter', function () { return view('admin.examples.flutter-licence'); })->name('flutter');
    });
    
    // Routes pour la recherche de licence
    Route::get('/license/search', [LicenseController::class, 'search'])->name('admin.license.search');
    Route::get('/license/details/{id}', [LicenseController::class, 'details'])->name('admin.license.details');

    // Exemples d'intégration client
    Route::get('/client-example', [ClientExampleController::class, 'index'])->name('admin.client-example');

    // Gestion des projets
    Route::resource('projects', ProjectController::class)
        ->names([
            'index' => 'admin.projects.index',
            'create' => 'admin.projects.create',
            'store' => 'admin.projects.store',
            'show' => 'admin.projects.show',
            'edit' => 'admin.projects.edit',
            'update' => 'admin.projects.update',
            'destroy' => 'admin.projects.destroy'
        ]);
    Route::get('projects/{project}/serial-keys', [ProjectController::class, 'serialKeys'])->name('admin.projects.serial-keys');
    Route::get('projects/{project}/api-keys', [ProjectController::class, 'apiKeys'])->name('admin.projects.api-keys');

    // Gestion des clés de licence
    Route::resource('serial-keys', SerialKeyController::class)
        ->names([
            'index' => 'admin.serial-keys.index',
            'create' => 'admin.serial-keys.create',
            'store' => 'admin.serial-keys.store',
            'show' => 'admin.serial-keys.show',
            'edit' => 'admin.serial-keys.edit',
            'update' => 'admin.serial-keys.update',
            'destroy' => 'admin.serial-keys.destroy'
        ]);
    Route::patch('serial-keys/{serialKey}/revoke', [SerialKeyController::class, 'revoke'])->name('admin.serial-keys.revoke');
    Route::patch('serial-keys/{serialKey}/suspend', [SerialKeyController::class, 'suspend'])->name('admin.serial-keys.suspend');
    Route::patch('serial-keys/{serialKey}/reactivate', [SerialKeyController::class, 'reactivate'])->name('admin.serial-keys.reactivate');

    // Gestion des clés API
    Route::resource('api-keys', ApiKeyController::class)
        ->names([
            'index' => 'admin.api-keys.index',
            'create' => 'admin.api-keys.create',
            'store' => 'admin.api-keys.store',
            'show' => 'admin.api-keys.show',
            'edit' => 'admin.api-keys.edit',
            'update' => 'admin.api-keys.update',
            'destroy' => 'admin.api-keys.destroy'
        ]);
    Route::patch('api-keys/{apiKey}/revoke', [ApiKeyController::class, 'revoke'])->name('admin.api-keys.revoke');
    Route::patch('api-keys/{apiKey}/reactivate', [ApiKeyController::class, 'reactivate'])->name('admin.api-keys.reactivate');
    Route::patch('api-keys/{apiKey}/permissions', [ApiKeyController::class, 'updatePermissions'])->name('admin.api-keys.update-permissions');

    // Configuration des emails
    Route::prefix('mail')->name('admin.mail.')->group(function () {
        Route::get('settings', [MailController::class, 'index'])->name('settings');
        Route::post('settings', [MailController::class, 'store'])->name('settings.store');

        // Gestion des fournisseurs d'email
        Route::prefix('providers')->name('providers.')->group(function () {
            Route::get('/', [EmailProviderController::class, 'index'])->name('index');
            Route::put('/', [EmailProviderController::class, 'updateProvider'])->name('update');
            Route::post('/test', [EmailProviderController::class, 'testProvider'])->name('test');

            // PHPMail
            Route::prefix('phpmail')->name('phpmail.')->group(function () {
                Route::get('/', [PHPMailController::class, 'index'])->name('index');
                Route::put('/', [PHPMailController::class, 'update'])->name('update');
                Route::post('/test', [PHPMailController::class, 'test'])->name('test');
                Route::get('/logs', [PHPMailController::class, 'logs'])->name('logs');
                Route::post('/logs/clear', [PHPMailController::class, 'clearLogs'])->name('logs.clear');
            });

            // Mailgun
            Route::prefix('mailgun')->name('mailgun.')->group(function () {
                Route::get('/', [MailgunController::class, 'index'])->name('index');
                Route::put('/', [MailgunController::class, 'update'])->name('update');
                Route::post('/test', [MailgunController::class, 'test'])->name('test');
                Route::get('/logs', [MailgunController::class, 'logs'])->name('logs');
                Route::post('/logs/clear', [MailgunController::class, 'clearLogs'])->name('logs.clear');
            });

            // Mailchimp
            Route::prefix('mailchimp')->name('mailchimp.')->group(function () {
                Route::get('/', [MailchimpController::class, 'index'])->name('index');
                Route::put('/', [MailchimpController::class, 'update'])->name('update');
                Route::post('/test', [MailchimpController::class, 'test'])->name('test');
                Route::post('/sync-lists', [MailchimpController::class, 'syncLists'])->name('sync-lists');
                Route::post('/sync-templates', [MailchimpController::class, 'syncTemplates'])->name('sync-templates');
                Route::get('/campaigns', [MailchimpController::class, 'campaigns'])->name('campaigns');
                Route::post('/campaigns', [MailchimpController::class, 'createCampaign'])->name('campaigns.create');
                Route::post('/campaigns/{campaign}/send', [MailchimpController::class, 'sendCampaign'])->name('campaigns.send');
            });
        });
    });

    // Gestion des templates d'email
    Route::prefix('email/templates')->name('admin.email.templates.')->group(function () {
        Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
        Route::get('/create', [EmailTemplateController::class, 'create'])->name('create');
        Route::post('/', [EmailTemplateController::class, 'store'])->name('store');
        Route::get('/{template}/edit', [EmailTemplateController::class, 'edit'])->name('edit');
        Route::put('/{template}', [EmailTemplateController::class, 'update'])->name('update');
        Route::delete('/{template}', [EmailTemplateController::class, 'destroy'])->name('destroy');
        Route::get('/{template}/preview', [EmailTemplateController::class, 'preview'])->name('preview');
    });

    // Routes pour la gestion des variables d'email
    Route::prefix('email/variables')->name('admin.email.variables.')->group(function () {
        Route::get('/', [EmailVariableController::class, 'index'])->name('index');
        Route::post('/', [EmailVariableController::class, 'store'])->name('store');
        Route::put('/{variable}', [EmailVariableController::class, 'update'])->name('update');
        Route::delete('/{variable}', [EmailVariableController::class, 'destroy'])->name('destroy');
    });

    // Route pour le changement de langue
    Route::post('/set-language', [LanguageController::class, 'setLanguage'])->name('admin.set.language');

    // Routes pour la gestion des traductions
    Route::prefix('settings/translations')->name('admin.settings.translations.')->group(function () {
        Route::get('/', [TranslationController::class, 'index'])->name('index');
        Route::get('/create', [TranslationController::class, 'create'])->name('create');
        Route::post('/', [TranslationController::class, 'store'])->name('store');
        Route::put('/', [TranslationController::class, 'update'])->name('update');
        Route::delete('/', [TranslationController::class, 'destroy'])->name('destroy');
    });
    
    // Routes pour les traductions
    Route::prefix('translations')->name('admin.translations.')->group(function () {
        Route::get('/', [TranslationController::class, 'index'])->name('index');
        Route::post('/', [TranslationController::class, 'store'])->name('store');
        Route::put('/', [TranslationController::class, 'update'])->name('update');
        Route::delete('/', [TranslationController::class, 'destroy'])->name('destroy');
    });

    // Routes pour les paramètres généraux
    Route::get('settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('settings/general', [SettingsController::class, 'updateGeneral'])->name('admin.settings.update');
    Route::put('settings/profile', [SettingsController::class, 'updateProfile'])->name('admin.settings.update-profile');
    
    // Les routes de gestion de licence ont été déplacées en dehors du middleware check.license
    Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('admin.settings.update-password');
    Route::put('settings/favicon', [SettingsController::class, 'updateFavicon'])->name('admin.settings.update-favicon');
    Route::put('settings/dark-mode', [SettingsController::class, 'toggleDarkMode'])->name('admin.settings.toggle-dark-mode');
    
    // Routes pour les outils d'optimisation
    Route::get('/settings/optimization', [OptimizationController::class, 'index'])->name('admin.settings.optimization');
    Route::post('/settings/optimization/clean-logs', [OptimizationController::class, 'cleanLogs'])->name('admin.settings.optimization.clean-logs');
    Route::get('/settings/optimization/view-log', [OptimizationController::class, 'viewLog'])->name('admin.settings.optimization.view-log');
    Route::post('/settings/optimization/optimize-images', [OptimizationController::class, 'optimizeImages'])->name('admin.settings.optimization.optimize-images');
    Route::post('/settings/optimization/asset-example', [OptimizationController::class, 'generateAssetExample'])->name('admin.settings.optimization.asset-example');

    // Diagnostic API
    Route::get('/settings/api-diagnostic', [\App\Http\Controllers\Admin\ApiDiagnosticController::class, 'index'])->name('admin.settings.api-diagnostic');
    Route::post('/settings/api-diagnostic/test-serial-key', [\App\Http\Controllers\Admin\ApiDiagnosticController::class, 'testSerialKey'])->name('admin.settings.api-diagnostic.test-serial-key');
    Route::post('/settings/api-diagnostic/test-api-connection', [\App\Http\Controllers\Admin\ApiDiagnosticController::class, 'testApiConnection'])->name('admin.settings.api-diagnostic.test-api-connection');
    Route::post('/settings/api-diagnostic/test-database', [\App\Http\Controllers\Admin\ApiDiagnosticController::class, 'testDatabaseConnection'])->name('admin.settings.api-diagnostic.test-database');
    Route::post('/settings/api-diagnostic/check-permissions', [\App\Http\Controllers\Admin\ApiDiagnosticController::class, 'checkPermissions'])->name('admin.settings.api-diagnostic.check-permissions');
    Route::post('/settings/api-diagnostic/get-logs', [\App\Http\Controllers\Admin\ApiDiagnosticController::class, 'getLatestLogs'])->name('admin.settings.api-diagnostic.get-logs');
    
    // Routes pour l'authentification à deux facteurs
    Route::get('settings/two-factor', [TwoFactorAuthController::class, 'index'])->name('admin.settings.two-factor');
    Route::post('settings/two-factor/enable', [TwoFactorAuthController::class, 'enable'])->name('admin.settings.two-factor.enable');
    Route::post('settings/two-factor/disable', [TwoFactorAuthController::class, 'disable'])->name('admin.settings.two-factor.disable');
    Route::post('settings/two-factor/regenerate-recovery-codes', [TwoFactorAuthController::class, 'regenerateRecoveryCodes'])->name('admin.settings.two-factor.regenerate-recovery-codes');
    Route::post('settings/verify-code', [TwoFactorAuthController::class, 'verifyCode'])->name('admin.settings.verify-code');
    Route::get('settings/test-google2fa', [TwoFactorController::class, 'testGoogle2FA'])->name('admin.settings.test-google2fa');
    
    // Routes pour les tickets de support (admin standard)
    // Routes supprimées
    // Routes super admin supprimées
    
    // Routes pour les notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('admin.notifications.index');
        Route::get('/unread', [\App\Http\Controllers\NotificationController::class, 'getUnread'])->name('admin.notifications.unread');
        Route::post('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('admin.notifications.mark-as-read');
        Route::post('/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-as-read');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('admin.notifications.destroy');
        Route::put('/preferences', [\App\Http\Controllers\NotificationController::class, 'updatePreferences'])->name('admin.notifications.update-preferences');
    });
});