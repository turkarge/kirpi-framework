<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->group(['prefix' => '/api/v1'], function (\Core\Routing\Router $router) {

    // Auth
    $router->post('/auth/login', function (\Core\Http\Request $request) {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $guard = \Core\Auth\Facades\Auth::guard('api');

        if (!$guard->attempt($data)) {
            return \Core\Http\Response::json(['error' => 'Invalid credentials.'], 401);
        }

        $user   = $guard->user();
        $tokens = $guard->issueTokens($user);

        return \Core\Http\Response::json($tokens);
    });

    $router->post('/auth/register', function (\Core\Http\Request $request) {
        $data = $request->validate([
            'name'                  => 'required|string|min:2',
            'email'                 => 'required|email',
            'password'              => 'required|min:8|confirmed',
        ]);

        $user = \Modules\Users\Models\User::create($data);

        return \Core\Http\Response::json($user->toArray(), 201);
    });

    $router->post('/auth/refresh', function (\Core\Http\Request $request) {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return \Core\Http\Response::json(['error' => 'Refresh token required.'], 422);
        }

        try {
            $tokens = \Core\Auth\Facades\Auth::guard('api')->refresh($refreshToken);
            return \Core\Http\Response::json($tokens);
        } catch (\Exception $e) {
            return \Core\Http\Response::json(['error' => $e->getMessage()], 401);
        }
    });

    // Protected routes
    $router->group(['middleware' => 'auth:api'], function (\Core\Routing\Router $router) {

        $router->get('/me', function (\Core\Http\Request $request) {
            return \Core\Http\Response::json(
                \Core\Auth\Facades\Auth::user()->toArray()
            );
        });

        $router->put('/me', function (\Core\Http\Request $request) {
            $data = $request->validate([
                'name'   => 'string|min:2|max:100',
                'locale' => 'string|max:10',
            ]);

            $user = \Core\Auth\Facades\Auth::user();
            $user->update($data);

            return \Core\Http\Response::json($user->toArray());
        });

    });

    // Ping
    $router->get('/ping', function (\Core\Http\Request $request) {
        return \Core\Http\Response::json([
            'message' => 'pong',
            'time'    => date('Y-m-d H:i:s'),
        ]);
    });

});