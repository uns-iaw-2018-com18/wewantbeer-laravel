<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/index',
        '/login',
        '/crud',
        '/logout',
        '/admin',
        '/admin/add',
        '/admin/edit',
        '/admin/edit/*',
        '/admin/delete',
        '/admin/delete/*',
        '/checkid',
    ];
}
