<?php

namespace Rezzza\PaymentBe2billBundle\Callback;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class Be2BillRequest
{
    private $execCode;

    private $transactionId;

    private $orderId;

    private $message;

    private $hash;

    private $params;

    public function __construct(Be2BillExecCode $execCode, $transactionId, $orderId, $message, $hash, ParameterBag $params)
    {
        $this->execCode = $execCode;
        $this->transactionId = $transactionId;
        $this->orderId = $orderId;
        $this->message = $message;
        $this->hash = $hash;
        $this->params = $params;
    }

    public static function createFromRequest(Request $request)
    {
        return new self(
            new Be2BillExecCode($request->query->get('EXECCODE')),
            $request->query->get('TRANSACTIONID'),
            $request->query->get('ORDERID'),
            $request->query->get('MESSAGE'),
            $request->query->get('HASH'),
            $request->request
        );
    }

    private function validSignature($hashGenerator)
    {

    }

    /**
     * Tells if the request is successful.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return '0000' === $this->execCode->isSuccess();
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
