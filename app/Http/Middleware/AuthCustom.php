<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;

class AuthCustom
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $authService = new AuthService();
        $token = explode(' ', $request->header('Authorization') ?? '');
        try {
            if (empty($token[1]) || !$authService->verify($token[1])) {
                return response()->json([
                    'data' => 'Unauthorized',
                    'success' => false,
                ], 401);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'data' => 'Unauthorized',
                'success' => false,
            ], 401);
        }

        app()->make('token')->setToken($token[1]);
        return $next($request);
    }
}
