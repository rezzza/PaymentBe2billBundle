<?php

namespace Rezzza\PaymentBe2billBundle\Callback\Controller;

use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest;
use JMS\Payment\CoreBundle\Model\PaymentInterface;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;

/**
 * Base class for callback controllers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
abstract class AbstractCallbackController implements CallbackControllerInterface
{
    /**
     * Performs the approval and deposit of a callback request.
     *
     * @param Callback3dsRequest            $request
     * @param FinancialTransactionInterface $transaction
     *
     * @throws \RuntimeException
     */
    protected function doApproveAndDeposit(Be2BillRequest $request, FinancialTransactionInterface $transaction)
    {
        if (FinancialTransactionInterface::STATE_PENDING !== $transaction->getState()) {
            throw new \RuntimeException('The financial transaction must be pending.');
        }

        if ($request->isSuccess()) {
            $this->deposit($transaction);
        } else {
            $this->fail($transaction, $request);
        }
    }

    /**
     * Marks the transaction and related payment failed.
     *
     * @param FinancialTransactionInterface $transaction
     * @param Callback3dsRequest            $request
     */
    private function fail(FinancialTransactionInterface $transaction, Be2BillRequest $request)
    {
        $payment = $transaction->getPayment();
        $instruction = $payment->getPaymentInstruction();

        $payment->setState(PaymentInterface::STATE_FAILED);
        $payment->setApprovingAmount(0.0);
        $payment->setDepositingAmount(0.0);

        $instruction->setApprovingAmount(0.0);
        $instruction->setDepositingAmount(0.0);

        $transaction->setState(FinancialTransactionInterface::STATE_FAILED);
        $transaction->setResponseCode((string) $request->getExecCode());
        $transaction->setReasonCode($request->getMessage());
    }

    /**
     * Marks the transaction and related payment deposited.
     *
     * @param FinancialTransactionInterface $transaction
     */
    private function deposit(FinancialTransactionInterface $transaction)
    {
        $payment = $transaction->getPayment();
        $instruction = $payment->getPaymentInstruction();
        $processedAmount = $transaction->getProcessedAmount();

        $payment->setState(PaymentInterface::STATE_DEPOSITED);
        $payment->setApprovingAmount(0.0);
        $payment->setDepositingAmount(0.0);
        $payment->setApprovedAmount($processedAmount);
        $payment->setDepositedAmount($processedAmount);

        $instruction->setApprovingAmount(0.0);
        $instruction->setDepositingAmount(0.0);
        $instruction->setApprovedAmount($processedAmount);
        $instruction->setDepositedAmount($processedAmount);

        $transaction->setState(FinancialTransactionInterface::STATE_SUCCESS);
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }
}
