<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $normalizedHost = str_replace(['http://', 'https://'], '', $host);
        
        $centralDomains = config('tenancy.central_domains', ['santrix.my.id', 'santrix.test', 'localhost', '127.0.0.1']);
        // Also skip owner subdomain
        $isOwnerSubdomain = str_starts_with($normalizedHost, 'owner.');
        
        // Skip resolution if request is for central domain or owner subdomain
        if (in_array($normalizedHost, $centralDomains) || $isOwnerSubdomain) {
            \Illuminate\Support\Facades\URL::defaults(['central_domain' => $normalizedHost]);
            return $next($request);
        }

        // Extract subdomain
        $parts = explode('.', $normalizedHost);
        $subdomain = $parts[0];

        // Find Tenant
        // Optimization: Use cache in production
        $tenant = \App\Models\Pesantren::where('subdomain', $subdomain)->first();

        if (!$tenant || !in_array($tenant->status, ['active', 'trial'])) {
             // If tenant not found or not active/trial, abort or redirect. 
             // Ideally customize the 404 page for better UX.
             abort(404, 'Pesantren not found or inactive.');
        }

        // Bind Current Tenant to the Container (for helper functions)
        app()->instance('tenant', $tenant);
        app()->instance('CurrentTenant', $tenant); // Keep for backward compatibility
        
        // Store in session for easy access
        session([
            'pesantren_id' => $tenant->id,
            'pesantren_name' => $tenant->nama,
            'pesantren_logo' => $tenant->logo_url,
        ]);
        
        // Share with all views
        view()->share('currentTenant', $tenant);

        // Set URL default for subdomain parameter (required for domain-scoped routes)
        \Illuminate\Support\Facades\URL::defaults(['subdomain' => $subdomain]);

        // Set URL default for central_domain parameter used in some routes
        $centralDomain = implode('.', array_slice($parts, 1));
        if ($centralDomain) {
            \Illuminate\Support\Facades\URL::defaults(['central_domain' => $centralDomain]);
        }


        return $next($request);
    }
}
