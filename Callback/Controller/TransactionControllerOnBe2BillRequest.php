<?php

namespace Rezzza\PaymentBe2billBundle\Callback\Controller;

use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInterface;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use JMS\Payment\CoreBundle\Entity\ExtendedData;

use Rezzza\PaymentBe2billBundle\Client\Be2BillExecCode;
use Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest;
use Rezzza\PaymentBe2billBundle\Repository\TransactionRepository;
use Rezzza\PaymentBe2billBundle\Exception\NotFoundTransactionException;

/**
 * Class for callback controllers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 * @author Timothée Barray <tim@amicalement-web.net>
 */
class TransactionControllerOnBe2BillRequest
{
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Performs the approval and deposit of a callback request.
     *
     * @param Be2BillRequest            $request
     * @param FinancialTransactionInterface $transaction
     *
     * @throws \RuntimeException
     */
    public function approveAndDeposit(Be2BillRequest $request)
    {
        $transaction = $this->transactionRepository->findOneByTrackingId($request->getTransactionId());

        if (null === $transaction) {
            throw new NotFoundTransactionException(
                sprintf('No transaction found with tracking "%s"', $request->getTransactionId())
            );
        }

        if (FinancialTransactionInterface::STATE_PENDING !== $transaction->getState()) {
            throw new \RuntimeException('The financial transaction must be pending.');
        }

        if ($request->isSuccess()) {
            $this->deposit($transaction, $request->getParam('ALIAS'));
        } else {
            $this->fail($transaction, $request->getExecCode(), $request->getMessage());
        }
    }

    /**
     * Marks the transaction and related payment failed.
     *
     * @param FinancialTransactionInterface $transaction
     * @param Be2BillRequest            $request
     */
    private function fail(FinancialTransactionInterface $transaction, Be2BillExecCode $execCode, $failReason)
    {
        $payment = $transaction->getPayment();
        $instruction = $payment->getPaymentInstruction();

        $payment->setState(PaymentInterface::STATE_FAILED);
        $payment->setApprovingAmount(0.0);
        $payment->setDepositingAmount(0.0);

        $instruction->setApprovingAmount(0.0);
        $instruction->setDepositingAmount(0.0);

        $transaction->setState(FinancialTransactionInterface::STATE_FAILED);
        $transaction->setResponseCode((string) $execCode);
        $transaction->setReasonCode($failReason);

        $this->transactionRepository->save($transaction);
    }

    /**
     * Marks the transaction and related payment deposited.
     *
     * @param FinancialTransactionInterface $transaction
     */
    private function deposit(FinancialTransactionInterface $transaction, $walletAlias = null)
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

        if (null !== $walletAlias) {
            $extendedData = new ExtendedData;
            $extendedData->set('ALIAS', $walletAlias);
            $transaction->setExtendedData($extendedData);
        }

        $this->transactionRepository->save($transaction);
    }
}
