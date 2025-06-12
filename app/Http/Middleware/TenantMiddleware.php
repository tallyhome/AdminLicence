<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Récupérer l'ID du tenant depuis la session, l'URL ou l'utilisateur authentifié
        $tenantId = $this->resolveTenantId($request);
        
        if (!$tenantId) {
            // Si aucun tenant n'est trouvé et que c'est requis, rediriger vers la sélection de tenant
            if ($request->route()->named('tenant.select') || $request->route()->named('tenant.switch')) {
                return $next($request);
            }
            
            return redirect()->route('tenant.select');
        }
        
        // Récupérer le tenant
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant || $tenant->status !== 'active') {
            // Si le tenant n'existe pas ou n'est pas actif, effacer la session et rediriger
            Session::forget('tenant_id');
            
            if ($request->route()->named('tenant.select') || $request->route()->named('tenant.switch')) {
                return $next($request);
            }
            
            return redirect()->route('tenant.select')
                ->with('error', 'Le compte demandé n\'est pas disponible.');
        }
        
        // Vérifier si l'utilisateur a accès à ce tenant
        if (Auth::check() && !$this->userHasAccessToTenant(Auth::user(), $tenant)) {
            return redirect()->route('tenant.select')
                ->with('error', 'Vous n\'avez pas accès à ce compte.');
        }
        
        // Stocker le tenant dans la session
        Session::put('tenant_id', $tenant->id);
        
        // Configurer le contexte du tenant pour cette requête
        $this->configureTenantContext($tenant);
        
        // Ajouter le tenant à la requête pour y accéder facilement dans les contrôleurs
        $request->merge(['tenant' => $tenant]);
        
        return $next($request);
    }
    
    /**
     * Résoudre l'ID du tenant à partir de différentes sources
     */
    protected function resolveTenantId(Request $request): ?int
    {
        // Priorité 1: Paramètre de requête pour le changement explicite de tenant
        if ($request->has('switch_tenant')) {
            return (int) $request->input('switch_tenant');
        }
        
        // Priorité 2: Session
        if (Session::has('tenant_id')) {
            return (int) Session::get('tenant_id');
        }
        
        // Priorité 3: Utilisateur authentifié (son tenant par défaut)
        if (Auth::check() && Auth::user()->default_tenant_id) {
            return (int) Auth::user()->default_tenant_id;
        }
        
        // Priorité 4: Premier tenant disponible pour l'utilisateur
        if (Auth::check()) {
            $firstTenant = Auth::user()->tenants()->where('status', 'active')->first();
            if ($firstTenant) {
                return (int) $firstTenant->id;
            }
        }
        
        return null;
    }
    
    /**
     * Vérifier si l'utilisateur a accès au tenant
     */
    protected function userHasAccessToTenant($user, Tenant $tenant): bool
    {
        // Vérifier si l'utilisateur est associé à ce tenant
        return $user->tenants()->where('tenants.id', $tenant->id)->exists();
    }
    
    /**
     * Configurer le contexte du tenant pour cette requête
     */
    protected function configureTenantContext(Tenant $tenant): void
    {
        // Définir le préfixe de base de données pour ce tenant si nécessaire
        // Config::set('database.connections.tenant.prefix', 'tenant_' . $tenant->id . '_');
        
        // Stocker le tenant actuel dans le conteneur de service pour y accéder globalement
        app()->instance('tenant', $tenant);
        
        // Définir une variable globale pour les vues
        view()->share('currentTenant', $tenant);
        
        // Journaliser le changement de contexte tenant en mode debug
        if (config('app.debug')) {
            Log::debug('Contexte tenant configuré', ['tenant_id' => $tenant->id, 'tenant_name' => $tenant->name]);
        }
    }
}
