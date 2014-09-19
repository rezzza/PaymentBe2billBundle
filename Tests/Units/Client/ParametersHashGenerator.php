<?php

namespace Rezzza\PaymentBe2billBundle\Tests\Units\Client;

use mageekguy\atoum;
use Rezzza\PaymentBe2billBundle\Client\ParametersHashGenerator as TestedClass;

/**
 * This file is part of the RezzzaPaymentBe2billBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

class ParametersHashGenerator extends atoum\test
{
    /**
     * @dataProvider dataProviderHash
     */
    public function test_it_generate_hash($password, $parameters, $expected)
    {
        $this
            ->given(
                $hashGenerator = new TestedClass($password)
            )
            ->when(
                $hash = $hashGenerator->hash($parameters)
            )
            ->then
                ->string($hash)
                    ->isIdenticalTo(hash('sha256', $expected))
        ;
    }

    public function dataProviderHash()
    {
        return array(
            array(
                'PASSWORD',
                array(
                    'CLIENTREFERRER'=> 'example.org',
                    'CLIENTIDENT' => '404',
                ),
                'PASSWORDCLIENTIDENT=404PASSWORDCLIENTREFERRER=example.orgPASSWORD'
            ),
            array(
                'HeyYou',
                array(
                ),
                'HeyYou'
            ),
            array(
                123,
                array(
                    'THISIS' => 'crazy',
                    'AH' => 'ho',
                    array(
                        'so' => 'call',
                        'me' => 'maybe'
                    )
                ),
                '1230[me]=maybe1230[so]=call123AH=ho123THISIS=crazy123'
            )
        );
    }
}
