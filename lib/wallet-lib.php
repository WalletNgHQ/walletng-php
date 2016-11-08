<?php
/**
 * Created by PhpStorm.
 * User: Peter.Makafan
 * Date: 11/6/2016
 * Time: 10:10 PM
 */

if (!function_exists('curl_init')) {
    throw new Exception('WALLET API LIBRARY needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new Exception('WALLET API LIBRARY needs the JSON PHP extension.');
}

// Config and Utilities
require(dirname(__FILE__) . '/Wallet/Wallet.php');
require(dirname(__FILE__) . '/Wallet/Util.php');
require(dirname(__FILE__) . '/Wallet/Error.php');

// Guts
require(dirname(__FILE__) . '/Wallet/APIClient.php');
require(dirname(__FILE__) . '/Wallet/Resource.php');