@extends('admin.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ t('common.notifications') }}</h5>
                    <button id="mark-all-as-read" class="btn btn-primary btn-sm">
                        <i class="fas fa-check-double me-2"></i>{{ t('common.mark_all_as_read') }}
                    </button>
                </div>
                <div class="card-body">

                    @if($notifications->count() > 0)
                        <div class="list-group">
                            @foreach($notifications as $notification)
                                <div class="list-group-item {{ $notification->read_at ? '' : 'list-group-item-light' }}">
                                    <div class="d-flex">
                                        @php
                                            $data = $notification->data;
                                            $iconClass = 'fas fa-bell text-secondary';
                                            $title = 'Notification';
                                            
                                            if (isset($data['action'])) {
                                                $iconClass = 'fas fa-key text-primary';
                                                $title = 'Changement de statut de licence';
                                            } elseif (isset($data['ticket_id'])) {
                                                $iconClass = 'fas fa-ticket-alt text-warning';
                                                $title = 'Nouveau ticket de support';
                                            } elseif (isset($data['invoice_id'])) {
                                                $iconClass = 'fas fa-money-bill text-success';
                                                $title = 'Nouveau paiement';
                                            }
                                        @endphp
                                        
                                        <div class="me-3">
                                            <i class="{{ $iconClass }} fs-4"></i>
                                        </div>
                                        
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <h5 class="mb-1">{{ $title }}</h5>
                                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                
                                            @if(isset($data['action']))
                                                <p class="mb-1">
                                                    @php
                                                        $statusMessages = [
                                                            'revoked' => 'révoquée',
                                                            'suspended' => 'suspendue',
                                                            'expired' => 'expirée',
                                                            'activated' => 'activée',
                                                            'renewed' => 'renouvelée'
                                                        ];
                                                        $status = $statusMessages[$data['action']] ?? $data['action'];
                                                    @endphp
                                                    La licence <strong>{{ $data['serial_key'] ?? 'N/A' }}</strong> a été {{ $status }}.
                                                </p>
                                                <div class="mt-2">
                                                    @if(!$notification->read_at)
                                                        <button class="mark-as-read btn btn-sm btn-outline-primary me-2" data-id="{{ $notification->id }}">
                                                            Marquer comme lu
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('admin.serial-keys.show', $data['serial_key_id']) }}" class="btn btn-sm btn-outline-secondary">
                                                        Voir les détails
                                                    </a>
                                                </div>
                                @elseif(isset($data['ticket_id']))
                                    <p class="mb-1">
                                        Nouveau ticket #{{ $data['ticket_id'] }}: {{ $data['subject'] ?? 'Sans sujet' }}
                                    </p>
                                    <div class="mt-2">
                                        @if(!$notification->read_at)
                                            <button class="mark-as-read btn btn-sm btn-outline-primary me-2" data-id="{{ $notification->id }}">
                                                Marquer comme lu
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.tickets.show', $data['ticket_id']) }}" class="btn btn-sm btn-outline-secondary">
                                            Voir le ticket
                                        </a>
                                    </div>
                                @elseif(isset($data['invoice_id']))
                                    <p class="mb-1">
                                        Paiement de {{ $data['amount'] ?? '0' }}€ reçu de {{ $data['client_name'] ?? 'Client' }}
                                    </p>
                                    <div class="mt-2">
                                        @if(!$notification->read_at)
                                            <button class="mark-as-read btn btn-sm btn-outline-primary me-2" data-id="{{ $notification->id }}">
                                                Marquer comme lu
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.invoices.show', $data['invoice_id']) }}" class="btn btn-sm btn-outline-secondary">
                                            Voir la facture
                                        </a>
                                    </div>
                                            @else
                                                <p class="mb-1">
                                                    {{ $data['message'] ?? 'Aucun détail disponible' }}
                                                </p>
                                                <div class="mt-2">
                                                    @if(!$notification->read_at)
                                                        <button class="mark-as-read btn btn-sm btn-outline-primary" data-id="{{ $notification->id }}">
                                                            Marquer comme lu
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-3">
                            {{ $notifications->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fs-1 mb-3 text-muted"></i>
                            <p>Vous n'avez aucune notification pour le moment.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Marquer une notification comme lue
        document.querySelectorAll('.mark-as-read').forEach(button => {
            button.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                fetch(`/admin/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Changer l'apparence de la notification
                        this.closest('.bg-blue-50').classList.remove('bg-blue-50');
                        this.remove();
                    }
                });
            });
        });

        // Marquer toutes les notifications comme lues
        document.getElementById('mark-all-as-read').addEventListener('click', function() {
            fetch('/admin/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Rafraîchir la page pour montrer les changements
                    window.location.reload();
                }
            });
        });
    });
</script>
@endpush