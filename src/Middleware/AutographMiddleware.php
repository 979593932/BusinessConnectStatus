<?php


namespace BusinessConnectStatus\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class AutographMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ret = $this->getStatus('http://admincenterlocal.cloudtoad.com/status', '---AppKey---');
        if ($ret['code'] !== 0) {
            return new JsonResponse(['detail' => '签名验证失败！', 'code' => $ret], 403);
        }
        return $handler->handle($request);
    }

    protected function MakeQuerySign($query, $signKey)
    {
        if (is_string($query)) {
            $url = parse_url($query);
            $query = [];
            if (! empty($url['query'])) {
                parse_str($url['query'], $query);
            }
        }

// 获取参与签名的key
        $keys = [];
        foreach ($query as $k => $v) {
            if (empty($v)) {
                continue;
            }
            array_push($keys, $k);
        }
        sort($keys); // 排序

// 获取参与签名的字符串
        $str = '';
        foreach ($keys as $k => $v) {
            $str .= '&' . $k . '=' . $v;
        }
        $str = substr($str, 1);
        $md5 = strtolower(md5($str . $signKey));
        return $md5;
    }

    protected function getStatus($statusURL, $appKey)
    {
        $tm = time();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $statusURL);//设置抓取的url
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置获取的信息以文件流的形式保存到字符串返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_HEADER, 0);//设定是否输出页面内容
        curl_setopt($ch, CURLOPT_HTTPHEADER, [//设置一个header中传输内容的数组
            'Timestamp: ' . $tm,
            'Authorization: Sign ' . $this->MakeQuerySign($statusURL, $tm . $appKey)
        ]);
        $respData = curl_exec($ch);
        $respCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (is_string($respData)) {
            $json = json_decode($respData, true);
            if ($json) {
                $respData = $json;
            }
        }
        if ($respCode != 200) {
            return ['code' => 127, 'httpCode' => $respCode, 'resp' => $respData];
        }
        if (! (isset($respData['code']) && $respData['code'] == 0)) {
            return ['code' => 127, 'httpCode' => $respCode, 'resp' => $respData];
        }
        return ['code' => 0, 'httpCode' => $respCode, 'resp' => $respData];
    }
}