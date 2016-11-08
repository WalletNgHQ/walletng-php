<?php
/**
 * Created by PhpStorm.
 * User: Peter.Makafan
 * Date: 11/6/2016
 * Time: 9:37 PM
 */

namespace Wallet;


abstract class Util
{
    public static function isList($array)
    {
        if (!is_array($array)) {

            return false;
        }
        foreach (array_keys($array) as $k) {
            if (!is_numeric($k)) {

                return false;
            }
        }

        return true;
    }



}
