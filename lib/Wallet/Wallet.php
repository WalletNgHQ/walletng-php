<?php
/**
 * Created by PhpStorm.
 * User: Peter.Makafan
 * Date: 11/6/2016
 * Time: 9:48 PM
 */

namespace Wallet;


abstract class Wallet
{

    public static $apiKey;
    public static $apiBase = 'https://bed.wallet.ng/merchant';
    public static $apiVersion = "1";
    const VERSION = '1.0.0';

    public static function getApiKey()
    {
        return self::$apiKey;
    }
    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    public static function getApiBase()
    {
        return self::$apiBase;
    }
    public static function setApiBase($apiBase)
    {
        self::$apiBase = $apiBase;
    }

    public static function getApiVersion()
    {
        return self::$apiVersion;
    }
    public static function setApiVersion($apiVersion)
    {
        self::$apiVersion = $apiVersion;
    }
}