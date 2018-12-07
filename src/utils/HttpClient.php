<?php
/**
 * Created by PhpStorm.
 * User: HZD
 * Date: 2018/1/16
 * Time: 下午8:48
 */

namespace mmgg\utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HttpClient
{

    /**
     * post一个json数据包
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param bool $returnArray
     * @return mixed
     */
    public static function postJson(string $url, array $params, $headers=null, bool $returnArray=true)
    {
        $paramsJson = json_encode($params, JSON_UNESCAPED_UNICODE);
        $body = \GuzzleHttp\Psr7\stream_for($paramsJson);
        $options = [
            'body' => $body,
        ];
        if (!empty($headers)) {
            $options['headers'] = $headers;
        }
        return self::post($url, $options, $returnArray);
    }

    /**
     * post一个form表单
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param bool $returnArray
     * @return mixed
     */
    public static function postForm(string $url, array $params, $headers=null, bool $returnArray=true)
    {
        $options = [
            'form_params' => $params,
        ];
        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        return self::post($url, $options, $returnArray);
    }

    /**
     * post一个multipart
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param bool $returnArray
     * @return mixed
     */
    public static function postMultipart(string $url, array $params, $headers=null, bool $returnArray=true)
    {
        $options = [
            'multipart' => [$params],
        ];
        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        return self::post($url, $options, $returnArray);
    }


    /**
     * post请求
     * @param string $url
     * @param array $options
     * @param bool $returnArray
     * @return mixed
     */
    public static function post(string $url, array $options, bool $returnArray=true)
    {
        try {
            $client = new Client();
            $response = $client->post($url, $options);
            $contents = $response->getBody()->getContents();
            if ($returnArray && !empty($contents)) {
                return self::formatResponse($contents);
            }

            return $contents;
        } catch (RequestException $e) {
            ErrorLog::error($e);

            return null;
        }
    }

    /**
     * 格式化输出数据为关联数组
     * @param $content
     * @return mixed
     */
    private static function formatResponse($content)
    {
        return \GuzzleHttp\json_decode($content, true);
    }

    /**
     * get请求
     * @param string $url
     * @param array $query
     * @param bool $returnArray
     * @return mixed
     */
    public static function get(string $url, array $query=[], bool $returnArray=true)
    {
        try {
            $client = new Client();

            $options = [
                'query' => $query
            ];
            $response = $client->get($url, $options);
            $contents = $response->getBody()->getContents();
            if ($returnArray) {
                return self::formatResponse($contents);
            }

            return $contents;
        } catch (RequestException $e) {
            ErrorLog::error($e);

            return null;
        }
    }

    /**
     * curl请求
     *
     * @param string $url
     * @param string $params
     * @param string $method | POST or GET
     * @param array $header
     * @param integer $returnTransfer
     * @param integer $timeout
     * @return array
     */
    public static function curl(string $url, array $params=[], $header=[], string $method='POST', $returnTransfer=1, $timeout=600)
    {
        $params = json_encode($params);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, $returnTransfer);
        $result = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return [
            'data' => json_decode($result, true),
            'code' => $code
        ];
    }
}
