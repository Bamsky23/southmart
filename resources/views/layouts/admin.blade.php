<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - SouthMart Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    @yield('styles')
    <style>
        :root {
            --primary-accent: #0047AB;
            --secondary-accent: #3B82F6;
            --bg-primary: #FFFFFF;
            --bg-sidebar: #0F172A;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* Sidebar styling */
        .sidebar {
            width: 260px;
            background-color: var(--bg-sidebar);
            color: #94A3B8;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            transition: all 0.3s;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand img {
            max-height: 40px;
            object-fit: contain;
        }

        .sidebar-brand h5 {
            color: #FFFFFF;
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .sidebar-menu {
            padding: 1.5rem 0.8rem;
            list-style: none;
            margin: 0;
        }

        .sidebar-item {
            margin-bottom: 0.4rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            color: #94A3B8;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #FFFFFF;
        }

        .sidebar-link.active {
            background-color: var(--primary-accent);
            color: #FFFFFF;
        }

        .sidebar-link i {
            font-size: 1.1rem;
        }

        /* Main Content wrapper */
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar top */
        .top-navbar {
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #EFF6FF;
            color: var(--primary-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        /* General dashboard components */
        .content-container {
            padding: 2rem;
            flex-grow: 1;
        }

        .dashboard-card {
            background-color: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .icon-box-accent {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .icon-box-blue {
            background-color: #EFF6FF;
            color: var(--primary-accent);
        }

        .icon-box-sky {
            background-color: #F0F9FF;
            color: var(--secondary-accent);
        }

        .icon-box-green {
            background-color: #ECFDF5;
            color: #10B981;
        }

        .icon-box-red {
            background-color: #FEF2F2;
            color: #EF4444;
        }

        /* Footer styling */
        .footer {
            background-color: #FFFFFF;
            border-top: 1px solid var(--border-color);
            padding: 1rem 2rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        @media (max-width: 991.98px) {
            .sidebar {
                left: -260px;
            }
            .sidebar.active {
                left: 0;
            }
            .main-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/assets/images/logo.png" alt="SouthMart Logo">
            <div>
                <h5>SouthMart HQ</h5>
                <small class="text-white-50 fs-xs" style="font-size: 0.75rem;">Admin Monitoring</small>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.monitoring') }}" class="sidebar-link {{ Request::routeIs('admin.monitoring') ? 'active' : '' }}">
                    <i class="bi bi-display-fill"></i>
                    <span>Monitoring Cabang</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.national-sales') }}" class="sidebar-link {{ Request::routeIs('admin.national-sales') ? 'active' : '' }}">
                    <i class="bi bi-cart-check-fill"></i>
                    <span>Penjualan Nasional</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.cross-node-query') }}" class="sidebar-link {{ Request::routeIs('admin.cross-node-query') ? 'active' : '' }}">
                    <i class="bi bi-database-fill-gear"></i>
                    <span>Query Lintas Node</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.produk.index') }}" class="sidebar-link {{ Request::routeIs('admin.produk.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>Produk</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.kategori.index') }}" class="sidebar-link {{ Request::routeIs('admin.kategori.*') ? 'active' : '' }}">
                    <i class="bi bi-tags-fill"></i>
                    <span>Kategori</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.inventory') }}" class="sidebar-link {{ Request::routeIs('admin.inventory') ? 'active' : '' }}">
                    <i class="bi bi-archive-fill"></i>
                    <span>Inventaris</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.branches') }}" class="sidebar-link {{ Request::routeIs('admin.branches') ? 'active' : '' }}">
                    <i class="bi bi-shop-window"></i>
                    <span>Cabang</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.replication') }}" class="sidebar-link {{ Request::routeIs('admin.replication') ? 'active' : '' }}">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Replikasi & Konsistensi</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.reports') }}" class="sidebar-link {{ Request::routeIs('admin.reports') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Laporan</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.users') }}" class="sidebar-link {{ Request::routeIs('admin.users') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Pengguna</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.settings') }}" class="sidebar-link {{ Request::routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Pengaturan</span>
                </a>
            </li>
            <li class="sidebar-item border-top border-secondary-subtle my-3 pt-3">
                <a href="{{ route('doc.index') }}" class="sidebar-link" target="_blank">
                    <i class="bi bi-book-half text-warning"></i>
                    <span class="text-warning">Modul Dokumentasi</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        
        <!-- NAVBAR -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm d-lg-none" onclick="document.querySelector('.sidebar').classList.toggle('active')">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h4 class="fw-bold m-0 text-dark">@yield('header_title', 'SouthMart HQ')</h4>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill d-none d-md-inline-flex align-items-center gap-1.5">
                    <span class="indicator online"></span> Server Pusat Online
                </span>
            </div>

            <div class="navbar-user">
                <div class="text-end d-none d-sm-block">
                    <h6 class="fw-semibold m-0 text-dark">{{ Auth::user()->name }}</h6>
                    <small class="text-muted text-capitalize">{{ Auth::user()->role }}</small>
                </div>
                <div class="user-avatar">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm ms-2" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </header>

        <!-- CONTENT -->
        <main class="content-container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="footer">
            <p class="m-0">&copy; {{ date('Y') }} SouthMart Retail Indonesia - Sistem Penjualan dan Monitoring Database Terdistribusi</p>
        </footer>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
