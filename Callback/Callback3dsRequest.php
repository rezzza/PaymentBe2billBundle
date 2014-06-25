<?php

namespace Rezzza\PaymentBe2billBundle\Callback;

use Symfony\Component\HttpFoundation\Request;

/**
 * Represents a Be2bill 3DS callback request.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Callback3dsRequest
{
    private $execCode;
    private $transactionId;
    private $orderId;
    private $message;

    /**
     * Creates a new request.
     *
     * @param string $execCode
     * @param string $transactionId
     * @param string $orderId
     * @param string $message
     */
    public function __construct($execCode, $transactionId, $orderId, $message)
    {
        $this->execCode = $execCode;
        $this->transactionId = $transactionId;
        $this->orderId = $orderId;
        $this->message = $message;
    }

    /**
     * Creates a callback request from an HTTP request.
     *
     * @param Request $request
     *
     * @return Callback3dsRequest
     */
    public static function createFromRequest(Request $request)
    {
        return new self(
            $request->query->get('EXECCODE'),
            $request->query->get('TRANSACTIONID'),
            $request->query->get('ORDERID'),
            $request->query->get('MESSAGE')
        );
    }

    /**
     * Tells if the request is successful.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return '0000' === $this->execCode;
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
