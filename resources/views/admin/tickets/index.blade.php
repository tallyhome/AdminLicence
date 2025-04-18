@extends('admin.layouts.app')

@section('title', 'Gestion des tickets de support')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">Tickets de support</h1>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Filtres</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tickets.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Statut</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Tous</option>
                                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ouvert</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Fermé</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Sujet</th>
                                    <th>Statut</th>
                                    <th>Priorité</th>
                                    <th>Créé le</th>
                                    <th>Dernière réponse</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                    <tr>
                                        <td>{{ $ticket->id }}</td>
                                        <td>{{ $ticket->client->name }}</td>
                                        <td>{{ $ticket->subject }}</td>
                                        <td>
                                            <span class="badge bg-{{ $ticket->status_color }}">{{ $ticket->status_label }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $ticket->priority_color }}">{{ $ticket->priority_label }}</span>
                                        </td>
                                        <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $ticket->last_reply_at ? $ticket->last_reply_at->format('d/m/Y H:i') : 'Aucune' }}</td>
                                        <td>
                                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Aucun ticket trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $tickets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection