<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Affiche la liste des administrateurs
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $admins = Admin::orderBy('created_at', 'desc')->get();
        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Affiche le formulaire de création d'un administrateur
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.admins.create');
    }

    /**
     * Enregistre un nouvel administrateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,superadmin',
        ]);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Administrateur créé avec succès.');
    }

    /**
     * Affiche le formulaire d'édition d'un administrateur
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\View\View
     */
    public function edit(Admin $admin)
    {
        return view('admin.admins.edit', compact('admin'));
    }

    /**
     * Met à jour les informations d'un administrateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'role' => 'required|in:admin,superadmin',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // Mettre à jour le mot de passe seulement s'il est fourni
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $data['password'] = bcrypt($request->password);
        }

        $admin->update($data);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Administrateur mis à jour avec succès.');
    }

    /**
     * Supprime un administrateur
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Admin $admin)
    {
        // Empêcher la suppression de son propre compte
        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Administrateur supprimé avec succès.');
    }
}
