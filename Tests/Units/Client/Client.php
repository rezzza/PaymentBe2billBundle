<?php

namespace Rezzza\PaymentBe2billBundle\Tests\Units\Client;

use mageekguy\atoum;
use Rezzza\PaymentBe2billBundle\Client\Client as TestedClient;

/**
 * This file is part of the RezzzaPaymentBe2billBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

/**
 * Client
 *
 * @uses atoum\test
 * @author Jérémy Romey <jeremy@free-agent.fr>
 */
class Client extends atoum\test
{
    public function testConstruct()
    {
        $this
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', false, 'main'))
                ->boolean($client->getDebug())
                    ->isFalse()
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', true, 'main'))
                ->boolean($client->getDebug())
                    ->isTrue()
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', false, 'main'))
                ->boolean($client->getDebug())
                    ->isFalse()
        ;
    }

    public function testSetDebug()
    {
        $this
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', false, 'main'))
                ->boolean($client->getDebug())
                    ->isFalse()
            ->if($client->setDebug(true))
                ->boolean($client->getDebug())
                    ->isTrue()
            ->if($client->setDebug(false))
                ->boolean($client->getDebug())
                    ->isFalse()
        ;
    }

    public function testGetApiEndpoints()
    {
        $apiEndPoints = array(
            'sandbox' => array(
                'https://secure-test.be2bill.com/front/service/rest/process',
            ),
            'production' => array(
                'https://secure-magenta1.be2bill.com/front/service/rest/process.php',
                'https://secure-magenta2.be2bill.com/front/service/rest/process.php',
            ),
        );

        $this
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', false, 'main'))
                ->array($client->getApiEndpoints(false))
                    ->isIdenticalTo($apiEndPoints['production'])
            ->if($client->setDebug(true))
                ->array($client->getApiEndpoints(true))
                    ->isIdenticalTo($apiEndPoints['sandbox'])
        ;
    }

    public function testSortParameters()
    {
        $this
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', false, 'main'))
            ->and($parameters = array(
                'CLIENTIDENT'      => '404',
                'CLIENTREFERRER'   => 'example.org',
                'CLIENTUSERAGENT'  => 'Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350',
                'CLIENTIP'         => '127.0.0.1',
                'DESCRIPTION'      => 'Winter is coming',
                'ORDERID'          => '13003',
                'AMOUNT'           => '23.99',
                'VERSION'          => '2.0',
                'CARDFULLNAME'     => 'CHUCK NORRIS',
                'CLIENTEMAIL'      => 'chucknorris@example.org',
                'CARDCODE'         => '1111111111111111',
                'CARDCVV'          => '123',
                'CARDVALIDITYDATE' => '07-20',
                'CREATEALIAS'      => 'no',
            ))
                ->array($client->sortParameters($parameters))
                    ->isIdenticalTo(array(
                'AMOUNT'           => '23.99',
                'CARDCODE'         => '1111111111111111',
                'CARDCVV'          => '123',
                'CARDFULLNAME'     => 'CHUCK NORRIS',
                'CARDVALIDITYDATE' => '07-20',
                'CLIENTEMAIL'      => 'chucknorris@example.org',
                'CLIENTIDENT'      => '404',
                'CLIENTIP'         => '127.0.0.1',
                'CLIENTREFERRER'   => 'example.org',
                'CLIENTUSERAGENT'  => 'Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350',
                'CREATEALIAS'      => 'no',
                'DESCRIPTION'      => 'Winter is coming',
                'ORDERID'          => '13003',
                'VERSION'          => '2.0',
            ))
        ;
    }

    public function testConvertAmountToBe2billFormat()
    {
        $this
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', false, 'main'))
                ->integer($amount = $client->convertAmountToBe2billFormat('23.99'))
                    ->isIdenticalTo(2399)
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', false, 'main'))
                ->integer($client->convertAmountToBe2billFormat('23'))
                    ->isIdenticalTo(2300)
        ;
    }

    public function testGetSignature()
    {
        $this
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', false, 'main'))
            ->and($parameters = array(
                'CLIENTREFERRER'=> 'example.org',
                'CLIENTIDENT'   => '404',
            ))
                ->string($client->getSignature('CuirMoustache', $parameters))
                    ->isIdenticalTo(hash('sha256', 'CuirMoustacheCLIENTIDENT=404CuirMoustacheCLIENTREFERRER=example.orgCuirMoustache'))
        ;
    }

    public function testConfigure3dsParametersUnsupportedOperation()
    {
        $this
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', false, 'main'))
            ->and($parameters = array('3DSECURE' => 'yes', '3DSECUREDISPLAYMODE' => 'main'))
            ->and($parameters = $client->configureParameters('invalid', $parameters))
                ->array($params = $parameters['params'])
                    ->notHasKeys(array('3DSECURE', '3DSECUREDISPLAYMODE'))
        ;
    }

    public function testConfigure3dsParameters()
    {
        $this
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', false, 'main'))
            ->and($parameters = array('3DSECURE' => 'yes', '3DSECUREDISPLAYMODE' => 'top'))
            ->and($parameters = $client->configureParameters('payment', $parameters))
            ->and($params = $parameters['params'])
                ->string($params['3DSECURE'])
                    ->isEqualTo('yes')
                ->string($params['3DSECUREDISPLAYMODE'])
                    ->isEqualTo('top')
        ;
    }

    public function testConfigure3dsParametersDefaultMode()
    {
        $this
            ->if($client = new TestedClient('CHUCKNORRIS', 'CuirMoustache', false, 'main'))
            ->and($parameters = array('3DSECURE' => 'yes'))
            ->and($parameters = $client->configureParameters('payment', $parameters))
            ->and($params = $parameters['params'])
                ->string($params['3DSECURE'])
                    ->isEqualTo('yes')
                ->string($params['3DSECUREDISPLAYMODE'])
                    ->isEqualTo('main')
        ;
    }
}
