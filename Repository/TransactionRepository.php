<?php

namespace Rezzza\PaymentBe2billBundle\Repository;

use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;

interface TransactionRepository
{
    public function findOneByTrackingId($trackingId);

    public function save(FinancialTransactionInterface $transaction);
}
