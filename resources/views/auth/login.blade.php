<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SouthMart Point of Sales</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-accent: #0047AB;
            --secondary-accent: #3B82F6;
            --bg-primary: #FFFFFF;
            --text-dark: #1E293B;
            --text-muted: #64748B;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .split-container {
            min-height: 100vh;
        }

        /* Left Side: Store Showcase & Stats (60%) */
        .showcase-pane {
            position: relative;
            color: #FFFFFF;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .showcase-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .slideshow-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
        }

        .slide.active {
            opacity: 1;
        }

        .showcase-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 71, 169, 0.92) 0%, rgba(59, 130, 246, 0.85) 100%);
            z-index: 1;
        }

        /* Right Side: Login Form (40%) */
        .form-pane {
            background-color: #FFFFFF;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo-img {
            max-height: 75px;
            max-width: 250px;
            object-fit: contain;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 1rem;
            color: #FFFFFF;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.25);
        }

        .node-bubble {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 30px;
            padding: 0.5rem 1.2rem;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .node-bubble:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .indicator.online {
            background-color: #10B981;
            box-shadow: 0 0 8px #10B981;
        }

        .indicator.offline {
            background-color: #EF4444;
            box-shadow: 0 0 8px #EF4444;
        }

        .btn-primary-accent {
            background-color: var(--primary-accent);
            border-color: var(--primary-accent);
            color: #FFFFFF;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary-accent:hover {
            background-color: #003682;
            border-color: #003682;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 71, 171, 0.2);
        }

        .form-control:focus {
            border-color: var(--secondary-accent);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.15);
        }

        /* Supermarket storefront overlay text styles */
        .branding-title {
            font-weight: 800;
            font-size: 2.8rem;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .branding-subtitle {
            font-weight: 400;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        @media (max-width: 991.98px) {
            .showcase-pane {
                display: none;
            }
            .form-pane {
                width: 100%;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0 split-container">
        
        <!-- LEFT SIDE: Showcase Slideshow (60%) -->
        <div class="col-lg-7 showcase-pane">
            <div class="slideshow-container">
                <!-- Slide 1: Supermarket storefront -->
                <div class="slide active" style="background-image: url('https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=1200');"></div>
                <!-- Slide 2: Supermarket aisle / shelves -->
                <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1604719312566-8912e9227c6a?auto=format&fit=crop&q=80&w=1200');"></div>
                <!-- Slide 3: Cashier area / shopping carts -->
                <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&q=80&w=1200');"></div>
            </div>
            <div class="showcase-overlay"></div>

            <div class="showcase-content">
                <!-- Storefront preview top branding -->
                <div>
                    <div class="bg-white px-3 py-2 rounded-3 d-inline-block shadow-sm mb-4">
                        <img src="/assets/images/logo.png" alt="SouthMart Logo" class="logo-img">
                    </div>
                    <h1 class="branding-title mt-2">SouthMart Point of Sales</h1>
                    <p class="branding-subtitle text-light">Sistem Penjualan dan Monitoring Database Terdistribusi</p>
                </div>

                <!-- Supermarket Mock Environment Description / Feature Highlights -->
                <div class="my-auto py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="stat-card d-flex align-items-center gap-3">
                                <div class="fs-2 text-white"><i class="bi bi-shop"></i></div>
                                <div>
                                    <h4 class="fw-bold m-0">3 Cabang</h4>
                                    <small class="text-white-50">Tebet, Kemang, Bogor</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card d-flex align-items-center gap-3">
                                <div class="fs-2 text-white"><i class="bi bi-hdd-network"></i></div>
                                <div>
                                    <h4 class="fw-bold m-0">1 Server Pusat</h4>
                                    <small class="text-white-50">Headquarters Aggregator</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card d-flex align-items-center gap-3">
                                <div class="fs-2 text-white"><i class="bi bi-lightning-charge"></i></div>
                                <div>
                                    <h4 class="fw-bold m-0">1000+ Transaksi</h4>
                                    <small class="text-white-50">Harian dan Real-Time</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card d-flex align-items-center gap-3">
                                <div class="fs-2 text-white"><i class="bi bi-arrow-repeat"></i></div>
                                <div>
                                    <h4 class="fw-bold m-0">99.9% Sinkronisasi</h4>
                                    <small class="text-white-50">Data Aman & Konsisten</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Node Connection Status Indicators -->
                <div>
                    <h5 class="fw-semibold mb-3 text-white-50">Status Koneksi Node Database:</h5>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($nodeStatuses as $node)
                        <div class="node-bubble">
                            <span class="indicator {{ $node->node_status === 'online' ? 'online' : 'offline' }}"></span> {{ $node->name }}
                        </div>
                        @empty
                        <div class="node-bubble">
                            <span class="indicator offline"></span> Database belum terhubung
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE: Login Form (40%) -->
        <div class="col-lg-5 form-pane">
            <div class="w-100 m-auto" style="max-width: 420px;">
                <!-- Logo & Heading -->
                <div class="text-center d-lg-none mb-4">
                    <img src="/assets/images/logo.png" alt="SouthMart Logo" class="logo-img mb-3">
                    <h3 class="fw-bold">SouthMart POS</h3>
                    <p class="text-muted small">Sistem Penjualan & Database Terdistribusi</p>
                </div>
                
                <h3 class="fw-bold mb-1 text-dark d-none d-lg-block">Selamat Datang</h3>
                <p class="text-muted mb-4 d-none d-lg-block">Silakan masuk untuk mengelola transaksi ritel Anda.</p>

                <!-- Feedback Message -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('login.post') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium small">Email / Nama Pengguna</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="nama@southmart.id" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <label for="password" class="form-label fw-medium small mb-0">Password</label>
                            <a href="#" class="text-decoration-none small" style="color: var(--secondary-accent)">Lupa Password?</a>
                        </div>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="mb-4 form-check d-flex justify-content-between align-items-center">
                        <div>
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label class="form-check-label small text-muted" for="remember">Ingat Saya</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-accent w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
                    </button>
                </form>



            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Slideshow functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 5000); // Ganti gambar setiap 5 detik
    });
</script>
</body>
</html>
