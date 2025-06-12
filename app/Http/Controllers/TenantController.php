<?php

namespace App\Http\Controllers;

use App\Models\SerialKey;
use App\Models\Tenant;
use App\Services\HistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    protected $historyService;

    public function __construct(HistoryService $historyService)
    {
        $this->historyService = $historyService;
        $this->middleware('auth');
    }

    /**
     * Afficher la liste des tenants disponibles pour l'utilisateur
     */
    public function select()
    {
        $user = Auth::user();
        $tenants = $user->tenants()->where('status', 'active')->get();
        
        // Si l'utilisateur n'a qu'un seul tenant, le sélectionner automatiquement
        if ($tenants->count() === 1) {
            Session::put('tenant_id', $tenants->first()->id);
            return redirect()->route('dashboard');
        }
        
        return view('tenants.select', compact('tenants'));
    }

    /**
     * Changer de tenant actif
     */
    public function switch(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        
        // Vérifier que l'utilisateur a accès à ce tenant
        if (!$tenant->users()->where('users.id', Auth::id())->exists()) {
            return redirect()->route('tenant.select')
                ->with('error', 'Vous n\'avez pas accès à ce compte.');
        }
        
        // Vérifier que le tenant est actif
        if ($tenant->status !== 'active') {
            return redirect()->route('tenant.select')
                ->with('error', 'Ce compte n\'est pas actif.');
        }
        
        // Stocker le tenant dans la session
        Session::put('tenant_id', $tenant->id);
        
        return redirect()->route('dashboard')
            ->with('success', 'Vous utilisez maintenant le compte : ' . $tenant->name);
    }

    /**
     * Afficher la liste des tenants (pour les administrateurs)
     */
    public function index()
    {
        $this->authorize('viewAny', Tenant::class);
        
        $tenants = Tenant::with(['licence', 'users'])
            ->latest()
            ->paginate(15);
            
        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Afficher le formulaire de création d'un tenant
     */
    public function create()
    {
        $this->authorize('create', Tenant::class);
        
        // Récupérer uniquement les licences multi-comptes qui peuvent accepter plus de tenants
        $licences = SerialKey::where('licence_type', SerialKey::LICENCE_TYPE_MULTI)
            ->where('status', 'active')
            ->get()
            ->filter(function($licence) {
                return $licence->canAcceptMoreTenants();
            });
            
        return view('admin.tenants.create', compact('licences'));
    }

    /**
     * Stocker un nouveau tenant
     */
    public function store(Request $request)
    {
        $this->authorize('create', Tenant::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'licence_id' => 'required|exists:serial_keys,id',
            'domain' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,suspended',
        ]);
        
        // Vérifier que la licence est de type multi et peut accepter plus de tenants
        $licence = SerialKey::findOrFail($validated['licence_id']);
        
        if (!$licence->isMultiAccount()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'La licence sélectionnée n\'est pas de type multi-comptes.');
        }
        
        if (!$licence->canAcceptMoreTenants()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'La licence sélectionnée a atteint son nombre maximum de comptes (' . $licence->getMaxAccounts() . ').');
        }
        
        // Créer le tenant
        $tenant = Tenant::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'licence_id' => $validated['licence_id'],
            'domain' => $validated['domain'],
            'status' => $validated['status'],
            'subscription_id' => 'tenant_' . Str::random(10),
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addYear(),
        ]);
        
        // Journaliser la création
        $this->historyService->logAction(
            $licence,
            'tenant_created',
            'Création d\'un nouveau compte tenant : ' . $tenant->name
        );
        
        return redirect()->route('admin.tenants.index')
            ->with('success', 'Compte créé avec succès.');
    }

    /**
     * Afficher les détails d'un tenant
     */
    public function show(Tenant $tenant)
    {
        $this->authorize('view', $tenant);
        
        $tenant->load(['licence', 'users']);
        
        return view('admin.tenants.show', compact('tenant'));
    }

    /**
     * Afficher le formulaire d'édition d'un tenant
     */
    public function edit(Tenant $tenant)
    {
        $this->authorize('update', $tenant);
        
        // Récupérer les licences multi-comptes disponibles
        $licences = SerialKey::where('licence_type', SerialKey::LICENCE_TYPE_MULTI)
            ->where('status', 'active')
            ->get()
            ->filter(function($licence) use ($tenant) {
                // Inclure la licence actuelle ou celles qui peuvent accepter plus de tenants
                return $licence->id === $tenant->licence_id || $licence->canAcceptMoreTenants();
            });
            
        return view('admin.tenants.edit', compact('tenant', 'licences'));
    }

    /**
     * Mettre à jour un tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $this->authorize('update', $tenant);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'licence_id' => 'required|exists:serial_keys,id',
            'domain' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,suspended',
        ]);
        
        // Vérifier que la nouvelle licence est de type multi et peut accepter ce tenant
        if ($tenant->licence_id !== (int)$validated['licence_id']) {
            $licence = SerialKey::findOrFail($validated['licence_id']);
            
            if (!$licence->isMultiAccount()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'La licence sélectionnée n\'est pas de type multi-comptes.');
            }
            
            if (!$licence->canAcceptMoreTenants()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'La licence sélectionnée a atteint son nombre maximum de comptes (' . $licence->getMaxAccounts() . ').');
            }
            
            // Journaliser le changement de licence
            $this->historyService->logAction(
                $tenant->licence,
                'tenant_removed',
                'Le compte tenant ' . $tenant->name . ' a été déplacé vers une autre licence.'
            );
            
            $this->historyService->logAction(
                $licence,
                'tenant_added',
                'Le compte tenant ' . $tenant->name . ' a été ajouté à cette licence.'
            );
        }
        
        $tenant->update($validated);
        
        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', 'Compte mis à jour avec succès.');
    }

    /**
     * Supprimer un tenant
     */
    public function destroy(Tenant $tenant)
    {
        $this->authorize('delete', $tenant);
        
        $licence = $tenant->licence;
        $tenantName = $tenant->name;
        
        $tenant->delete();
        
        // Journaliser la suppression
        $this->historyService->logAction(
            $licence,
            'tenant_deleted',
            'Suppression du compte tenant : ' . $tenantName
        );
        
        return redirect()->route('admin.tenants.index')
            ->with('success', 'Compte supprimé avec succès.');
    }
}
