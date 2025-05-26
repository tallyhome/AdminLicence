<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Gestion du changement de langue directement dans la page de connexion
        $locale = $request->query('locale');
        
        if ($locale) {
            $availableLocales = config('app.available_locales', ['en', 'fr', 'es', 'de', 'it', 'pt', 'nl', 'ru', 'zh', 'ja', 'ar', 'tr']);
            
            if (in_array($locale, $availableLocales)) {
                // Définir la langue dans l'application
                app()->setLocale($locale);
                
                // Stocker la langue en session et cookie
                session(['locale' => $locale]);
                cookie()->queue('locale', $locale, 60 * 24 * 30); // Cookie valable 30 jours
            }
        }
        
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            
            $admin = Auth::guard('admin')->user();
            
            // Vérifier si l'authentification à deux facteurs est activée
            if ($admin->two_factor_enabled) {
                // Stocker l'ID de l'administrateur dans la session pour la vérification 2FA
                Session::put('admin_2fa_id', $admin->id);
                
                // Rediriger vers la page de vérification 2FA
                return redirect()->route('admin.2fa.verify');
            }
            
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }
    
    /**
     * Afficher le formulaire de vérification 2FA.
     *
     * @return \Illuminate\View\View
     */
    public function showTwoFactorForm()
    {
        if (!Session::has('admin_2fa_id')) {
            return redirect()->route('admin.login');
        }
        
        return view('auth.admin-2fa');
    }
    
    /**
     * Vérifier le code 2FA.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);
        
        if (!Session::has('admin_2fa_id')) {
            return redirect()->route('admin.login');
        }
        
        $admin = \App\Models\Admin::find(Session::get('admin_2fa_id'));
        
        if (!$admin) {
            return redirect()->route('admin.login');
        }
        
        // Vérifier le code 2FA avec Google Authenticator
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $valid = $google2fa->verifyKey($admin->two_factor_secret, $request->code);
        
        if ($valid) {
            // Authentifier l'administrateur
            Auth::guard('admin')->login($admin);
            
            // Supprimer l'ID de la session
            Session::forget('admin_2fa_id');
            
            return redirect()->intended(route('admin.dashboard'));
        }
        
        return back()->withErrors([
            'code' => 'Le code d\'authentification est invalide.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    /**
     * Afficher le formulaire de demande de réinitialisation de mot de passe.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.admin-passwords.email');
    }

    /**
     * Envoyer le lien de réinitialisation de mot de passe.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('admins')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Afficher le formulaire de réinitialisation de mot de passe.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.admin-passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Réinitialiser le mot de passe.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('admin.login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}