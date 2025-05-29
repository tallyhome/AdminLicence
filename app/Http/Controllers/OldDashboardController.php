<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

/**
 * Contrôleur pour rediriger l'ancien dashboard vers le nouveau
 */
class OldDashboardController extends Controller
{
    /**
     * Rediriger toutes les requêtes de l'ancien dashboard vers le nouveau
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(Request $request)
    {
        // Journaliser la redirection
        Log::info('Redirection de l\'ancien dashboard vers le nouveau', [
            'url' => $request->fullUrl(),
            'ip' => $request->ip()
        ]);
        
        // Rediriger vers le nouveau dashboard
        return redirect()->route('admin.dashboard');
    }
    
    /**
     * Rediriger spécifiquement les pages d'optimisation
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectOptimization(Request $request)
    {
        // Journaliser la redirection
        Log::info('Redirection de l\'ancienne page d\'optimisation vers la nouvelle', [
            'url' => $request->fullUrl(),
            'ip' => $request->ip()
        ]);
        
        // Rediriger vers la nouvelle page d'optimisation
        return redirect()->route('admin.settings.optimization');
    }
    
    /**
     * Rediriger spécifiquement les pages de diagnostic API
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectApiDiagnostic(Request $request)
    {
        // Journaliser la redirection
        Log::info('Redirection de l\'ancienne page de diagnostic API vers la nouvelle', [
            'url' => $request->fullUrl(),
            'ip' => $request->ip()
        ]);
        
        // Rediriger vers la nouvelle page de diagnostic API
        return redirect()->route('admin.settings.api-diagnostic');
    }
}
