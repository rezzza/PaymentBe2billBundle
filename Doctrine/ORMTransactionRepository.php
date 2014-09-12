<?php

namespace Rezzza\PaymentBe2billBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;

use Rezzza\PaymentBe2billBundle\Repository\TransactionRepository;

class ORMTransactionRepository implements TransactionRepository
{
    private $om;

    private $internalRepository;

    public function __construct(ObjectManager $om, $transactionClass)
    {
        $this->om = $om;
        $this->internalRepository = $om->getRepository($transactionClass);
    }

    public function findOneByTrackingId($trackingId)
    {
        return $this->internalRepository->findOneBy(array('trackingId' => $trackingId));
    }

    public function save(FinancialTransactionInterface $transaction)
    {
        $this->om->persist($transaction);
        $this->om->persist($transaction->getPayment());
        $this->om->persist($transaction->getPayment()->getPaymentInstruction());

        $this->om->flush();
    }
}
