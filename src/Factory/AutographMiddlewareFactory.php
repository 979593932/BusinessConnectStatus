<?php


namespace BusinessConnectStatus\Factory;


use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class AutographMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        try {
            $key = array_values($container->get('config')['business_key'])[0];
        } catch (\Exception $exception) {
            return new $requestedName('');
        }
        return new $requestedName($key);
    }
}