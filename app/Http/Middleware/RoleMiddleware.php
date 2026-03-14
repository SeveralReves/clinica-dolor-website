<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return abort(401);
        }

        // BYPASS: Si es un super rol (definido en tu config/acl.php), pasa sin preguntar
        if (\App\Support\Acl::isSuper($user)) {
            return $next($request);
        }

        // Si no es super, entonces verificamos si su rol está en la lista permitida
        if (!$user->hasRole($roles)) {
            abort(403, 'No tienes el rol necesario.');
        }

        return $next($request);
    }
}
