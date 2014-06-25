<?php

namespace Rezzza\PaymentBe2billBundle\Callback\Controller;

use Rezzza\PaymentBe2billBundle\Callback\Callback3dsRequest;

/**
 * Interface for 3DS callback controllers.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface Callback3dsControllerInterface
{
    /**
     * Approves an deposits a 3DS callback request.
     *
     * @param Callback3dsRequest $request
     *
     * @throws \Exception
     */
    public function approveAndDeposit(Callback3dsRequest $request);
}
