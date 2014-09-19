<?php

namespace Rezzza\PaymentBe2billBundle\Client;

class Be2BillExecCode
{
    const STATUS_CANCELED = 'canceled';
    const STATUS_PAYMENT_ERROR = 'payment_error';
    const STATUS_CLIENT_ERROR = 'client_error';
    const STATUS_REMOTE_ERROR = 'remote_error';
    const STATUS_SUCCESS = 'success';
    const STATUS_PENDING = 'pending';
    const STATUS_FAILED = 'failed';

    private $value;

    private $status;

    public function __construct($value)
    {
        $this->value = (string) $value;
        $this->status = $this->readStatusFromExecCode($value);
    }

    public function __toString()
    {
        return $this->getValue();
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isSuccess()
    {
        return self::STATUS_SUCCESS === $this->status;
    }

    public function isPending()
    {
        return self::STATUS_PENDING === $this->status;
    }

    public function isCanceled()
    {
        return self::STATUS_CANCELED === $this->status;
    }

    public function isClientError()
    {
        return self::STATUS_CLIENT_ERROR === $this->status;
    }

    public function isRemoteError()
    {
        return self::STATUS_REMOTE_ERROR === $this->status;
    }

    public function isPaymentError()
    {
        return self::STATUS_PAYMENT_ERROR === $this->status;
    }

    public function isFailed()
    {
        return self::STATUS_FAILED === $this->status;
    }

    private function readStatusFromExecCode($execCode)
    {
        switch ((string) $execCode) {
            case '4004': // Canceled transaction
                $status = self::STATUS_CANCELED;
                break;
            case '4002': // insufficient funds
            case '4003': // credit card refused by bank network
            case '4005': // fraud suspicion
            case '4006': // lost credit card
            case '4007': // stolen credit card
            case '4010': // invalid transaction
            case '4011': // duplicate transaction
            case '4012': // invalid credit card informations
            case '4013': // transaction unauthorized from bank network for card holder
            case '6001': // transaction refused by the merchant
            case '6002': // transaction refused
            case '6003': // transaction already contested by card holder
            case '6004': // transaction refused by merchant ruleset
                $status = self::STATUS_PAYMENT_ERROR;
                break;
            case '1001': // missing parameter X
            case '1002': // invalid parameter X
            case '1003': // hash error
            case '1004': // unsupported protocol
            case '1005': // REST error
            case '3001': // disabled cient account
            case '3002': // client ip not authorized
            case '3003': // transaction not authorized
                $status = self::STATUS_CLIENT_ERROR;
                break;
            case '5001': // exchange protocol error
            case '5002': // bank network error
            case '5003': // service in maintenance
            case '5004': // request timeout, will be notified by notification url
            case '5005': // 3ds module display error
                $status = self::STATUS_REMOTE_ERROR;
                break;
            case '0000': // sucessful transaction
                $status = self::STATUS_SUCCESS;
                break;
            case '0001': // 3ds authorization required
                $status = self::STATUS_PENDING;
                break;
            case '2001': // alias not found
            case '2002': // unfinished reference transaction
            case '2003': // non-refundable reference transaction
            case '2004': // reference transaction not found
            case '2005': // reference auth can't be captured
            case '2006': // unsucessful reference transaction
            case '2007': // invalid amount for capture
            case '2008': // invalid amount for refund
            case '2009': // expired authorization
            case '4001': // unauthorizzed transaction from bank network
            case '4008': // 3ds authentication failed
            case '4009': // 3ds authentication expired
            default:
                $status = self::STATUS_FAILED;
                break;
        }

        return $status;
    }
}
