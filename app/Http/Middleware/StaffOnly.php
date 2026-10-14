<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Réserve une route à l'équipe (les comptes clients n'y ont pas accès). */
class StaffOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->isStaff(), 403);
        return $next($request);
    }
}
