@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Plans d\'abonnement') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="text-center mb-4">{{ __('Choisissez le plan qui vous convient') }}</h4>
                            <p class="text-center text-muted">
                                {{ __('Tous nos plans incluent l\'accès à toutes les fonctionnalités de base. Les plans supérieurs offrent plus de comptes et de fonctionnalités avancées.') }}
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        @forelse ($plans as $plan)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 {{ $plan->is_featured ? 'border-primary' : '' }}">
                                    @if ($plan->is_featured)
                                        <div class="card-header bg-primary text-white text-center">
                                            <span class="badge bg-warning">{{ __('Recommandé') }}</span>
                                        </div>
                                    @endif
                                    <div class="card-body text-center">
                                        <h5 class="card-title">{{ $plan->name }}</h5>
                                        <div class="my-4">
                                            <span class="display-4">{{ number_format($plan->price, 0) }}</span>
                                            <span class="text-muted">€ / {{ $plan->billing_cycle_text }}</span>
                                        </div>
                                        
                                        <ul class="list-unstyled">
                                            @if(is_array($plan->features))
                                                @foreach($plan->features as $feature)
                                                    <li class="mb-2">
                                                        <i class="fas fa-check text-success"></i> {{ $feature }}
                                                    </li>
                                                @endforeach
                                            @endif
                                            <li class="mb-2">
                                                <i class="fas fa-check text-success"></i> 
                                                {{ __(':count comptes', ['count' => $plan->max_tenants]) }}
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 text-center">
                                        <a href="{{ route('billing.checkout', $plan->id) }}" class="btn {{ $plan->is_featured ? 'btn-primary' : 'btn-outline-primary' }} btn-lg w-100">
                                            {{ __('Choisir ce plan') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    {{ __('Aucun plan d\'abonnement disponible pour le moment.') }}
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">{{ __('Questions fréquentes') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="accordion" id="faqAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="faqHeading1">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="false" aria-controls="faqCollapse1">
                                                    {{ __('Comment fonctionne la facturation ?') }}
                                                </button>
                                            </h2>
                                            <div id="faqCollapse1" class="accordion-collapse collapse" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body">
                                                    {{ __('La facturation est effectuée mensuellement ou selon la période choisie. Vous recevrez une facture par email et pourrez la consulter dans votre espace client.') }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="faqHeading2">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                                    {{ __('Puis-je changer de plan à tout moment ?') }}
                                                </button>
                                            </h2>
                                            <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body">
                                                    {{ __('Oui, vous pouvez changer de plan à tout moment. Si vous passez à un plan supérieur, la différence sera facturée au prorata. Si vous passez à un plan inférieur, le changement prendra effet à la fin de votre période de facturation actuelle.') }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="faqHeading3">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                                    {{ __('Comment puis-je annuler mon abonnement ?') }}
                                                </button>
                                            </h2>
                                            <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body">
                                                    {{ __('Vous pouvez annuler votre abonnement à tout moment depuis votre espace client. L\'annulation prendra effet à la fin de votre période de facturation actuelle.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
