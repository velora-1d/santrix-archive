<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Login - SANTRIX</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: linear-gradient(145deg, #1e293b, #0f172a);
            padding: 2rem;
            border-radius: 1rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        .icon {
            width: 64px;
            height: 64px;
            background: rgba(99, 102, 241, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #818cf8;
        }
        h1 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        p { color: #94a3b8; font-size: 0.875rem; margin-bottom: 2rem; }
        .input-group { margin-bottom: 1.5rem; }
        input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            color: #fff;
            font-size: 1.25rem;
            text-align: center;
            letter-spacing: 0.5em;
            outline: none;
            transition: all 0.3s;
        }
        input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        button:hover { background: #4f46e5; }
        .resend {
            display: block;
            margin-top: 1.5rem;
            color: #94a3b8;
            font-size: 0.875rem;
            text-decoration: none;
        }
        .resend:hover { color: #fff; }
        .alert {
            padding: 0.75rem;
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <i data-feather="shield"></i>
        </div>
        <h1>Verifikasi Keamanan</h1>
        <p>Kami mendeteksi login dari perangkat baru.<br>Masukkan kode 6 digit yang dikirim ke email Anda.</p>

        @if ($errors->any())
            <div class="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ app()->has('CurrentTenant') ? route('tenant.verification.verify') : route('login.verify.check') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" name="token" maxlength="6" placeholder="000000" autofocus required autocomplete="off">
            </div>
            <button type="submit">Verifikasi & Masuk</button>
        </form>

        @php
            $logoutUrl = app()->has('CurrentTenant')
                ? route('tenant.logout')
                : (Route::has('owner.logout') ? route('owner.logout') : url('/logout'));
        @endphp
        <form action="{{ $logoutUrl }}" method="POST" style="margin-top: 1rem;">
            @csrf
            <button type="submit" style="background: transparent; color: #94a3b8; font-weight: normal; font-size: 0.875rem;">Login akun lain</button>
        </form>
    </div>
    <script>feather.replace()</script>
</body>
</html>
