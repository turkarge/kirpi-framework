<?php

declare(strict_types=1);

/** @var \Core\Routing\Router $router */

$router->group(['prefix' => '/api/v1'], function (\Core\Routing\Router $router) {

    $router->get('/ping', function (\Core\Http\Request $request) {
        return \Core\Http\Response::json([
            'message' => 'pong',
            'time'    => date('Y-m-d H:i:s'),
        ]);
    });

});