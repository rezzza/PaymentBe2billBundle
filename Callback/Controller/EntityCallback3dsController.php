<?php

namespace Rezzza\PaymentBe2billBundle\Callback\Controller;

use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use Rezzza\PaymentBe2billBundle\Callback\Callback3dsRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManager;

/**
 * Handles a 3DS callback and persist the result with an EntityManager.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class EntityCallback3dsController extends AbstractCallback3dsController
{
    private $entityManager;
    private $transactionClass;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager
     * @param string        $transactionClass
     */
    public function __construct(EntityManager $entityManager, $transactionClass)
    {
        $this->entityManager = $entityManager;
        $this->transactionClass = $transactionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function approveAndDeposit(Callback3dsRequest $request)
    {
        $transaction = $this->entityManager->getRepository($this->transactionClass)
            ->findOneBy(array('trackingId' => $request->getTransactionId()));

        if (!$transaction instanceof FinancialTransactionInterface) {
            throw new NotFoundHttpException(sprintf(
                'Cannot find the transaction with tracking id "%s"',
                $request->getTransactionId()
            ));
        }

        $this->doApproveAndDeposit($request, $transaction);

        $this->entityManager->getConnection()->beginTransaction();

        try {
            $this->entityManager->persist($transaction);
            $this->entityManager->persist($transaction->getPayment());
            $this->entityManager->persist($transaction->getPayment()->getPaymentInstruction());

            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollback();
            $this->entityManager->close();

            throw $e;
        }
    }
}
