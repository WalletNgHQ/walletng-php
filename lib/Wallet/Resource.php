<?php
/**
 * Created by PhpStorm.
 * User: Peter.Makafan
 * Date: 11/6/2016
 * Time: 9:49 PM
 */

namespace Wallet;


class Resource
{

    /**
     *
     * @param type $params
     * @throws Error
     */
    private static function _validate($params = null)
    {
        if ($params && !is_array($params)) {
            throw new Error("You must pass an array as the first argument to ACE API method calls.");
        }
    }


    /**
     *
     * @param array $params
     * @return type
     */
    public  static function _transfer($params = null)
    {
        self::_validate( $params);
        $apiClient = new APIClient();
        $url = "/wallet";
        /** @var TYPE_NAME $apiKey */
        list($response, $apiKey) = $apiClient->request('post', $url, $params);

        return $response;
    }
}