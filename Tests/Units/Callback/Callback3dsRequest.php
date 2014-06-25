<?php

namespace Rezzza\PaymentBe2billBundle\Tests\Units\Callback;

use mageekguy\atoum;
use Rezzza\PaymentBe2billBundle\Callback\Callback3dsRequest as TestedRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * This file is part of the RezzzaPaymentBe2billBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

/**
 * Callback3dsRequest.
 *
 * @uses atoum\test
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Callback3dsRequest extends atoum\test
{
    public function testCreateFromRequest()
    {
        $request = new Request(array(
            'EXECCODE' => '0000',
            'TRANSACTIONID' => '123',
            'ORDERID' => '456',
            'MESSAGE' => 'foo'
        ));

        $this
            ->if($callback3dsRequest = TestedRequest::createFromRequest($request))
            ->string($callback3dsRequest->getExecCode())
                ->isEqualTo('0000')
            ->string($callback3dsRequest->getTransactionId())
                ->isEqualTo('123')
            ->string($callback3dsRequest->getOrderId())
                ->isEqualTo('456')
            ->string($callback3dsRequest->getMessage())
                ->isEqualTo('foo')
        ;
    }

    public function testIsSuccess()
    {
        $this
            ->if($callback3dsRequest = new TestedRequest('0000', null, null, null))
            ->boolean($callback3dsRequest->isSuccess())
                ->isTrue()
        ;

        $this
            ->if($callback3dsRequest = new TestedRequest('4008', null, null, null))
            ->boolean($callback3dsRequest->isSuccess())
                ->isFalse()
        ;
    }
}
