<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;
class RoleMiddleware { public function handle(Request $request, Closure $next, ...$roles) { if (! $request->user() || (in_array($request->user()->role,['admin','super_admin'],true) && !$request->user()->is_active) || ! in_array($request->user()->role, $roles, true)) abort(403); return $next($request); } }
