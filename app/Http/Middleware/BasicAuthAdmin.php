<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BasicAuthAdmin
{
    public function handle(Request $request, Closure $next)
{
    $user = $request->getUser();
    $pass = $request->getPassword();

    // fallback
    if (!$user) {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $_SERVER['PHP_AUTH_USER'];
            $pass = $_SERVER['PHP_AUTH_PW'] ?? '';
        }
    }

    //  HARDCODE TEST
    if (
        $user !== 'admin' ||
        $pass !== '123456aA@'
    ) {
        return response('Unauthorized', 401, [
            'WWW-Authenticate' => 'Basic realm="Admin Panel"',
        ]);
    }

    return $next($request);
}
}