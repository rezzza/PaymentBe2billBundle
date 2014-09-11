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

    public function __construct(Be2BillExecCode $execCode, $transactionId, $orderId, $message)
    {
        $this->execCode = $execCode;
        $this->transactionId = $transactionId;
        $this->orderId = $orderId;
        $this->message = $message;
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
            $message
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
     * @return string
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
}
