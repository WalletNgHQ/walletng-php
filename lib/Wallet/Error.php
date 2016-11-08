<?php
/**
 * Created by PhpStorm.
 * User: Peter.Makafan
 * Date: 11/6/2016
 * Time: 9:34 PM
 */

namespace Wallet;


/**
 * @property null httpStatus
 * @property null httpBody
 * @property mixed jsonBody
 * @property  param
 * @property null param
 */
class Error extends \Exception
{
    /**
     * Error constructor.
     * @param null $message
     * @param null $httpStatus
     * @param null $httpBody
     */
    public function __construct($message = null, $httpStatus = null, $httpBody = null)
    {
        parent::__construct($message);
        $this->httpStatus = $httpStatus;
        $this->httpBody = $httpBody;

        try {
            $this->jsonBody = json_decode($httpBody, true);
            if (isset($this->jsonBody) && !empty($this->jsonBody['error']['param'])) {
                $this->param = $this->jsonBody['error']['param'];
            } else {
                $this->param = null;
            }
        } catch (\Exception $e) {
            $this->jsonBody = null;
        }
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    public function getHttpBody()
    {
        return $this->httpBody;
    }
}