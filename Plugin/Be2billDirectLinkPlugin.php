<?php

namespace Rezzza\PaymentBe2billBundle\Plugin;

use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Entity\ExtendedData;

use Rezzza\PaymentBe2billBundle\Client\Client;
use Rezzza\PaymentBe2billBundle\Plugin\Exception\SecureActionRequiredException;

/**
 * This file is part of the RezzzaPaymentBe2billBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

/**
 * Be2bill direct link plugin
 *
 * @author Jérémy Romey <jeremy@free-agent.fr>
 */
class Be2billDirectLinkPlugin extends AbstractPlugin
{
    protected $client;

    public function __construct(Client $client, $isDebug)
    {
        $this->client = $client;
        $this->client->setDebug($isDebug);

        parent::__construct($isDebug);
    }

    public function processes($paymentSystemName)
    {
        return 'be2bill_direct_link' === $paymentSystemName;
    }

    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {
        $this->approve($transaction, $retry);
        $this->deposit($transaction, $retry);
    }

    public function approve(FinancialTransactionInterface $transaction, $retry)
    {
        $transaction->setProcessedAmount($transaction->getPayment()->getTargetAmount());
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }

    public function deposit(FinancialTransactionInterface $transaction, $retry)
    {
        $parameters = $transaction->getPayment()->getPaymentInstruction()->getExtendedData()->get('be2bill_direct_link_params');

        $response = $this->client->requestPayment($parameters);

        $transaction->setTrackingId($response->getTransactionId());

        if ($response->isSecureActionRequired()) {
            $exception = new SecureActionRequiredException(sprintf('Deposit : transaction "%s" waits approval by 3DS', $response->getTransactionId()));
            $exception->setHtml($response->getSecureHtml());

            throw $exception;
        }

        if ($response->getAlias()) {
            $extendedData = new ExtendedData;
            $extendedData->set('ALIAS', $response->getAlias());
            $transaction->setExtendedData($extendedData);
        }

        if (!$response->isSuccess()) {
            $exception = new FinancialException(sprintf('Deposit : transaction "%s" is not valid', $response->getTransactionId()));
            $exception->setFinancialTransaction($transaction);
            $transaction->setResponseCode($response->getExecutionCode());
            $transaction->setReasonCode($response->getMessage());

            throw $exception;
        }

        $transaction->setProcessedAmount($transaction->getPayment()->getTargetAmount());
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }

    public function credit(FinancialTransactionInterface $transaction, $retry)
    {
        $parameters = $transaction->getCredit()->getPaymentInstruction()->getExtendedData()->get('be2bill_direct_link_params');

        $response = $this->client->requestRefund($parameters);

        $transaction->setTrackingId($response->getTransactionId());

        if (!$response->isSuccess()) {
            $exception = new FinancialException(sprintf('Credit: transaction "%s" is not valid', $response->getTransactionId()));
            $exception->setFinancialTransaction($transaction);
            $transaction->setResponseCode($response->getExecutionCode());
            $transaction->setReasonCode($response->getMessage());

            throw $exception;
        }

        $transaction->setProcessedAmount($transaction->getCredit()->getTargetAmount());
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }
}
