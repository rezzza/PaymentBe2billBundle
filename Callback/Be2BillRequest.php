<?php

namespace Rezzza\PaymentBe2billBundle\Callback;

use Rezzza\PaymentBe2billBundle\Client\Be2BillExecCode;
use Rezzza\PaymentBe2billBundle\Client\ParametersHashGenerator;

class Be2BillRequest
{
    private $execCode;

    private $transactionId;

    private $orderId;

    private $message;

    private $params = array();

    public function __construct(Be2BillExecCode $execCode, $transactionId, $orderId, $message, array $params = array())
    {
        $this->execCode = $execCode;
        $this->transactionId = $transactionId;
        $this->orderId = $orderId;
        $this->message = $message;
        $this->params = $params;
    }

    public static function create($execCode, $transactionId, $orderId, $message, $hash, array $params, ParametersHashGenerator $hashGenerator)
    {
        unset($params['HASH']);

        if ($hashGenerator->hash($params) !== $hash) {
            throw new InvalidBe2BillRequestException;
        }

        return new self(
            new Be2BillExecCode($execCode),
            $transactionId,
            $orderId,
            $message,
            $params
        );
    }

    /**
     * Tells if the request is successful.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->execCode->isSuccess();
    }

    /**
     * Gets the exec code.
     *
     * @return Be2BillExecCode
     */
    public function getExecCode()
    {
        return $this->execCode;
    }

    /**
     * Gets the transaction id.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Gets the order id.
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Gets the message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function getParam($key)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
    }
}
