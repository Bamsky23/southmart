@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard Monitoring SouthMart')

@section('content')
<div class="row g-4 mb-4">
    <!-- Total Nodes Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="dashboard-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted fw-medium small d-block mb-1">Total Node Database</span>
                <h3 class="fw-bold m-0 text-dark">{{ $totalNodes + 1 }}</h3>
                <small class="text-muted">1 Pusat, {{ $totalNodes }} Cabang</small>
            </div>
            <div class="icon-box-accent icon-box-blue">
                <i class="bi bi-diagram-3-fill"></i>
            </div>
        </div>
    </div>
    
    <!-- Online Nodes Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="dashboard-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted fw-medium small d-block mb-1">Node Online</span>
                <h3 class="fw-bold m-0 text-success">{{ $onlineNodes + 1 }}</h3>
                <small class="text-muted">Aktif & Sinkron</small>
            </div>
            <div class="icon-box-accent icon-box-green">
                <i class="bi bi-cloud-check-fill"></i>
            </div>
        </div>
    </div>

    <!-- Offline Nodes Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="dashboard-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted fw-medium small d-block mb-1">Node Offline</span>
                <h3 class="fw-bold m-0 {{ $offlineNodes > 0 ? 'text-danger' : 'text-muted' }}">{{ $offlineNodes }}</h3>
                <small class="text-muted">Terputus / Terisolasi</small>
            </div>
            <div class="icon-box-accent {{ $offlineNodes > 0 ? 'icon-box-red' : 'icon-box-sky' }}">
                <i class="bi bi-cloud-slash-fill"></i>
            </div>
        </div>
    </div>

    <!-- Replication Rate Card -->
    <div class="col-xl-3 col-sm-6">
        <div class="dashboard-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted fw-medium small d-block mb-1">Rasio Konsistensi</span>
                <h3 class="fw-bold m-0 text-primary">{{ number_format($consistencyRate, 1) }}%</h3>
                <small class="text-muted">Akurasi Data Replikasi</small>
            </div>
            <div class="icon-box-accent icon-box-blue">
                <i class="bi bi-shield-check-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- SYNCHRONIZATION PANEL -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-sliders me-2 text-primary"></i>Panel Sinkronisasi & Replikasi</h5>
                <p class="text-muted m-0 small">Kendalikan alur replikasi master data dan sinkronisasi transaksi secara terdistribusi.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('admin.sync-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm px-3 py-2 fw-semibold">
                        <i class="bi bi-arrow-repeat me-1.5"></i> Sinkronisasi Transaksi
                    </button>
                </form>
                <form action="{{ route('admin.run-replication') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm px-3 py-2 fw-semibold">
                        <i class="bi bi-copy me-1.5"></i> Jalankan Replikasi Master
                    </button>
                </form>
                <form action="{{ route('admin.check-consistency') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold">
                        <i class="bi bi-shield-check me-1.5"></i> Uji Konsistensi Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- NODE MONITORING (Poin Status Koneksi Node) -->
<h5 class="fw-bold text-dark mb-3"><i class="bi bi-hdd-network-fill me-2 text-primary"></i>Poin Status Koneksi Node Cabang</h5>
<div class="row g-3 mb-4">
    @foreach($nodes as $node)
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-bold text-dark m-0">{{ $node->name }}</h6>
                        <small class="text-muted text-uppercase" style="font-size: 0.75rem;">Kode: {{ $node->code }}</small>
                    </div>
                    @if($node->node_status === 'online')
                        <span class="badge bg-success text-white px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                            <span class="indicator online"></span> Online
                        </span>
                    @else
                        <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                            <span class="indicator offline"></span> Offline
                        </span>
                    @endif
                </div>

                <div class="border-top border-bottom py-2 my-2 fs-7" style="font-size: 0.85rem;">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Status DB:</span>
                        <strong class="{{ $node->db_status === 'online' ? 'text-success' : 'text-danger' }}">
                            {{ strtoupper($node->db_status) }}
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">IP Address:</span>
                        <strong class="text-dark">{{ $node->ip_address ?? '-' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Terakhir Sinkron:</span>
                        <strong class="text-dark">{{ $node->last_sync ? date('d-m-Y H:i:s', strtotime($node->last_sync)) : 'Belum pernah' }}</strong>
                    </div>
                </div>

                <!-- Simulation Toggle Switches -->
                <div class="d-flex justify-content-between align-items-center mt-2.5">
                    <span class="small text-muted fw-medium">Koneksi:</span>
                    <form action="{{ route('admin.toggle-node', $node->id) }}" method="POST">
                        @csrf
                        @if($node->node_status === 'online')
                            <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2 fw-semibold" style="font-size: 0.75rem;">
                                <i class="bi bi-cloud-slash me-1"></i> Putuskan Node
                            </button>
                        @else
                            <button type="submit" class="btn btn-xs btn-outline-success py-1 px-2 fw-semibold" style="font-size: 0.75rem;">
                                <i class="bi bi-cloud-check me-1"></i> Hubungkan Node
                            </button>
                        @endif
                    </form>
                </div>

            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    <!-- CHART SECTION -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>Statistik Penjualan per Cabang</h5>
                <a href="{{ route('admin.national-sales') }}" class="btn btn-link btn-sm text-decoration-none" style="color: var(--secondary-accent)">Lihat Rincian</a>
            </div>
            <div class="card-body p-4">
                <div style="position: relative; height: 320px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- SALES TOTAL SUMMARY & DIRECT ACCESS -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <h5 class="fw-bold text-dark mb-4"><i class="bi bi-cash-stack me-2 text-primary"></i>Ringkasan Omzet Nasional</h5>
                    
                    <div class="mb-4">
                        <span class="text-muted small d-block">TOTAL OMZET BRUTO NASIONAL</span>
                        <h2 class="fw-extrabold text-primary m-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h2>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted d-block small" style="font-size: 0.75rem;">TOTAL TRANSAKSI</span>
                                <strong class="fs-5 text-dark">{{ number_format($totalTxCount, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted d-block small" style="font-size: 0.75rem;">PRODUK TERJUAL</span>
                                <strong class="fs-5 text-dark">{{ number_format($totalQtySold, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <a href="{{ route('admin.cross-node-query') }}?run=1" class="btn btn-primary py-2.5 fw-bold shadow-sm">
                        <i class="bi bi-database-fill-gear me-2"></i> Jalankan Query Lintas Node
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SYNC LOGS & SYSTEM ACTIVITY LOGS -->
<div class="row g-4">
    <!-- Synchronization Logs -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-journal-text me-2 text-primary"></i>Log Sinkronisasi Transaksi Terbaru</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cabang</th>
                                <th>Aksi</th>
                                <th class="text-center">Jumlah Baris</th>
                                <th>Waktu</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($syncLogs as $log)
                            <tr>
                                <td><strong>{{ $log->branch_name }}</strong></td>
                                <td><span class="text-uppercase text-muted small">{{ $log->action }}</span></td>
                                <td class="text-center fw-medium">{{ $log->records_synced }}</td>
                                <td>{{ date('d-m-Y H:i', strtotime($log->created_at)) }}</td>
                                <td class="text-center">
                                    @if($log->status === 'success')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">SUKSES</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">GAGAL</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada log sinkronisasi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity logs (Audit Trail) -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold m-0 text-dark"><i class="bi bi-card-list me-2 text-primary"></i>Audit Trail (Log Aktivitas Sistem)</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-0">
                        <thead class="table-light">
                            <tr>
                                <th>User/Cabang</th>
                                <th>Aktivitas</th>
                                <th>Deskripsi</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody id="activity-log-table-body">
                            @forelse($activityLogs as $log)
                            <tr data-log-id="{{ $log->id }}">
                                <td>
                                    <strong>{{ $log->user_name ?? 'Sistem' }}</strong>
                                    <div class="text-muted small" style="font-size: 0.7rem;">{{ $log->branch_name ?? 'Pusat' }}</div>
                                </td>
                                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $log->activity }}</span></td>
                                <td><span class="small">{{ $log->description }}</span></td>
                                <td style="font-size: 0.8rem;">{{ date('d-m-Y H:i', strtotime($log->created_at)) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada aktivitas tercatat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    @keyframes flashNewRow {
        0% { background-color: rgba(59, 130, 246, 0.25); }
        100% { background-color: transparent; }
    }
    .new-log-row {
        animation: flashNewRow 3s ease-out;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Chart Initialization
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        const branchNames = {!! json_encode($salesByBranch->pluck('name')) !!};
        const revenues = {!! json_encode($salesByBranch->pluck('revenue')) !!};

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: branchNames,
                datasets: [{
                    label: 'Omzet Penjualan (Rp)',
                    data: revenues,
                    backgroundColor: [
                        'rgba(0, 71, 171, 0.85)',
                        'rgba(59, 130, 246, 0.85)',
                        'rgba(96, 165, 250, 0.85)',
                        'rgba(147, 197, 253, 0.85)',
                        'rgba(191, 219, 254, 0.85)'
                    ],
                    borderColor: [
                        '#0047AB',
                        '#3B82F6',
                        '#60A5FA',
                        '#93C5FD',
                        '#BFDBFE'
                    ],
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#F1F5F9'
                        },
                        ticks: {
                            color: '#64748B',
                            callback: function(value, index, ticks) {
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748B',
                            font: {
                                weight: '500'
                            }
                        }
                    }
                }
            }
        });

        // Real-time Activity Logs Polling
        let displayedLogIds = Array.from(document.querySelectorAll('[data-log-id]')).map(el => parseInt(el.getAttribute('data-log-id')));
        
        function pollActivityLogs() {
            fetch("{{ route('admin.activity-logs.realtime') }}")
                .then(response => response.json())
                .then(logs => {
                    const tbody = document.getElementById('activity-log-table-body');
                    if (!tbody) return;

                    if (logs.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada aktivitas tercatat.</td>
                            </tr>
                        `;
                        displayedLogIds = [];
                        return;
                    }

                    // Check if there is any new log
                    const fetchedIds = logs.map(log => log.id);
                    const hasNewLogs = fetchedIds.some(id => !displayedLogIds.includes(id));

                    if (hasNewLogs) {
                        let html = '';
                        logs.forEach(log => {
                            const isNew = !displayedLogIds.includes(log.id) && displayedLogIds.length > 0;
                            const rowClass = isNew ? 'new-log-row' : '';
                            const userName = log.user_name || 'Sistem';
                            const branchName = log.branch_name || 'Pusat';
                            
                            html += `
                                <tr data-log-id="${log.id}" class="${rowClass}">
                                    <td>
                                        <strong>${userName}</strong>
                                        <div class="text-muted small" style="font-size: 0.7rem;">${branchName}</div>
                                    </td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">${log.activity}</span></td>
                                    <td><span class="small">${log.description}</span></td>
                                    <td style="font-size: 0.8rem;">${log.formatted_time}</td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                        displayedLogIds = fetchedIds;
                    }
                })
                .catch(err => console.error("Error polling activity logs:", err));
        }

        // Poll every 3 seconds
        setInterval(pollActivityLogs, 3000);
    });
</script>
@endsection
