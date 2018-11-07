<?php


namespace BusinessConnectStatus;


use BusinessConnectStatus\Factory\ConnectFactory;
use BusinessConnectStatus\Middleware\AutographMiddleware;
use BusinessConnectStatus\Action\AuthConnect;


class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    AuthConnect::class => ConnectFactory::class,
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
            ]
        ];
    }
}