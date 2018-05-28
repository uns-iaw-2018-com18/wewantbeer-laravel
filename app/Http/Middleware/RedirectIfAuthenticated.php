<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
      //Esto lo comente porque sino me pasaba que redireccionaba si estaba autenticado, pero quiero autenticar cada vez que voy al formulario
      //  if (Auth::guard($guard)->check()) {
        //    return redirect('/crud');
        //}

        return $next($request);
    }
}
