<?php

namespace Rezzza\PaymentBe2billBundle\Tests\Units\Callback\Controller;

use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInterface;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use mageekguy\atoum;
use Rezzza\PaymentBe2billBundle\Callback\Controller\TransactionControllerOnBe2BillRequest as TestedController;

/**
 * This file is part of the RezzzaPaymentBe2billBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

/**
 * TransactionControllerOnBe2BillRequest.
 *
 * @uses atoum\test
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class TransactionControllerOnBe2BillRequest extends atoum\test
{
    private $instruction;

    private $payment;

    private $transaction;

    public function testHandleSuccess()
    {
        $processedAmount = 1337;
        $transactionId = '1234567';

        // Request
        $this->mockGenerator->orphanize('__construct');
        $request = new \mock\Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest();
        $request->getMockController()->isSuccess = true;
        $request->getMockController()->getTransactionId = $transactionId;

        // Transaction
        $this->givenAFinancialTransactionWithStatus(FinancialTransactionInterface::STATE_PENDING);
        $this->transaction->getMockController()->getProcessedAmount = $processedAmount;

        // Repository
        $repository = new \mock\Rezzza\PaymentBe2billBundle\Repository\TransactionRepository;
        $repository->getMockController()->findOneByTrackingId = $this->transaction;

        $this
            ->if(
                $handler = new TestedController($repository),
                $handler->approveAndDeposit($request)
            )
            ->mock($this->payment)
                ->call('setState')
                    ->once()
                    ->withIdenticalArguments(PaymentInterface::STATE_DEPOSITED)
                ->call('setApprovingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
                ->call('setDepositingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
                ->call('setApprovedAmount')
                    ->once()
                    ->withIdenticalArguments($processedAmount)
                ->call('setDepositedAmount')
                    ->once()
                    ->withIdenticalArguments($processedAmount)
            ->mock($this->instruction)
                ->call('setApprovingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
                ->call('setDepositingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
                ->call('setApprovedAmount')
                    ->once()
                    ->withIdenticalArguments($processedAmount)
                ->call('setDepositedAmount')
                    ->once()
                    ->withIdenticalArguments($processedAmount)
            ->mock($this->transaction)
                ->call('setState')
                    ->once()
                    ->withIdenticalArguments(FinancialTransactionInterface::STATE_SUCCESS)
                ->call('setResponseCode')
                    ->once()
                    ->withIdenticalArguments(PluginInterface::RESPONSE_CODE_SUCCESS)
                ->call('setReasonCode')
                    ->once()
                    ->withIdenticalArguments(PluginInterface::REASON_CODE_SUCCESS)
            ->mock($repository)
                ->call('save')
                    ->withIdenticalArguments($this->transaction)
                    ->once()
        ;
    }

    public function test_it_should_save_wallet_alias_if_provided()
    {
        $processedAmount = 1337;
        $transactionId = '1234567';

        // Request
        $this->mockGenerator->orphanize('__construct');
        $request = new \mock\Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest();
        $request->getMockController()->isSuccess = true;
        $request->getMockController()->getTransactionId = $transactionId;
        $request->getMockController()->getParam = '4LI4S';

        // Transaction
        $this->givenAFinancialTransactionWithStatus(FinancialTransactionInterface::STATE_PENDING);
        $this->transaction->getMockController()->getProcessedAmount = $processedAmount;

        // Repository
        $repository = new \mock\Rezzza\PaymentBe2billBundle\Repository\TransactionRepository;
        $repository->getMockController()->findOneByTrackingId = $this->transaction;

         $this
            ->when(
                $handler = new TestedController($repository),
                $handler->approveAndDeposit($request)
            )
            ->then
                ->variable($this->transaction->getExtendedData()->get('ALIAS'))
                    ->isEqualTo('4LI4S')
        ;
    }

    public function testHandleFailure()
    {
        $transactionId = '1234567';
        $execCode = new \Rezzza\PaymentBe2billBundle\Client\Be2BillExecCode('0048');
        $message = 'hello';

        // Request
        $this->mockGenerator->orphanize('__construct');
        $request = new \mock\Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest();
        $request->getMockController()->isSuccess = false;
        $request->getMockController()->getTransactionId = $transactionId;
        $request->getMockController()->getExecCode = $execCode;
        $request->getMockController()->getMessage = $message;

        // Transaction
        $this->givenAFinancialTransactionWithStatus(FinancialTransactionInterface::STATE_PENDING);

        // Repository
        $repository = new \mock\Rezzza\PaymentBe2billBundle\Repository\TransactionRepository;
        $repository->getMockController()->findOneByTrackingId = $this->transaction;

        $this
            ->if(
                $handler = new TestedController($repository),
                $handler->approveAndDeposit($request)
            )
            ->mock($this->payment)
                ->call('setState')
                    ->once()
                    ->withIdenticalArguments(PaymentInterface::STATE_FAILED)
                ->call('setApprovingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
                ->call('setDepositingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
            ->mock($this->instruction)
                ->call('setApprovingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
                ->call('setDepositingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
            ->mock($this->transaction)
                ->call('setState')
                    ->once()
                    ->withIdenticalArguments(FinancialTransactionInterface::STATE_FAILED)
                ->call('setResponseCode')
                    ->once()
                    ->withIdenticalArguments($execCode)
                ->call('setReasonCode')
                    ->once()
                    ->withIdenticalArguments($message)
            ->mock($repository)
                ->call('save')
                    ->withIdenticalArguments($this->transaction)
                    ->once()
        ;
    }

    public function testHandleNonPendingTransaction()
    {
        // Request
        $this->mockGenerator->orphanize('__construct');
        $request = new \mock\Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest();

        // Financial transaction
        $this->givenAFinancialTransactionWithStatus(FinancialTransactionInterface::STATE_SUCCESS);

        // Repository
        $repository = new \mock\Rezzza\PaymentBe2billBundle\Repository\TransactionRepository;
        $repository->getMockController()->findOneByTrackingId = $this->transaction;

        $this
            ->if($handler = new TestedController($repository))
            ->exception(function () use ($handler, $request) {
                $handler->approveAndDeposit($request);
            })
        ;
    }

    public function testHandleUnexistingTransaction()
    {
        // Request
        $this->mockGenerator->orphanize('__construct');
        $request = new \mock\Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest();

        // Repository
        $repository = new \mock\Rezzza\PaymentBe2billBundle\Repository\TransactionRepository;
        $repository->getMockController()->findOneByTrackingId = null;

        $this
            ->if($handler = new TestedController($repository))
            ->exception(function () use ($handler, $request) {
                $handler->approveAndDeposit($request);
            })
        ;
    }

    private function givenAFinancialTransactionWithStatus($status)
    {
        // Instruction
        $this->instruction = new \mock\JMS\Payment\CoreBundle\Model\PaymentInstructionInterface();

        // Payment
        $this->payment = new \mock\JMS\Payment\CoreBundle\Model\PaymentInterface();
        $this->payment->getMockController()->getPaymentInstruction = $this->instruction;

        // Financial transaction
        $this->transaction = new \mock\JMS\Payment\CoreBundle\Entity\FinancialTransaction();
        $this->transaction->getMockController()->getState = $status;//FinancialTransactionInterface::STATE_PENDING;
        $this->transaction->getMockController()->getPayment = $this->payment;
    }
}
