<?php

namespace Rezzza\PaymentBe2billBundle\Tests\Units\Callback;

use mageekguy\atoum;
use Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest as TestedRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * This file is part of the RezzzaPaymentBe2billBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

/**
 * Be2BillRequest.
 *
 * @uses atoum\test
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Be2BillRequest extends atoum\test
{
    public function test_it_create_a_request_verified()
    {
        $this
            ->given(
                $params = array(
                    'IDENTIFIER' => 'VERYLASTROOM',
                    'OPERATIONTYPE' => 'payment',
                    'TRANSACTIONID' => 'A55555',
                    '3DSECURE' => 'YES',
                ),
                $mockHashGenerator = new \mock\Rezzza\PaymentBe2billBundle\Client\ParametersHashGenerator('password'),
                $mockHashGenerator->getMockController()->hash = 'YOUPALALA',
                $request = new SymfonyRequest(array(), array_merge($params, array('HASH' => 'YOUPALALA')))
            )
            ->when(
                $be2BillRequest = TestedRequest::createFromRequest($request, $mockHashGenerator)
            )
            ->then
                ->object($be2BillRequest)
                    ->isInstanceOf('Rezzza\PaymentBe2billBundle\Callback\Be2BillRequest')
                ->mock($mockHashGenerator)
                    ->call('hash')
                        ->withArguments($params)
                        ->once()
        ;
    }

    public function test_it_fail_to_create_request()
    {
        $this
            ->given(
                $params = array(
                    'IDENTIFIER' => 'VERYLASTROOM',
                    'OPERATIONTYPE' => 'payment',
                    'TRANSACTIONID' => 'A55555',
                    '3DSECURE' => 'YES',
                ),
                $mockHashGenerator = new \mock\Rezzza\PaymentBe2billBundle\Client\ParametersHashGenerator('password'),
                $mockHashGenerator->getMockController()->hash = 'YOUPALALA',
                $request = new SymfonyRequest(array(), array_merge($params, array('HASH' => 'PERDU')))
            )
                ->exception(function () use ($request, $mockHashGenerator) {
                    TestedRequest::createFromRequest($request, $mockHashGenerator);
                })
                    ->isInstanceOf('Rezzza\PaymentBe2billBundle\Callback\InvalidBe2BillRequestException')
        ;
    }
}
