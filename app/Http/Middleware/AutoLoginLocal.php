<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class AutoLoginLocal
{
    /**
     * In local development, automatically log in as the first available user
     * so the application can be accessed without entering credentials.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (App::isLocal() && ! Auth::check()) {
            $user = User::query()->first();

            if ($user !== null) {
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
