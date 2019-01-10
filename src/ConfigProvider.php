<?php


namespace BusinessTestConnectStatus;


use BusinessTestConnectStatus\Factory\AutographMiddlewareFactory;
use BusinessTestConnectStatus\Factory\ConnectFactory;
use BusinessTestConnectStatus\Middleware\AutographMiddleware;
use BusinessTestConnectStatus\Action\AuthConnect;


class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    AuthConnect::class => ConnectFactory::class,
                    AutographMiddleware::class => AutographMiddlewareFactory::class
                ]
            ],
            'routes' => [
                /**
                 * 主动业务巡检验证签名
                 */
                [
                    'name' => 'business-auth',
                    'path' => '/business/auth',
                    'middleware' => [
                        AutographMiddleware::class,
                        AuthConnect::class,
                    ],
                    'allowed_methods' => ['GET'],
                ],
            ],
        ];
    }
}