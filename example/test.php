<?php
require_once("../lib/wallet-lib.php");
/**
 * Created by PhpStorm.
 * User: Peter.Makafan
 * Date: 11/6/2016
 * Time: 10:14 PM
 */
\Wallet\Wallet::setApiKey("vmsuqza8bu9o");
try{
    $data = array();
    $data["Amount"] = 20000;
    $data["Source"] = "2348050926456";
    $data["TransactionPassCode"] = "1010";
    $status = \Wallet\Resource::_transfer($data);

    print_r($status);

    echo '<br />';
}catch (Exception $e){
    echo "Status: " . $e->getHttpStatus() . ":\n";
    echo $e->getMessage();
    if (!empty($e->param)) {
        echo "\nInvalid param: {$e->param}";
    }
}