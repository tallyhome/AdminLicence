<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SerialKey;
use App\Models\Tenant;
use App\Models\Client;
use App\Services\LicenceModeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LicenceModeController extends Controller
{
    /**
     * @var LicenceModeService
     */
    protected $licenceModeService;
    
    /**
     * Constructeur
     *
     * @param LicenceModeService $licenceModeService
     */
    public function __construct(LicenceModeService $licenceModeService)
    {
        $this->licenceModeService = $licenceModeService;
    }
    
    /**
     * Affiche le tableau de bord du mode de licence
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $currentMode = $this->licenceModeService->getCurrentMode();
        $features = $this->licenceModeService->getAvailableFeatures();
        $limits = $this->licenceModeService->getLimits();
        
        // Statistiques selon le mode
        $stats = $this->getStatistics();
        
        // Licences actives
        $activeLicences = SerialKey::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('project')
            ->get();
        
        return view('admin.licence-mode.dashboard', compact(
            'currentMode',
            'features',
            'limits',
            'stats',
            'activeLicences'
        ));
    }
    
    /**
     * Affiche les informations détaillées sur le mode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getModeInfo()
    {
        $currentMode = $this->licenceModeService->getCurrentMode();
        $features = $this->licenceModeService->getAvailableFeatures();
        $limits = $this->licenceModeService->getLimits();
        $stats = $this->getStatistics();
        
        return response()->json([
            'mode' => $currentMode,
            'mode_label' => $currentMode === LicenceModeService::MODE_SAAS ? 'SaaS' : 'Mono-compte',
            'features' => $features,
            'limits' => $limits,
            'statistics' => $stats,
            'is_saas' => $this->licenceModeService->isSaasMode(),
            'is_single_account' => $this->licenceModeService->isSingleAccountMode(),
        ]);
    }
    
    /**
     * Force la mise à jour du mode de licence
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshMode()
    {
        try {
            $oldMode = $this->licenceModeService->getCurrentMode();
            $newMode = $this->licenceModeService->refreshMode();
            
            Log::info('Mode de licence actualisé', [
                'old_mode' => $oldMode,
                'new_mode' => $newMode,
                'admin_id' => auth('admin')->id(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Mode de licence actualisé avec succès',
                'old_mode' => $oldMode,
                'new_mode' => $newMode,
                'mode_changed' => $oldMode !== $newMode,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'actualisation du mode de licence: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'actualisation du mode de licence',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Simule un changement de mode (pour les tests)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function simulateMode(Request $request)
    {
        if (!app()->environment('local')) {
            return response()->json([
                'success' => false,
                'message' => 'Cette fonctionnalité n\'est disponible qu\'en environnement de développement',
            ], 403);
        }
        
        $request->validate([
            'mode' => 'required|in:' . LicenceModeService::MODE_SINGLE_ACCOUNT . ',' . LicenceModeService::MODE_SAAS,
        ]);
        
        $mode = $request->input('mode');
        
        // Mettre en cache le mode simulé
        Cache::put('simulated_licence_mode', $mode, 3600); // 1 heure
        
        return response()->json([
            'success' => true,
            'message' => "Mode simulé activé: {$mode}",
            'simulated_mode' => $mode,
        ]);
    }
    
    /**
     * Désactive la simulation de mode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearSimulation()
    {
        Cache::forget('simulated_licence_mode');
        
        return response()->json([
            'success' => true,
            'message' => 'Simulation de mode désactivée',
        ]);
    }
    
    /**
     * Vérifie les limites selon le mode actuel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkLimits()
    {
        $limits = $this->licenceModeService->getLimits();
        $stats = $this->getStatistics();
        
        $limitChecks = [];
        
        foreach ($limits as $limitType => $limitValue) {
            if ($limitValue === null) {
                $limitChecks[$limitType] = [
                    'limit' => null,
                    'current' => $stats[$limitType] ?? 0,
                    'reached' => false,
                    'percentage' => 0,
                ];
            } else {
                $current = $stats[$limitType] ?? 0;
                $reached = $this->licenceModeService->isLimitReached($limitType, $current);
                $percentage = $limitValue > 0 ? min(100, ($current / $limitValue) * 100) : 0;
                
                $limitChecks[$limitType] = [
                    'limit' => $limitValue,
                    'current' => $current,
                    'reached' => $reached,
                    'percentage' => round($percentage, 2),
                ];
            }
        }
        
        return response()->json([
            'limits' => $limitChecks,
            'mode' => $this->licenceModeService->getCurrentMode(),
        ]);
    }
    
    /**
     * Obtient les statistiques selon le mode
     *
     * @return array
     */
    protected function getStatistics(): array
    {
        $stats = [
            'max_tenants' => Tenant::count(),
            'max_clients_per_tenant' => 0,
            'max_projects' => \App\Models\Project::count(),
            'max_serial_keys' => SerialKey::count(),
            'max_api_calls_per_minute' => 0, // À implémenter avec un système de monitoring
            'storage_limit_gb' => 0, // À implémenter avec un système de monitoring
        ];
        
        // Calculer le maximum de clients par tenant
        if ($stats['max_tenants'] > 0) {
            $maxClientsPerTenant = DB::table('clients')
                ->select('tenant_id', DB::raw('COUNT(*) as client_count'))
                ->groupBy('tenant_id')
                ->orderBy('client_count', 'desc')
                ->first();
            
            $stats['max_clients_per_tenant'] = $maxClientsPerTenant ? $maxClientsPerTenant->client_count : 0;
        }
        
        return $stats;
    }
    
    /**
     * Obtient les recommandations selon le mode actuel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecommendations()
    {
        $currentMode = $this->licenceModeService->getCurrentMode();
        $stats = $this->getStatistics();
        $limits = $this->licenceModeService->getLimits();
        
        $recommendations = [];
        
        if ($currentMode === LicenceModeService::MODE_SINGLE_ACCOUNT) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Mode mono-compte actif',
                'message' => 'Votre application fonctionne en mode mono-compte. Certaines fonctionnalités SaaS ne sont pas disponibles.',
                'action' => 'Considérez une mise à niveau vers une licence multi-comptes pour débloquer toutes les fonctionnalités.',
            ];
            
            // Vérifier les limites
            foreach ($limits as $limitType => $limitValue) {
                if ($limitValue !== null && isset($stats[$limitType])) {
                    $current = $stats[$limitType];
                    $percentage = ($current / $limitValue) * 100;
                    
                    if ($percentage > 80) {
                        $recommendations[] = [
                            'type' => 'warning',
                            'title' => 'Limite bientôt atteinte',
                            'message' => "Vous approchez de la limite pour {$limitType}: {$current}/{$limitValue}",
                            'action' => 'Envisagez une mise à niveau de votre licence.',
                        ];
                    }
                }
            }
        } else {
            $recommendations[] = [
                'type' => 'success',
                'title' => 'Mode SaaS actif',
                'message' => 'Votre application fonctionne en mode SaaS avec toutes les fonctionnalités disponibles.',
                'action' => 'Profitez de toutes les fonctionnalités avancées disponibles.',
            ];
        }
        
        return response()->json([
            'recommendations' => $recommendations,
            'mode' => $currentMode,
        ]);
    }
}