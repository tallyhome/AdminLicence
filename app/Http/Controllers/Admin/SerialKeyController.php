<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SerialKey;
use App\Services\HistoryService;
use App\Services\LicenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SerialKeyController extends Controller
{
    protected $licenceService;
    protected $historyService;

    public function __construct(LicenceService $licenceService, HistoryService $historyService)
    {
        $this->licenceService = $licenceService;
        $this->historyService = $historyService;
    }

    /**
     * Afficher la liste des clés de licence
     */
    public function index(Request $request)
    {
        // Récupérer les paramètres de pagination
        $perPage = $request->input('per_page', 10);
        $validPerPage = in_array($perPage, [10, 25, 50, 100, 500, 1000]) ? $perPage : 10;
        
        // Récupérer les paramètres de recherche et de filtrage
        $search = $request->input('search');
        $projectFilter = $request->input('project_id');
        $domainFilter = $request->input('domain');
        $ipFilter = $request->input('ip_address');
        $statusFilter = $request->input('status');
        $usedFilter = $request->input('used');
        
        // Construire la requête avec eager loading optimisé
        $query = SerialKey::with([
            'project', 
            'history' => function($query) {
                $query->latest()->limit(3); // Charger uniquement les 3 derniers événements d'historique
            }
        ]);
        
        // Appliquer les filtres
        if ($projectFilter) {
            $query->where('project_id', $projectFilter);
        }
        
        if ($domainFilter) {
            $query->where('domain', 'like', "%{$domainFilter}%");
        }
        
        if ($ipFilter) {
            $query->where('ip_address', 'like', "%{$ipFilter}%");
        }
        
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        
        // Appliquer la recherche
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('serial_key', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('project', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Ajouter une logique pour détecter les clés expirées
        if ($request->input('status') === 'expired') {
            $query->where(function($q) {
                $q->whereNotNull('expires_at')
                  ->where('expires_at', '<', now());
            });
        }
        
        // Ajouter une logique pour détecter les clés utilisées
        if ($usedFilter === 'true') {
            $query->where(function($q) {
                $q->whereNotNull('domain')
                  ->orWhereNotNull('ip_address');
            });
        }
        
        // Récupérer les résultats
        $serialKeys = $query->latest()->paginate($validPerPage)->appends(request()->query());
        
        // Récupérer la liste des projets pour le filtre avec mise en cache (5 minutes)
        $projects = cache()->remember('projects_list', 300, function() {
            return Project::select('id', 'name', 'description')->orderBy('name')->get();
        });
        
        // Liste des statuts pour le filtre
        $statuses = [
            'active' => 'Active',
            'suspended' => 'Suspendue',
            'revoked' => 'Révoquée',
            'expired' => 'Expirée',
            'used' => 'Utilisé'
        ];
        
        return view('admin.serial-keys.index', compact('serialKeys', 'projects', 'statuses'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        // Récupérer la liste des projets avec mise en cache
        $projects = cache()->remember('projects_list', 300, function() {
            return Project::select('id', 'name', 'description')->orderBy('name')->get();
        });
        
        // Types de licence disponibles
        $licenceTypes = [
            SerialKey::LICENCE_TYPE_SINGLE => t('serial_keys.single_account_licence'),
            SerialKey::LICENCE_TYPE_MULTI => t('serial_keys.multi_account_licence')
        ];
        
        return view('admin.serial-keys.create', compact('projects', 'licenceTypes'));
    }

    /**
     * Stocker une nouvelle clé de licence
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'quantity' => 'required|integer|min:1|max:100000',
            'domain' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
            'expires_at' => 'nullable|date|after:today',
            'licence_type' => 'required|in:' . SerialKey::LICENCE_TYPE_SINGLE . ',' . SerialKey::LICENCE_TYPE_MULTI,
            'max_accounts' => 'nullable|required_if:licence_type,' . SerialKey::LICENCE_TYPE_MULTI . '|integer|min:1|max:1000',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $keys = [];

        DB::transaction(function () use ($validated, $project, &$keys) {
            for ($i = 0; $i < $validated['quantity']; $i++) {
                $key = new SerialKey([
                    'serial_key' => $this->licenceService->generateKey(),
                    'project_id' => $project->id,
                    'domain' => $validated['domain'],
                    'ip_address' => $validated['ip_address'],
                    'expires_at' => $validated['expires_at'],
                    'status' => 'active',
                    'licence_type' => $validated['licence_type'],
                    'max_accounts' => $validated['licence_type'] === SerialKey::LICENCE_TYPE_MULTI ? $validated['max_accounts'] : null,
                ]);

                $key->save();
                $keys[] = $key;

                $this->historyService->logAction(
                    $key,
                    'create',
                    'Création d\'une nouvelle clé de licence'
                );
            }
        });

        return redirect()
            ->route('admin.serial-keys.index')
            ->with('success', $validated['quantity'] . ' clé(s) de licence créée(s) avec succès.');
    }

    /**
     * Afficher les détails d'une clé
     */
    public function show(SerialKey $serialKey)
    {
        $serialKey->load(['project', 'history']);
        return view('admin.serial-keys.show', compact('serialKey'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(SerialKey $serialKey)
    {
        // Récupérer la liste des projets avec mise en cache
        $projects = cache()->remember('projects_list', 300, function() {
            return Project::select('id', 'name', 'description')->orderBy('name')->get();
        });
        
        // Types de licence disponibles
        $licenceTypes = [
            SerialKey::LICENCE_TYPE_SINGLE => t('serial_keys.single_account_licence'),
            SerialKey::LICENCE_TYPE_MULTI => t('serial_keys.multi_account_licence')
        ];
        
        return view('admin.serial-keys.edit', compact('serialKey', 'projects', 'licenceTypes'));
    }

    /**
     * Mettre à jour une clé
     */
    public function update(Request $request, SerialKey $serialKey)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'status' => 'required|in:active,suspended,revoked,expired',
            'domain' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
            'expires_at' => 'nullable|date|after:today',
            'licence_type' => 'required|in:' . SerialKey::LICENCE_TYPE_SINGLE . ',' . SerialKey::LICENCE_TYPE_MULTI,
            'max_accounts' => 'nullable|required_if:licence_type,' . SerialKey::LICENCE_TYPE_MULTI . '|integer|min:1|max:1000',
        ]);
        
        // Vérifier si on peut changer le type de licence
        if ($serialKey->licence_type !== $validated['licence_type']) {
            // Si on passe de multi à single, vérifier qu'il n'y a pas plusieurs tenants actifs
            if ($validated['licence_type'] === SerialKey::LICENCE_TYPE_SINGLE && $serialKey->activeTenantsCount() > 1) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', t('serial_keys.cannot_switch_to_single_account', ['count' => $serialKey->activeTenantsCount()]));
            }
        }

        $oldStatus = $serialKey->status;
        $serialKey->update($validated);

        if ($oldStatus !== $validated['status']) {
            $this->historyService->logAction(
                $serialKey,
                'status_change',
                'Changement de statut de la clé de ' . $oldStatus . ' à ' . $validated['status']
            );
        }

        return redirect()
            ->route('admin.serial-keys.show', $serialKey)
            ->with('success', 'Clé de licence mise à jour avec succès.');
    }

    /**
     * Supprimer une clé
     */
    public function destroy(SerialKey $serialKey)
    {
        $serialKey->delete();

        $this->historyService->logAction(
            $serialKey,
            'delete',
            'Suppression de la clé de licence'
        );

        return redirect()
            ->route('admin.serial-keys.index')
            ->with('success', 'Clé de licence supprimée avec succès.');
    }

    /**
     * Révoquer une clé
     */
    public function revoke(SerialKey $serialKey)
    {
        // Débogage - Enregistrer l'ID et le statut avant modification
        \Log::info('Tentative de révocation de la clé #' . $serialKey->id . ' avec statut actuel: ' . $serialKey->status);
        
        if ($serialKey->status === 'active' || $serialKey->status === 'suspended') {
            try {
                // Utiliser DB::transaction pour s'assurer que la modification est bien enregistrée
                DB::transaction(function() use ($serialKey) {
                    // Modification directe via requête SQL pour éviter tout problème de cache ou de modèle
                    DB::table('serial_keys')
                        ->where('id', $serialKey->id)
                        ->update(['status' => 'revoked']);
                    
                    // Forcer le rechargement du modèle depuis la base de données
                    $serialKey->refresh();
                    
                    // Vérifier que le statut a bien été mis à jour
                    \Log::info('Après mise à jour, statut de la clé #' . $serialKey->id . ': ' . $serialKey->status);
                });
                
                // Enregistrer l'action dans l'historique
                $this->historyService->logAction(
                    $serialKey,
                    'revoke',
                    'Révocation de la clé de licence'
                );
                
                return redirect()
                    ->route('admin.serial-keys.show', $serialKey)
                    ->with('success', 'Clé de licence révoquée avec succès. Nouveau statut: ' . $serialKey->status);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la révocation de la clé #' . $serialKey->id . ': ' . $e->getMessage());
                
                return redirect()
                    ->route('admin.serial-keys.show', $serialKey)
                    ->with('error', 'Erreur lors de la révocation de la clé: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.serial-keys.show', $serialKey)
            ->with('error', 'Impossible de révoquer une clé non active ou suspendue. Statut actuel: ' . $serialKey->status);
    }

    /**
     * Suspendre une clé
     */
    public function suspend(SerialKey $serialKey)
    {
        // Débogage - Enregistrer l'ID et le statut avant modification
        \Log::info('Tentative de suspension de la clé #' . $serialKey->id . ' avec statut actuel: ' . $serialKey->status);
        
        if ($serialKey->status === 'active') {
            try {
                // Utiliser DB::transaction pour s'assurer que la modification est bien enregistrée
                DB::transaction(function() use ($serialKey) {
                    // Modification directe via requête SQL pour éviter tout problème de cache ou de modèle
                    DB::table('serial_keys')
                        ->where('id', $serialKey->id)
                        ->update(['status' => 'suspended']);
                    
                    // Forcer le rechargement du modèle depuis la base de données
                    $serialKey->refresh();
                    
                    // Vérifier que le statut a bien été mis à jour
                    \Log::info('Après mise à jour, statut de la clé #' . $serialKey->id . ': ' . $serialKey->status);
                });
                
                // Enregistrer l'action dans l'historique
                $this->historyService->logAction(
                    $serialKey,
                    'suspend',
                    'Suspension de la clé de licence'
                );
                
                return redirect()
                    ->route('admin.serial-keys.show', $serialKey)
                    ->with('success', 'Clé de licence suspendue avec succès. Nouveau statut: ' . $serialKey->status);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la suspension de la clé #' . $serialKey->id . ': ' . $e->getMessage());
                
                return redirect()
                    ->route('admin.serial-keys.show', $serialKey)
                    ->with('error', 'Erreur lors de la suspension de la clé: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.serial-keys.show', $serialKey)
            ->with('error', 'Impossible de suspendre une clé non active. Statut actuel: ' . $serialKey->status);
    }

    /**
     * Réactiver une clé
     */
    public function reactivate(SerialKey $serialKey)
    {
        // Débogage - Enregistrer l'ID et le statut avant modification
        \Log::info('Tentative de réactivation de la clé #' . $serialKey->id . ' avec statut actuel: ' . $serialKey->status);
        
        if ($serialKey->status === 'suspended') {
            try {
                // Utiliser DB::transaction pour s'assurer que la modification est bien enregistrée
                DB::transaction(function() use ($serialKey) {
                    // Modification directe via requête SQL pour éviter tout problème de cache ou de modèle
                    DB::table('serial_keys')
                        ->where('id', $serialKey->id)
                        ->update(['status' => 'active']);
                    
                    // Forcer le rechargement du modèle depuis la base de données
                    $serialKey->refresh();
                    
                    // Vérifier que le statut a bien été mis à jour
                    \Log::info('Après mise à jour, statut de la clé #' . $serialKey->id . ': ' . $serialKey->status);
                });
                
                // Enregistrer l'action dans l'historique
                $this->historyService->logAction(
                    $serialKey,
                    'reactivate',
                    'Réactivation de la clé de licence'
                );
                
                return redirect()
                    ->route('admin.serial-keys.show', $serialKey)
                    ->with('success', 'Clé de licence réactivée avec succès. Nouveau statut: ' . $serialKey->status);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la réactivation de la clé #' . $serialKey->id . ': ' . $e->getMessage());
                
                return redirect()
                    ->route('admin.serial-keys.show', $serialKey)
                    ->with('error', 'Erreur lors de la réactivation de la clé: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('admin.serial-keys.show', $serialKey)
            ->with('error', 'Impossible de réactiver une clé non suspendue. Statut actuel: ' . $serialKey->status);
    }
}