{{-- Indicateur de mode de licence --}}
<div class="licence-mode-indicator mb-3">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    @if($licenceMode === 'saas')
                        <div class="badge bg-primary me-2">
                            <i class="fas fa-cloud"></i> Mode SaaS
                        </div>
                    @else
                        <div class="badge bg-success me-2">
                            <i class="fas fa-user"></i> Mode Mono-compte
                        </div>
                    @endif
                    
                    <small class="text-muted">
                        {{ $licenceModeLabel }}
                    </small>
                </div>
                
                <div class="d-flex align-items-center">
                    @if(isset($currentLimits))
                        <div class="me-3">
                            <small class="text-muted d-block">Projets</small>
                            <span class="fw-bold">
                                @if($currentLimits['projects'] === null)
                                    ∞
                                @else
                                    {{ $currentLimits['projects'] }}
                                @endif
                            </span>
                        </div>
                        
                        @if($licenceMode === 'saas')
                            <div class="me-3">
                                <small class="text-muted d-block">Tenants</small>
                                <span class="fw-bold">
                                    @if($currentLimits['tenants'] === null)
                                        ∞
                                    @else
                                        {{ $currentLimits['tenants'] }}
                                    @endif
                                </span>
                            </div>
                        @endif
                    @endif
                    
                    <a href="{{ route('admin.licence-mode.dashboard') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-cog"></i> Gérer
                    </a>
                </div>
            </div>
            
            {{-- Barre de progression pour les limites --}}
            @if(isset($usageStats) && isset($currentLimits))
                <div class="mt-2">
                    @foreach(['projects', 'tenants'] as $item)
                        @if(isset($currentLimits[$item]) && $currentLimits[$item] !== null)
                            @php
                                $current = $usageStats[$item . '_count'] ?? 0;
                                $max = $currentLimits[$item];
                                $percentage = $max > 0 ? min(($current / $max) * 100, 100) : 0;
                                $colorClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                            @endphp
                            
                            <div class="mb-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted text-capitalize">{{ $item }}</small>
                                    <small class="text-muted">{{ $current }}/{{ $max }}</small>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar {{ $colorClass }}" 
                                         role="progressbar" 
                                         style="width: {{ $percentage }}%" 
                                         aria-valuenow="{{ $current }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="{{ $max }}">
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Styles CSS --}}
<style>
.licence-mode-indicator .badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.licence-mode-indicator .progress {
    border-radius: 2px;
}

.licence-mode-indicator .card {
    transition: all 0.2s ease-in-out;
}

.licence-mode-indicator .card:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>