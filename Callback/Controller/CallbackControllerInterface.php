<?php

namespace Rezzza\PaymentBe2billBundle\Callback\Controller;

use Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest;

/**
 * Interface for 3DS callback controllers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface CallbackControllerInterface
{
    /**
     * Approves an deposits a callback request.
     *
     * @param Be2BillRequest $request
     *
     * @throws \Exception
     */
    public function approveAndDeposit(Be2BillRequest $request);
}
