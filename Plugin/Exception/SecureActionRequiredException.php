<?php

namespace Rezzza\PaymentBe2billBundle\Plugin\Exception;

use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;

/**
 * This file is part of the RezzzaPaymentBe2billBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

/**
 * This exception is thrown when the transaction is secured by 3DS
 * and the user needs to perform an action (eg. enter his pin code).
 * The HTML (base64 encoded) needed to display the form can be accessed with getHtml().
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class SecureActionRequiredException extends ActionRequiredException
{
    private $html;

    /**
     * Sets the html for the form.
     *
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * Gets the html.
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }
}
