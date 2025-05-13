@extends('admin.layouts.app')

@section('title', 'Informations de version')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Version 3.3.1</h5>
                    <p class="text-muted">Date de sortie : 10 Avril 2025</p>

                    <div class="mt-4">
                        <h6 class="fw-bold">Améliorations</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Optimisation des requêtes de base de données</li>
                            <li class="mb-2"><i class="fas fa-arrow-right text-success me-2"></i>Amélioration de la gestion des sessions</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold text-warning">Corrections de bugs</h6>
                        <div class="alert alert-warning">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Correction des problèmes de timezone</li>
                                <li class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Correction des erreurs de validation des licences</li>
                                <li class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Correction des problèmes d'affichage dans l'interface</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold">Instructions de mise à jour</h6>
                        <div class="alert alert-info">
                            <p class="mb-2">Pour mettre à jour vers cette version, exécutez les commandes suivantes :</p>
                            <pre class="mb-0"><code>php artisan migrate
php artisan optimize:clear
php artisan cache:clear</code></pre>
                            <p class="mt-2 mb-0"><strong>Note :</strong> Assurez-vous de sauvegarder vos données avant la mise à jour.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 