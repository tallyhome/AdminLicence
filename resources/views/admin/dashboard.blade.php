@extends('admin.layouts.app')

@section('title', t('dashboard.title'))

@section('styles')
<style>
    .card-link {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .card-link:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .card-link .card {
        transition: border-color 0.2s ease-in-out;
    }
    .card-link:hover .card {
        border-color: #007bff;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('license_warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <strong><i class="fas fa-exclamation-triangle"></i> {{ session('license_warning') }}</strong>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- {{ t('dashboard.statistics') }} -->
    <div class="row">
        <div class="col-xl col-lg-4 col-md-6 mb-4">
            <a href="{{ route('admin.serial-keys.index') }}" class="text-decoration-none card-link">
                <div class="card border-left-primary shadow h-100 py-2 dashboard-stat-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    {{ t('dashboard.total_keys') }}</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_keys'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-key fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6 mb-4">
            <a href="{{ route('admin.serial-keys.index', ['status' => 'active']) }}" class="text-decoration-none card-link">
                <div class="card border-left-success shadow h-100 py-2 dashboard-stat-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    {{ t('dashboard.active_keys') }}</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_keys'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6 mb-4">
            <a href="{{ route('admin.serial-keys.index', ['used' => 'true']) }}" class="text-decoration-none card-link">
                <div class="card border-left-info shadow h-100 py-2 dashboard-stat-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    {{ t('dashboard.used_keys') }}</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['used_keys'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-laptop fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-6 col-md-6 mb-4">
            <a href="{{ route('admin.serial-keys.index', ['status' => 'suspended']) }}" class="text-decoration-none card-link">
                <div class="card border-left-warning shadow h-100 py-2 dashboard-stat-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    {{ t('dashboard.suspended_keys') }}</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['suspended_keys'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-6 col-md-6 mb-4">
            <a href="{{ route('admin.serial-keys.index', ['status' => 'revoked']) }}" class="text-decoration-none card-link">
                <div class="card border-left-danger shadow h-100 py-2 dashboard-stat-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    {{ t('dashboard.revoked_keys') }}</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['revoked_keys'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-ban fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- {{ t('dashboard.keys_usage_by_project') }} -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">{{ t('dashboard.keys_usage_by_project') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>{{ t('projects.project') }}</th>
                                    <th>{{ t('dashboard.total_keys') }}</th>
                                    <th>{{ t('dashboard.used_keys') }}</th>
                                    <th>{{ t('dashboard.available_keys') }}</th>
                                    <th>{{ t('common.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projectStats as $project)
                                <tr>
                                    <td>{{ $project->name }}</td>
                                    <td>{{ $project->serial_keys_count }}</td>
                                    <td>{{ $project->used_keys_count }}</td>
                                    <td>{{ $project->available_keys_count }}</td>
                                    <td>
                                        @if($project->serial_keys_count > 0)
                                            <div class="progress mb-2" style="height: 20px;">
                                                @php
                                                    $usedPercentage = ($project->used_keys_count / $project->serial_keys_count) * 100;
                                                    $availablePercentage = 100 - $usedPercentage;
                                                    $progressClass = $project->is_running_low ? 'bg-danger' : 'bg-success';
                                                @endphp
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $usedPercentage }}%" 
                                                     aria-valuenow="{{ $usedPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                    {{ round($usedPercentage) }}% {{ t('dashboard.used') }}
                                                </div>
                                                <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $availablePercentage }}%" 
                                                     aria-valuenow="{{ $availablePercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                    {{ round($availablePercentage) }}% {{ t('dashboard.available') }}
                                                </div>
                                            </div>
                                            @if($project->is_running_low)
                                                <span class="badge bg-danger">{{ t('dashboard.low_stock') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ t('dashboard.sufficient_stock') }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">{{ t('dashboard.no_keys') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- {{ t('dashboard.charts') }} -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ t('dashboard.keys_usage_last_30_days') }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="usageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ t('dashboard.distribution_by_project') }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="projectChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- {{ t('dashboard.recent_keys') }} -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ t('dashboard.recent_keys') }}</h6>
            <div>
                <select class="form-select" id="perPageSelect" onchange="window.location.href='{{ route('admin.dashboard') }}?per_page=' + this.value">
                    <option value="10" {{ $validPerPage == 10 ? 'selected' : '' }}>{{ t('pagination.per_page', ['number' => 10]) }}</option>
                    <option value="25" {{ $validPerPage == 25 ? 'selected' : '' }}>{{ t('pagination.per_page', ['number' => 25]) }}</option>
                    <option value="50" {{ $validPerPage == 50 ? 'selected' : '' }}>{{ t('pagination.per_page', ['number' => 50]) }}</option>
                    <option value="100" {{ $validPerPage == 100 ? 'selected' : '' }}>{{ t('pagination.per_page', ['number' => 100]) }}</option>
                    <option value="500" {{ $validPerPage == 500 ? 'selected' : '' }}>{{ t('pagination.per_page', ['number' => 500]) }}</option>
                    <option value="1000" {{ $validPerPage == 1000 ? 'selected' : '' }}>{{ t('pagination.per_page', ['number' => 1000]) }}</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ t('common.serial_keys') }}</th>
                            <th>{{ t('common.projects') }}</th>
                            <th>{{ t('common.status') }}</th>
                            <th>{{ t('common.date') }}</th>
                            <th>{{ t('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentKeys as $key)
                        <tr>
                            <td>{{ $key->serial_key }}</td>
                            <td>{{ $key->project->name }}</td>
                            <td>
                                <span class="badge-status badge-{{ $key->status }}">
                                    {{ t('common.' . $key->status) }}
                                </span>
                            </td>
                            <td>{{ $key->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.serial-keys.show', $key) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-tailwind">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // {{ t('dashboard.usage_chart') }}
    const usageCtx = document.getElementById('usageChart').getContext('2d');
    new Chart(usageCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($usageStats->pluck('date')) !!},
            datasets: [{
                label: '{{ t("dashboard.usage") }}',
                data: {!! json_encode($usageStats->pluck('count')) !!},
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // {{ t('dashboard.project_distribution_chart') }}
    const projectCtx = document.getElementById('projectChart').getContext('2d');
    new Chart(projectCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($projectStats->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($projectStats->pluck('serial_keys_count')) !!},
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });
</script>
@endpush
@endsection