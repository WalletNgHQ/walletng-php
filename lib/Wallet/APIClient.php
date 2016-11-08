<?php
/**
 * Created by PhpStorm.
 * User: Peter.Makafan
 * Date: 11/6/2016
 * Time: 9:50 PM
 */

namespace Wallet;


class APIClient
{

    public $apiKey;

    public function __construct($apiKey = null)
    {
        $this->_apiKey = $apiKey;
    }

    public static function apiUrl($url = '')
    {
        $apiBase = Wallet::$apiBase;

        return "{$apiBase}{$url}";
    }

    public static function utf8($value)
    {
        if (is_string($value) && function_exists('mb_detect_encoding') && mb_detect_encoding($value, "UTF-8", TRUE) != "UTF-8") {
            return utf8_encode($value);
        }

        return $value;
    }

    private static function _encodeObjects($d)
    {
        if ($d instanceof Resource) {

            return array("id" => self::utf8($d->id));
        } else if ($d === true) {

            return 'true';
        } else if ($d === false) {

            return 'false';
        } else if (is_array($d)) {
            $res = array();
            foreach ($d as $k => $v) {
                $res[$k] = self::_encodeObjects($v);
            }

            return $res;
        } else {

            return self::utf8($d);
        }
    }

    public static function encode($arr, $prefix = null)
    {
        if (!is_array($arr)) {

            return $arr;
        }

        $r = array();
        foreach ($arr as $k => $v) {
            if (is_null($v)) {
                continue;
            }

            if ($prefix && isset($k)) {
                $k = $prefix . "[" . $k . "]";
            } else if ($prefix) {
                $k = $prefix . "[]";
            }

            if (is_array($v)) {
                $r[] = self::encode($v, $k, true);
            } else {
                $r[] = urlencode($k) . "=" . urlencode($v);
            }
        }

        return implode("&", $r);
    }

    public function request($method, $url, $params = null)
    {
        if (!$params) {
            $params = array();
        }
        list($httpBody, $httpStatus, $myApiKey) = $this->_requestRaw($method, $url, $params);

        // decode the json response into an array
        $response = $this->_interpretResponse($httpBody, $httpStatus);

        return array($response, $myApiKey);
    }

    /**
     * @param $method
     * @param $url
     * @param $params
     * @return array
     * @throws Error
     */
    private function _requestRaw($method, $url, $params)
    {
        // check auth
        $myApiKey = $this->_apiKey;
        /** @var TYPE_NAME $myApiKey */
        if (!$myApiKey) {
            if (!$myApiKey = Wallet::$apiKey) {
                throw new Error('No API key provided. Set your API key using "Wallet::setApiKey(<API-KEY>)".');
            }
        }

        // prepare url, params, header for cURL
        $absUrl = $this->apiUrl($url);
        $params = self::_encodeObjects($params);

        $langVersion = phpversion();
        $uname = php_uname();
        $ua = array(
            'bindings_version' => Wallet::VERSION,
            'lang'             => 'php',
            'lang_version'     => $langVersion,
            'publisher'        => 'wallet',
            'uname'            => $uname
        );
        $headers = array('X-Wallet-Client-User-Agent: ' . json_encode($ua),
            'User-Agent: Wallet/v1 PhpClient/' . Wallet::VERSION,
            "Authorization: Bearer {$myApiKey}",
            "Accept: application/json"       );
        if (Wallet::$apiVersion) {
            $headers[] = 'Wallet-Version: ' . Wallet::$apiVersion;
        }
        list($httpBody, $httpStatus) = $this->_curlRequest($method, $absUrl, $headers, $params, $myApiKey);

        return array($httpBody, $httpStatus, $myApiKey);
    }

    private function _curlRequest($method, $absUrl, $headers, $params, $myApiKey)
    {
        $curl = curl_init();
        $method = strtolower($method);
        $curlOptions = array();

        // method
        if ($method == 'get') {
            $curlOptions[CURLOPT_HTTPGET] = 1;
            if (count($params) > 0) {
                $encoded = self::encode($params);
                $absUrl = "$absUrl?$encoded";
            }
        } else if ($method == 'post') {
            $curlOptions[CURLOPT_POST] = 1;
            $curlOptions[CURLOPT_POSTFIELDS] = self::encode($params);
        } else if ($method == 'delete') {
            $curlOptions[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            if (count($params) > 0) {
                $encoded = self::encode($params);
                $absUrl = "{$absUrl}?{$encoded}";
            }
        } else {
            throw new Error("Unrecognized method {$method}");
        }

        // url
        $absUrl = self::utf8($absUrl);
        $curlOptions[CURLOPT_URL] = $absUrl;

        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[CURLOPT_CONNECTTIMEOUT] = 30;
        $curlOptions[CURLOPT_TIMEOUT] = 80;
        $curlOptions[CURLOPT_HTTPHEADER] = $headers;

        curl_setopt_array($curl, $curlOptions);
        $httpBody = curl_exec($curl);

        $errorNum = curl_errno($curl);
        if ($errorNum == CURLE_SSL_CACERT || $errorNum == CURLE_SSL_PEER_CERTIFICATE || $errorNum == 77) {
            //TODO: FIX
            curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . '/../cacert.pem');
            $httpBody = curl_exec($curl);
        }

        if ($httpBody === false) {
            $errorNum = curl_errno($curl);
            $message = curl_error($curl);
            curl_close($curl);
            $this->handleCurlError($errorNum, $message);
        }

        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return array($httpBody, $httpStatus);
    }

    private function _interpretResponse($httpBody, $httpStatus)
    {
        try {
            $response = json_decode($httpBody, true);
        } catch (\Exception $e) {
            throw new Error("Invalid response body from API: HTTP Status: ({$httpStatus}) {$httpBody}", $httpStatus, $httpBody);
        }

        if ($httpStatus < 200 || $httpStatus >= 300) {
            $this->handleApiError($httpBody, $httpStatus, $response);
        }
        return $response;
    }

    public function handleApiError($httpBody, $httpStatus, $response)
    {
        if (!is_array($response) || !isset($response['error'])) {
            throw new Error("Invalid response object from API: HTTP Status: ({$httpStatus}) {$httpBody})", $httpStatus, $httpBody);
        }
        throw new Error(is_array($response['error']) ? $response['error']['message'] : (!empty($response['error']) ? $response['error'] : ""), $httpStatus, $httpBody);
    }

    /**
     * @param $errorNum
     * @param $message
     * @throws Error
     */
    public function handleCurlError($errorNum, $message)
    {
        $apiBase = Wallet::$apiBase;
        switch ($errorNum) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $msg = "Could not connect to Wallet ({$apiBase}).  Please check your internet connection and try again.  If this problem persists please let us know at contact@easypost.com.";
                break;
            case CURLE_SSL_CACERT:
            case CURLE_SSL_PEER_CERTIFICATE:
                $msg = "Could not verify Wallet's SSL certificate.  Please make sure that your network is not intercepting certificates.  (Try going to {$apiBase} in your browser.)  If this problem persists, let us know at contact@easypost.com.";
                break;
            default:
                $msg = "Unexpected error communicating with Wallet.  If this problem persists please let us know at john@wallet.ng.";
        }

        $msg .= "\nNetwork error [errno {$errorNum}]: {$message})";
        throw new Error($msg);
    }

}