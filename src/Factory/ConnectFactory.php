<?php


namespace BusinessConnectStatus\Factory;


use Interop\Container\ContainerInterface;
use PDO;
use Redis;
use Zend\ServiceManager\Factory\FactoryInterface;

class ConnectFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $mysqlAdapters = $container->get('config')['db']['adapters'];
        $pdoArray = [];
        foreach ($mysqlAdapters as $key => $value) {
            $defaultOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            try {
                $pdo = new PDO(
                    $value['dsn'],
                    $value['username'],
                    $value['password'],
                    array_merge($defaultOptions, $value['options'] ?? [])
                );
            } catch (\PDOException $exception) {
                $pdoArray[$key] = ['mysql' => false];
                continue;
            }

            try {
                $pdo::ATTR_SERVER_INFO;
            } catch (\PDOException $exception) {
                if (strpos($exception->getMessage(), 'MySQL server has gone away') !== false) {
                    $pdoArray[$key] = ['mysql' => false];
                }
            }
            $pdoArray[$key] = ['mysql' => true];
        }

        //检测当前Redis链接状态
        $redisStatus = ['redis' => ['redis' => true]];
        try {
            $redis = new Redis();
            $config = ['host' => '127.0.0.1', 'port' => '6379', 'timeout' => '5'];
            $redis->connect($config['host'], $config['port'], $config['timeout']);
            $redis->ping();
        } catch (\RedisException $redisException) {
            $redisStatus = ['redis' => ['redis' => false]];
        }
        return new $requestedName($pdoArray, $redisStatus);
    }
}