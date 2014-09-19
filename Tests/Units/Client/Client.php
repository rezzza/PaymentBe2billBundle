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
    private $hashGenerator;

    private $httpClient;

    public function beforeTestMethod($method)
    {
        $this->hashGenerator = new \mock\Rezzza\PaymentBe2billBundle\Client\ParametersHashGenerator('CuirMoustache');
        $this->httpClient = new \mock\Guzzle\Http\Client;
    }

    public function test_it_use_production_endpoints()
    {
        $this
            ->given(
                $this->hashGenerator->getMockController()->hash = 'HASH',
                $client = new TestedClient($this->httpClient, 'CHUCKNORRIS', $this->hashGenerator, true, 'main'),
                $this->addGuzzleMockResponses(array(
                    new \Guzzle\Http\Message\Response(200),
                    new \Guzzle\Http\Message\Response(200)
                ))
            )
            ->when(
                $client->setDebug(false),
                $response = $client->requestRefund(array())
            )
             ->mock($this->httpClient)
                ->call('post')
                    ->withArguments(
                        'https://secure-magenta1.be2bill.com/front/service/rest/process.php',
                        null,
                        array(
                            'method' => 'refund',
                            'params' => array(
                                'IDENTIFIER' => 'CHUCKNORRIS',
                                'OPERATIONTYPE' => 'refund',
                                'HASH' => 'HASH'
                            )
                        )
                    )
                    ->once()
        ;
    }

    public function test_it_use_second_production_endpoint()
    {
        $this
            ->given(
                $this->hashGenerator->getMockController()->hash = 'HASH',
                $client = new TestedClient($this->httpClient, 'CHUCKNORRIS', $this->hashGenerator, true, 'main'),
                $this->addGuzzleMockResponses(array(
                    new \Guzzle\Http\Message\Response(500),
                    new \Guzzle\Http\Message\Response(200)
                ))
            )
            ->when(
                $client->setDebug(false),
                $response = $client->requestRefund(array())
            )
             ->mock($this->httpClient)
                ->call('post')
                    ->withArguments(
                        'https://secure-magenta1.be2bill.com/front/service/rest/process.php',
                        null,
                        array(
                            'method' => 'refund',
                            'params' => array(
                                'IDENTIFIER' => 'CHUCKNORRIS',
                                'OPERATIONTYPE' => 'refund',
                                'HASH' => 'HASH'
                            )
                        )
                    )
                    ->once()

                    ->withArguments(
                        'https://secure-magenta2.be2bill.com/front/service/rest/process.php',
                        null,
                        array(
                            'method' => 'refund',
                            'params' => array(
                                'IDENTIFIER' => 'CHUCKNORRIS',
                                'OPERATIONTYPE' => 'refund',
                                'HASH' => 'HASH'
                            )
                        )
                    )
                    ->once()
        ;
    }

    public function test_it_throw_exception_while_no_valid_response()
    {
        $this
            ->given(
                $this->hashGenerator->getMockController()->hash = 'HASH',
                $client = new TestedClient($this->httpClient, 'CHUCKNORRIS', $this->hashGenerator, true, 'main'),
                $this->addGuzzleMockResponses(array(
                    new \Guzzle\Http\Message\Response(500),
                    new \Guzzle\Http\Message\Response(403)
                ))
            )
            ->exception(
                function () use ($client) {
                    $client->setDebug(false);
                    $response = $client->requestRefund(array());
                }
            )
                ->isInstanceOf('JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException')

            ->mock($this->httpClient)
                ->call('post')
                    ->withArguments(
                        'https://secure-magenta1.be2bill.com/front/service/rest/process.php',
                        null,
                        array(
                            'method' => 'refund',
                            'params' => array(
                                'IDENTIFIER' => 'CHUCKNORRIS',
                                'OPERATIONTYPE' => 'refund',
                                'HASH' => 'HASH'
                            )
                        )
                    )
                    ->once()

                    ->withArguments(
                        'https://secure-magenta2.be2bill.com/front/service/rest/process.php',
                        null,
                        array(
                            'method' => 'refund',
                            'params' => array(
                                'IDENTIFIER' => 'CHUCKNORRIS',
                                'OPERATIONTYPE' => 'refund',
                                'HASH' => 'HASH'
                            )
                        )
                    )
                    ->once()
        ;
    }

    public function test_it_should_strip_3ds_parameters()
    {
        $this
            ->given(
                $this->hashGenerator->getMockController()->hash = 'HASH',
                $client = new TestedClient($this->httpClient, 'CHUCKNORRIS', $this->hashGenerator, true, 'main'),
                $this->addGuzzleMockResponses(array(new \Guzzle\Http\Message\Response(200)))
            )
            ->when(
                $response = $client->requestRefund(array(
                    '3DSECURE' => 'yes',
                    '3DSECUREDISPLAYMODE' => 'main'
                ))
            )
            ->mock($this->httpClient)
                ->call('post')
                    ->withArguments(
                        'https://secure-test.be2bill.com/front/service/rest/process',
                        null,
                        array(
                            'method' => 'refund',
                            'params' => array(
                                'IDENTIFIER' => 'CHUCKNORRIS',
                                'OPERATIONTYPE' => 'refund',
                                'HASH' => 'HASH'
                            )
                        )
                    )
                    ->once()
        ;
    }

    public function test_it_should_keep_3ds_parameters()
    {
        $this
            ->given(
                $this->hashGenerator->getMockController()->hash = 'HASH',
                $client = new TestedClient($this->httpClient, 'CHUCKNORRIS', $this->hashGenerator, true, 'main'),
                $this->addGuzzleMockResponses(array(new \Guzzle\Http\Message\Response(200)))
            )
            ->when(
                $response = $client->requestPayment(array(
                    '3DSECURE' => 'yes',
                    '3DSECUREDISPLAYMODE' => 'main'
                ))
            )
            ->mock($this->httpClient)
                ->call('post')
                    ->withArguments(
                        'https://secure-test.be2bill.com/front/service/rest/process',
                        null,
                        array(
                            'method' => 'payment',
                            'params' => array(
                                '3DSECURE' => 'yes',
                                '3DSECUREDISPLAYMODE' => 'main',
                                'IDENTIFIER' => 'CHUCKNORRIS',
                                'OPERATIONTYPE' => 'payment',
                                'HASH' => 'HASH'
                            )
                        )
                    )
                    ->once()
        ;
    }

    public function test_it_should_add_default_3ds_parameters()
    {
        $this
            ->given(
                $this->hashGenerator->getMockController()->hash = 'HASH',
                $client = new TestedClient($this->httpClient, 'CHUCKNORRIS', $this->hashGenerator, true, 'main'),
                $this->addGuzzleMockResponses(array(new \Guzzle\Http\Message\Response(200)))
            )
            ->when(
                $response = $client->requestPayment(array(
                    '3DSECURE' => 'yes',
                ))
            )
            ->mock($this->httpClient)
                ->call('post')
                    ->withArguments(
                        'https://secure-test.be2bill.com/front/service/rest/process',
                        null,
                        array(
                            'method' => 'payment',
                            'params' => array(
                                '3DSECURE' => 'yes',
                                '3DSECUREDISPLAYMODE' => 'main',
                                'IDENTIFIER' => 'CHUCKNORRIS',
                                'OPERATIONTYPE' => 'payment',
                                'HASH' => 'HASH'
                            )
                        )
                    )
                    ->once()
        ;
    }

    public function test_it_should_convert_amount()
    {
        $this
            ->given(
                $this->hashGenerator->getMockController()->hash = 'HASH',
                $client = new TestedClient($this->httpClient, 'CHUCKNORRIS', $this->hashGenerator, true, 'main'),
                $this->addGuzzleMockResponses(array(new \Guzzle\Http\Message\Response(200)))
            )
            ->when(
                $response = $client->requestPayment(array('AMOUNT' => 1.05))
            )
            ->mock($this->httpClient)
                ->call('post')
                    ->withArguments(
                        'https://secure-test.be2bill.com/front/service/rest/process',
                        null,
                        array(
                            'method' => 'payment',
                            'params' => array(
                                'AMOUNT' => 105,
                                'IDENTIFIER' => 'CHUCKNORRIS',
                                'OPERATIONTYPE' => 'payment',
                                'HASH' => 'HASH'
                            )
                        )
                    )
                    ->once()
        ;
    }

    private function addGuzzleMockResponses(array $responses)
    {
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        foreach ($responses as $response) {
            $plugin->addResponse($response);
        }

        $this->httpClient->addSubscriber($plugin);
    }
}
