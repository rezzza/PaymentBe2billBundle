<?php

namespace Rezzza\PaymentBe2billBundle\Plugin;

use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use Rezzza\PaymentBe2billBundle\Client\Client;

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
    protected $apiEndPoints;

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
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_PENDING);
    }

    function deposit(FinancialTransactionInterface $transaction, $retry)
    {
        $parameters = $transaction->getPayment()->getPaymentInstruction()->getExtendedData()->get('be2bill_direct_link_params');

        $response = $this->client->requestPayment($parameters);

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
}
