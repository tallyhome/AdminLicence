<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Afficher la page des paramètres généraux
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $darkModeEnabled = session('dark_mode', false);
        $settings = session('settings', [
            'site_name' => config('app.name', 'AdminLicence'),
            'site_description' => ''
        ]);
        
        return view('admin.settings.index', compact('admin', 'darkModeEnabled', 'settings'));
    }

    /**
     * Mettre à jour les paramètres généraux du site
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:1000',
        ]);

        // Stocker les paramètres dans un fichier de configuration ou une table de base de données
        // Pour cet exemple, nous allons les stocker dans la session
        session(['settings' => $validated]);

        return redirect()->route('admin.settings.index')
            ->with('success', t('settings.general.update_success'));
    }

    /**
     * Mettre à jour les informations de l'administrateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
        ]);

        $admin->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Vos informations ont été mises à jour avec succès.');
    }

    /**
     * Mettre à jour le mot de passe de l'administrateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password:admin',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $admin = Auth::guard('admin')->user();
        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Votre mot de passe a été mis à jour avec succès.');
    }

    /**
     * Mettre à jour le favicon du site
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:ico,png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('favicon')) {
            // Supprimer l'ancien favicon s'il existe
            if (file_exists(public_path('favicon.ico'))) {
                unlink(public_path('favicon.ico'));
            }

            // Enregistrer le nouveau favicon
            $favicon = $request->file('favicon');
            $favicon->move(public_path(), 'favicon.ico');
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Le favicon a été mis à jour avec succès.');
    }

    /**
     * Activer ou désactiver le thème sombre
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleDarkMode(Request $request)
    {
        $darkMode = $request->has('dark_mode');
        session(['dark_mode' => $darkMode]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Le thème a été mis à jour avec succès.');
    }
}