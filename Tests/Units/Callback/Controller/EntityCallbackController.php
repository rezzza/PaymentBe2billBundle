<?php

namespace Rezzza\PaymentBe2billBundle\Tests\Units\Callback\Controller;

use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInterface;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use mageekguy\atoum;
use Rezzza\PaymentBe2billBundle\Callback\Controller\EntityCallbackController as TestedController;

/**
 * This file is part of the RezzzaPaymentBe2billBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

/**
 * EntityCallbackController.
 *
 * @uses atoum\test
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class EntityCallbackController extends atoum\test
{
    public function testHandleSuccess()
    {
        $processedAmount = 1337;
        $transactionClass = 'JMS\Payment\CoreBundle\Entity\FinancialTransaction';
        $transactionId = '1234567';

        // Request
        $this->mockGenerator->orphanize('__construct');
        $request = new \mock\Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest();
        $request->getMockController()->isSuccess = true;
        $request->getMockController()->getTransactionId = $transactionId;

        // Instruction
        $instruction = new \mock\JMS\Payment\CoreBundle\Model\PaymentInstructionInterface();

        // Payment
        $payment = new \mock\JMS\Payment\CoreBundle\Model\PaymentInterface();
        $payment->getMockController()->getPaymentInstruction = $instruction;

        // Financial transaction
        $transaction = new \mock\JMS\Payment\CoreBundle\Entity\FinancialTransaction();
        $transaction->getMockController()->getState = FinancialTransactionInterface::STATE_PENDING;
        $transaction->getMockController()->getPayment = $payment;
        $transaction->getMockController()->getProcessedAmount = $processedAmount;

        // Repository
        $this->mockGenerator->orphanize('__construct');
        $repository = new \mock\Doctrine\ORM\EntityRepository();
        $repository->getMockController()->findOneBy = $transaction;

        // Connection
        $this->mockGenerator->orphanize('__construct');
        $connection = new \mock\Doctrine\DBAL\Connection();
        $connection->getMockController()->beginTransaction = function () {};
        $connection->getMockController()->commit = function () {};

        // Entity manager
        $this->mockGenerator->orphanize('__construct');
        $entityManager = new \mock\Doctrine\ORM\EntityManager();
        $entityManager->getMockController()->getRepository = $repository;
        $entityManager->getMockController()->getConnection = $connection;

        $this
            ->if(
                $handler = new TestedController($entityManager, $transactionClass),
                $handler->approveAndDeposit($request)
            )
            ->mock($repository)
                ->call('findOneBy')
                    ->once()
                    ->withIdenticalArguments(array('trackingId' => $transactionId))
            ->mock($payment)
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
            ->mock($instruction)
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
            ->mock($transaction)
                ->call('setState')
                    ->once()
                    ->withIdenticalArguments(FinancialTransactionInterface::STATE_SUCCESS)
                ->call('setResponseCode')
                    ->once()
                    ->withIdenticalArguments(PluginInterface::RESPONSE_CODE_SUCCESS)
                ->call('setReasonCode')
                    ->once()
                    ->withIdenticalArguments(PluginInterface::REASON_CODE_SUCCESS)
            ->mock($connection)
                ->call('beginTransaction')
                    ->once()
                ->call('commit')
                    ->once()
            ->mock($entityManager)
                ->call('persist')
                    ->withIdenticalArguments($payment)
                    ->once()
                    ->withIdenticalArguments($instruction)
                    ->once()
                    ->withIdenticalArguments($transaction)
                    ->once()
                ->call('flush')
                    ->once()
        ;
    }

    public function testHandleFailure()
    {
        $transactionClass = 'JMS\Payment\CoreBundle\Entity\FinancialTransaction';
        $transactionId = '1234567';
        $execCode = '0048';
        $message = 'hello';

        // Request
        $this->mockGenerator->orphanize('__construct');
        $request = new \mock\Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest();
        $request->getMockController()->isSuccess = false;
        $request->getMockController()->getTransactionId = $transactionId;
        $request->getMockController()->getExecCode = $execCode;
        $request->getMockController()->getMessage = $message;

        // Instruction
        $instruction = new \mock\JMS\Payment\CoreBundle\Model\PaymentInstructionInterface();

        // Payment
        $payment = new \mock\JMS\Payment\CoreBundle\Model\PaymentInterface();
        $payment->getMockController()->getPaymentInstruction = $instruction;

        // Financial transaction
        $transaction = new \mock\JMS\Payment\CoreBundle\Entity\FinancialTransaction();
        $transaction->getMockController()->getState = FinancialTransactionInterface::STATE_PENDING;
        $transaction->getMockController()->getPayment = $payment;

        // Repository
        $this->mockGenerator->orphanize('__construct');
        $repository = new \mock\Doctrine\ORM\EntityRepository();
        $repository->getMockController()->findOneBy = $transaction;

        // Connection
        $this->mockGenerator->orphanize('__construct');
        $connection = new \mock\Doctrine\DBAL\Connection();
        $connection->getMockController()->beginTransaction = function () {};
        $connection->getMockController()->commit = function () {};

        // Entity manager
        $this->mockGenerator->orphanize('__construct');
        $entityManager = new \mock\Doctrine\ORM\EntityManager();
        $entityManager->getMockController()->getRepository = $repository;
        $entityManager->getMockController()->getConnection = $connection;

        $this
            ->if(
                $handler = new TestedController($entityManager, $transactionClass),
                $handler->approveAndDeposit($request)
            )
            ->mock($repository)
                ->call('findOneBy')
                    ->once()
                    ->withIdenticalArguments(array('trackingId' => $transactionId))
            ->mock($payment)
                ->call('setState')
                    ->once()
                    ->withIdenticalArguments(PaymentInterface::STATE_FAILED)
                ->call('setApprovingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
                ->call('setDepositingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
            ->mock($instruction)
                ->call('setApprovingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
                ->call('setDepositingAmount')
                    ->once()
                    ->withIdenticalArguments(0.0)
            ->mock($transaction)
                ->call('setState')
                    ->once()
                    ->withIdenticalArguments(FinancialTransactionInterface::STATE_FAILED)
                ->call('setResponseCode')
                    ->once()
                    ->withIdenticalArguments($execCode)
                ->call('setReasonCode')
                    ->once()
                    ->withIdenticalArguments($message)
            ->mock($connection)
                ->call('beginTransaction')
                    ->once()
                ->call('commit')
                    ->once()
            ->mock($entityManager)
                ->call('persist')
                    ->withIdenticalArguments($payment)
                    ->once()
                    ->withIdenticalArguments($instruction)
                    ->once()
                    ->withIdenticalArguments($transaction)
                    ->once()
                ->call('flush')
                    ->once()
        ;
    }

    public function testHandleNonPendingTransaction()
    {
        // Request
        $this->mockGenerator->orphanize('__construct');
        $request = new \mock\Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest();

        // Financial transaction
        $transaction = new \mock\JMS\Payment\CoreBundle\Entity\FinancialTransaction();
        $transaction->getMockController()->getState = FinancialTransactionInterface::STATE_SUCCESS;

        // Repository
        $this->mockGenerator->orphanize('__construct');
        $repository = new \mock\Doctrine\ORM\EntityRepository();
        $repository->getMockController()->findOneBy = $transaction;

        // Entity manager
        $this->mockGenerator->orphanize('__construct');
        $entityManager = new \mock\Doctrine\ORM\EntityManager();
        $entityManager->getMockController()->getRepository = $repository;

        $this
            ->if($handler = new TestedController($entityManager, ''))
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
        $this->mockGenerator->orphanize('__construct');
        $repository = new \mock\Doctrine\ORM\EntityRepository();
        $repository->getMockController()->findOneBy = null;

        // Entity manager
        $this->mockGenerator->orphanize('__construct');
        $entityManager = new \mock\Doctrine\ORM\EntityManager();
        $entityManager->getMockController()->getRepository = $repository;

        $this
            ->if($handler = new TestedController($entityManager, ''))
            ->exception(function () use ($handler, $request) {
                $handler->approveAndDeposit($request);
            })
        ;
    }
}
