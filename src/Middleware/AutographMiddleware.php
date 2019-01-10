<?php


namespace BusinessTestConnectStatus\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class AutographMiddleware implements MiddlewareInterface
{
    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $this->key) {
            return new JsonResponse(['detail' => '请配置密钥!'], 403);
        }
        $params = $request->getQueryParams();
        $timestamp = $request->getHeader('Timestamp')[0] ?? '';
        $authorization = $request->getHeader('Authorization')[0] ?? '';
        $signKey = $timestamp . $this->key;
        if ($authorization != $this->MakeQuerySign($params, $signKey)) {
            return new JsonResponse(['detail' => '签名验证失败！'], 403);
        }
        return $handler->handle($request);
    }

    /**
     * 生成签名
     * @param $params
     * @param $signKey
     * @return string
     */
    protected function MakeQuerySign($params, $signKey)
    {
// 获取参与签名的key
        $keys = [];
        if (is_array($params)) {
            foreach ($params as $k => $v) {
                if (empty($v)) {
                    continue;
                }
                array_push($keys, $k);
            }
            sort($keys); // 排序
        }

// 获取参与签名的字符串
        $str = '';
        foreach ($keys as $k => $v) {
            $str .= '&' . $k . '=' . $v;
        }
        $str = substr($str, 1);
        $md5 = strtolower(md5($str . $signKey));
        return 'Sign ' . $md5;
    }
}