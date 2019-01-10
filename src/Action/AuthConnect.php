<?php


namespace BusinessConnectStatus\Action;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class AuthConnect implements RequestHandlerInterface
{
    private $pdoArray;
    private $redisStatus;

    public function __construct($pdoArray, $redisStatus)
    {
        $this->pdoArray = $pdoArray;
        $this->redisStatus = $redisStatus;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $mysqlInfomation = $this->message($this->pdoArray);
        $redisInfomation = $this->message($this->redisStatus);
        return new JsonResponse(['info' => $mysqlInfomation, 'redisStatus' => $redisInfomation]);
    }

    protected function message($booleArray)
    {
        $info = [];
        foreach ($booleArray as $key => $value) {
            $code = 0;
            $message = '';
            $dependent = $key;
            $dependentDsc = array_keys($value)[0];
            $now = date('Y-m-d H:i:s');
            if (! array_values($value)[0]) {
                $code = 500;
                $message = $dependentDsc . '连接失败！';
            }
            $info[] = [
                'code' => $code, 'message' => $message,
                'dependents' => ["$dependentDsc" => $dependent], 'env' => ['now' => $now]
            ];
        }
        return $info;
    }
}