<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to appropriate dashboard
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        // Check if accessing from Central Domain (No Tenant)
        // Adjust check based on your tenancy setup, but usually !app('CurrentTenant') works
        // or check if request host is in config('tenancy.central_domains')
        
        $isTenant = app()->has('CurrentTenant');

        if (!$isTenant) {
            return view('auth.login-central');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // 1. Rate Limiting (Prevent Brute Force)
        // Key: ip + email to prevent single-account attack from multiple IPs? 
        // Or simply throttle by IP for DDoS prevention, and throttle by email for account lock.
        // Let's us throttleKey based on email + IP.
        
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $seconds . ' detik.',
            ])->onlyInput('email');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (app()->has('CurrentTenant')) {
            $credentials['pesantren_id'] = app('CurrentTenant')->id;
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            RateLimiter::clear($throttleKey); // Clear hits on success

            $user = Auth::user();

            // Tenant Safety Check
            if (app()->has('CurrentTenant') && $user->pesantren_id !== app('CurrentTenant')->id) {
                Auth::logout();
                return back()->withErrors(['email' => 'User tidak terdaftar di pesantren ini.']);
            }

            // Block Owner from logging in via Tenant Subdomain
            if (app()->has('CurrentTenant') && $user->role === 'owner') {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Owner harus login melalui portal Owner.']);
            }

            // OTP verification disabled for simplicity - direct login
            return $this->redirectToDashboard();
        }

        // Increment failed attempts
        RateLimiter::hit($throttleKey, 60); // 1 minute decay

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Redirect to dashboard based on user role
     */
    protected function redirectToDashboard()
    {
        $user = Auth::user();
        $mainDomain = config('tenancy.central_domains')[0] ?? 'santrix.my.id';
        $currentHost = request()->getHost();
        $isLocalhost = in_array($currentHost, ['localhost', '127.0.0.1']);

        // 1. Owner -> Always Central Owner Dashboard
        if ($user->role === 'owner') {
            // Localhost: redirect to path-based /owner
            if ($isLocalhost) {
                return redirect('/owner');
            }
            return redirect()->to('https://owner.' . $mainDomain . '/owner');
        }

        // 2. Tenant User Redirect Logic
        if ($user->pesantren_id) {
            $pesantren = $user->pesantren;
            
            if (!$pesantren) {
                Auth::logout();
                return redirect('/login')->withErrors(['email' => 'Data pesantren tidak ditemukan.']);
            }

            $path = match($user->role) {
                'admin'       => '/admin',
                'pendidikan'  => '/pendidikan',
                'sekretaris'  => '/sekretaris',
                'bendahara'   => '/bendahara',
                default       => '/',
            };

            // Localhost: redirect to /tenant/<path>
            if ($isLocalhost) {
                return redirect('/tenant' . $path);
            }

            // Production: redirect to subdomain
            $tenantUrl = 'https://' . $pesantren->subdomain . '.' . $mainDomain;
            $expectedHostStart = $pesantren->subdomain . '.';

            if (!Str::startsWith($currentHost, $expectedHostStart)) {
                return redirect()->to($tenantUrl . $path);
            }

            // Already on correct tenant domain
            return match($user->role) {
                'admin'      => redirect()->route('admin.dashboard'),
                'pendidikan' => redirect()->route('pendidikan.dashboard'),
                'sekretaris' => redirect()->route('sekretaris.dashboard'),
                'bendahara'  => redirect()->route('bendahara.dashboard'),
                default      => redirect('/'),
            };
        }

        Auth::logout();
        return redirect('/login')->withErrors(['email' => 'Role user tidak valid.']);
    }

    /**
     * Handle Demo Auto-Login via Token
     */
    public function demoLogin(Request $request)
    {
        $token = $request->query('token');
        $type = $request->query('type', 'sekretaris');

        if (!$token) {
            return redirect()->route('tenant.login')->with('error', 'Token demo tidak valid.');
        }

        // Verify Token
        $verification = \App\Models\LoginVerification::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return redirect()->route('tenant.login')->with('error', 'Sesi demo kadaluarsa. Silakan mulai ulang dari halam utama.');
        }

        // Login User
        $user = User::find($verification->user_id);
        
        if (!$user) {
            return redirect()->route('tenant.login');
        }

        // SECURITY: Ensure the token user belongs to the current domain's tenant
        if (app()->has('CurrentTenant') && $user->pesantren_id !== app('CurrentTenant')->id) {
            return redirect()->route('tenant.login')->with('error', 'Token demo tidak valid untuk domain ini.');
        }

        Auth::login($user);

        // Delete used token
        $verification->delete();

        // Redirect to specific dashboard
        $path = match ($type) {
            'bendahara' => '/bendahara', 
            'pendidikan' => '/pendidikan', 
            'admin' => '/admin',
            'sekretaris' => '/sekretaris',
            default => '/sekretaris',
        };

        return redirect($path)->with('success', 'Berhasil masuk ke Mode Demo!');
    }
}
