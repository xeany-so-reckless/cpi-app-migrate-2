<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TALLY PRO 2026 - @yield('title', 'Dashboard')</title>
    <style>
        :root {
            --sidebar-color: #1e293b;
            --active-color: #27ae60;
            --hover-color: #334155;
            --text-muted: #94a3b8;
        }
 
        html, body {
            margin: 0; padding: 0; height: 100%; width: 100%;
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            overflow: hidden;
        }
 
        .dashboard-container { display: flex; width: 100%; height: 100vh; }
 
        .sidebar { width: 260px; background: var(--sidebar-color); color: white; display: flex; flex-direction: column; flex-shrink: 0; z-index: 10; }
        .sidebar-header { padding: 25px 20px; text-align: center; background: #0f172a; border-bottom: 1px solid #1e293b; }
        .nav-menu { flex-grow: 1; padding: 15px 0; }
        .nav-item { padding: 15px 20px; cursor: pointer; transition: all 0.2s; border-left: 4px solid transparent; font-size: 14px; color: var(--text-muted); display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .nav-item:hover { background: var(--hover-color); color: white; }
        .nav-item.active { background: var(--hover-color); border-left-color: var(--active-color); color: var(--active-color); font-weight: bold; }
 
        .sidebar-footer { padding: 20px; font-size: 10px; color: #475569; text-align: center; border-top: 1px solid #1e293b; }
 
        .content-area { flex-grow: 1; height: 100vh; background-color: white; position: relative; overflow-y: auto; box-sizing: border-box; }
        .top-bar { padding: 20px 20px 0 20px; display: flex; justify-content: space-between; align-items: center; gap: 15px; }
        .welcome-banner { background-color: #27ae60; color: white; padding: 15px 20px; border-radius: 6px; font-size: 16px; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.05); flex-grow: 1; }
 
        .btn-logout {
            background: #e11d48; color: white; border: none; padding: 12px 18px;
            border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 13px; white-space: nowrap;
        }
        .btn-logout:hover { background: #be123c; }
 
        .page-body { padding: 20px; box-sizing: border-box; }
 
        /* SweetAlert Modern */
 
        .modern-popup{
            box-shadow:0 25px 60px rgba(0,0,0,.18)!important;
        }
 
        .modern-confirm-btn{
            border-radius:12px!important;
            padding:12px 24px!important;
            font-size:15px!important;
            font-weight:600!important;
            transition:.25s;
        }
 
        .modern-confirm-btn:hover{
            transform:translateY(-2px);
            box-shadow:0 12px 30px rgba(39,174,96,.35);
        }
 
        .modern-cancel-btn{
            border-radius:12px!important;
            padding:12px 24px!important;
            font-size:15px!important;
            font-weight:600!important;
            transition:.25s;
        }
 
        .modern-cancel-btn:hover{
            transform:translateY(-2px);
            box-shadow:0 12px 30px rgba(100,116,139,.25);
        }
    </style>
    @stack('styles')
</head>
<body>
 
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2 style="margin:0; font-size: 18px; color: white;">TALLY PRO 2026</h2>
                <small style="color: var(--text-muted);">Integrated Dashboard V.2</small>
            </div>
            <div class="nav-menu">
                <a href="{{ route('tally.input') }}" class="nav-item {{ request()->routeIs('tally.input') ? 'active' : '' }}">
                    <span>Tally Produksi Bersih</span>
                </a>
                <a href="{{ route('tally.rekap') }}" class="nav-item {{ request()->routeIs('tally.rekap') ? 'active' : '' }}">
                    <span>Hasil Produksi</span>
                </a>
            </div>
            <div class="sidebar-footer">
                PT. Charoen Pokphand Indonesia - Plant Jombang
            </div>
        </div>
 
        <div class="content-area">
            <div class="top-bar">
                <div class="welcome-banner">Selamat bekerja, {{ auth()->guard('tally')->user()->name }}</div>
                <form id="logoutForm" method="POST" action="{{ route('tally.logout') }}">
                @csrf
                <button type="button" class="btn-logout" onclick="confirmLogout()">
                    Keluar / Logout
                </button>
            </form>
            </div>
 
            <div class="page-body">
                @yield('content')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
function confirmLogout() {
    Swal.fire({
        title: '<span style="font-size:26px;font-weight:700;">Keluar dari Sistem?</span>',
        html: `
            <div style="font-size:15px;color:#6b7280;margin-top:8px;">
                Anda akan keluar dari
                <b style="color:#27ae60;">TALLY PRO 2026</b>.
            </div>
        `,
        icon: 'question',
        width: 430,
        background: '#fff',
        color: '#1f2937',
        borderRadius: '18px',
        padding: '2em',
 
        showCancelButton: true,
        reverseButtons: true,
 
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
 
        confirmButtonColor: '#FF0000',
        cancelButtonColor: '#64748b',
 
        customClass: {
            popup: 'modern-popup',
            confirmButton: 'modern-confirm-btn',
            cancelButton: 'modern-cancel-btn'
        }
 
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('logoutForm').submit();
        }
    });
}
</script>
    @stack('scripts')
</body>
</html>