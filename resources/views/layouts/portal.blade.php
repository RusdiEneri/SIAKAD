<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Portal Akademik Mahasiswa & Dosen - SIAKAD' }}</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Livewire Styles -->
    @livewireStyles

    <style>
        :root {
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.3);
            --secondary: #38bdf8;
            --bg-dark: #090d16;
            --bg-dark-accent: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.2);
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #0ea5e9;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Navbar Styling */
        .navbar {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--card-border);
            padding: 16px 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            font-size: 20px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo-box {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 0 16px var(--primary-glow);
        }

        .brand-text {
            background: linear-gradient(135deg, #ffffff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .user-nav { display: flex; align-items: center; gap: 20px; }
        .user-info { text-align: right; }
        .user-name { font-size: 14px; font-weight: 700; color: var(--text-light); }
        .user-role { font-size: 11px; font-weight: 600; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px; }

        .btn-admin {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: var(--text-light);
            padding: 9px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(8px);
        }

        .btn-admin:hover {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 20px var(--primary-glow);
            transform: translateY(-2px);
        }

        /* Container Layout */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 36px 20px;
            flex: 1;
            width: 100%;
        }

        /* Cards & Grid System */
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 26px;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        }

        .card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 30px 50px -20px rgba(99, 102, 241, 0.25);
        }

        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .card-title { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .card-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .card-value { font-size: 34px; font-weight: 800; color: var(--text-light); letter-spacing: -1px; }

        /* Buttons & Actions */
        .btn {
            padding: 11px 22px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            box-shadow: 0 8px 20px -6px var(--primary-glow);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -4px rgba(99, 102, 241, 0.5);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 8px 20px -6px var(--success-glow);
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -4px rgba(16, 185, 129, 0.5);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }
        .btn-danger:hover { transform: translateY(-2px); opacity: 0.9; }

        /* Tabs Styling */
        .tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 28px;
            background: rgba(15, 23, 42, 0.5);
            padding: 6px;
            border-radius: 16px;
            border: 1px solid var(--card-border);
            width: fit-content;
        }

        .tab-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            padding: 10px 22px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 12px;
            transition: all 0.25s ease;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, var(--primary), #4338ca);
            color: #fff;
            box-shadow: 0 4px 15px var(--primary-glow);
        }
        .tab-btn:hover:not(.active) { color: var(--text-light); background: rgba(255, 255, 255, 0.05); }

        /* Table Styling */
        .table-responsive {
            overflow-x: auto;
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid var(--card-border);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
        }

        table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; }
        
        th {
            background: rgba(15, 23, 42, 0.8);
            padding: 18px 24px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--card-border);
        }

        td {
            padding: 18px 24px;
            border-bottom: 1px solid var(--card-border);
            font-size: 14px;
            color: var(--text-light);
            transition: background 0.2s;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255, 255, 255, 0.03); }

        /* Pill Badges */
        .badge {
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            letter-spacing: 0.3px;
        }

        .badge-success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        .badge-info { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); }

        /* Alerts */
        .alert {
            padding: 18px 24px;
            border-radius: 16px;
            margin-bottom: 28px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            backdrop-filter: blur(12px);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.2);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #6ee7b7;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.2);
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--card-border);
            padding: 28px;
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            margin-top: auto;
            background: rgba(15, 23, 42, 0.4);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="/portal" class="navbar-brand">
            <div class="brand-logo-box">🎓</div>
            <div class="brand-text">SIAKAD PORTAL</div>
        </a>
        <div class="user-nav">
            @auth
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">
                        {{ auth()->user()->roles->pluck('name')->map(fn($n) => ucfirst($n instanceof \BackedEnum ? $n->value : (string)$n))->implode(', ') }}
                    </div>
                </div>
                <a href="/admin" class="btn-admin">⚙️ Panel Admin Filament</a>
            @else
                <a href="/admin/login" class="btn btn-primary">Login SIAKAD</a>
            @endauth
        </div>
    </nav>

    <main class="container">
        {{ $slot }}
    </main>

    <footer>
        &copy; {{ date('Y') }} <strong>SIAKAD ACADEMIC SYSTEM</strong> &bull; Perguruan Tinggi Terakreditasi Utama. Powered by Laravel 12 & Filament 3.
    </footer>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
