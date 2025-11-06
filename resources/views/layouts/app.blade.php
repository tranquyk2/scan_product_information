<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'InfoModel - Quản lý sản phẩm')</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('image/sungho-electronics--600.ico') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 border-bottom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('scanner.index') }}">
                <img src="{{ asset('logo.png') }}" alt="InfoModel Logo" style="height: 40px;">
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('scanner.index') }}">🔍 Quét mã</a>
                <a class="nav-link" href="{{ route('history.index') }}">🕑 Lịch sử thay thế</a>
                @auth
                    @if(auth()->user()->is_admin)
                        <a class="nav-link" href="{{ route('admin.upload') }}">📤 Upload Excel</a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">Đăng xuất</button>
                        </form>
                    @endif
                @else
                    <a class="nav-link" href="{{ route('login') }}">🔑 Đăng nhập</a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✓ {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ✗ {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>
