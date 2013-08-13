<?php

namespace Rezzza\PaymentBe2billBundle\Tests\Units\Client;

require_once __DIR__ . '/../../../vendor/autoload.php';

use mageekguy\atoum;
use Rezzza\PaymentBe2billBundle\Client\Response as TestedResponse;

/**
 * Response
 *
 * @uses atoum\test
 * @author Jérémy Romey <jeremy@free-agent.fr>
 */
class Response extends atoum\test
{
    public function testConstruct()
    {
        $this
            ->if($response = new TestedResponse(array('chuck' => 'norris')))
                ->object($response->body)
                    ->isInstanceOf('Symfony\Component\HttpFoundation\ParameterBag')
                ->string($response->body->get('chuck'))
                    ->isIdenticalTo('norris')
        ;
    }

    public function testGetOperationType()
    {
        $this
            ->if($response = new TestedResponse(array('OPERATIONTYPE' => 'Enter the Dragon')))
                ->string($response->getOperationType())
                    ->isIdenticalTo('Enter the Dragon')
        ;
    }

    public function testGetTransactionId()
    {
        $this
            ->if($response = new TestedResponse(array('TRANSACTIONID' => '13003')))
                ->string($response->getTransactionId())
                    ->isIdenticalTo('13003')
        ;
    }

    public function testGetExecutionCode()
    {
        $this
            ->if($response = new TestedResponse(array('EXECCODE' => '404')))
                ->string($response->getExecutionCode())
                    ->isIdenticalTo('404')
        ;
    }

    public function testGetMessage()
    {
        $this
            ->if($response = new TestedResponse(array('MESSAGE' => 'Winter is coming')))
                ->string($response->getMessage())
                    ->isIdenticalTo('Winter is coming')
        ;
    }

    public function testIsSuccess()
    {
        $this
            ->if($response = new TestedResponse(array('EXECCODE' => '0000')))
                ->boolean($response->isSuccess())
                    ->isTrue()
            ->if($response = new TestedResponse(array('EXECCODE' => '9999')))
                ->boolean($response->isSuccess())
                    ->isFalse()
            ->if($response = new TestedResponse(array()))
                ->boolean($response->isSuccess())
                    ->isFalse()
        ;
    }

    public function testIsError()
    {
        $this
            ->if($response = new TestedResponse(array('EXECCODE' => '9999')))
                ->boolean($response->isError())
                    ->isTrue()
            ->if($response = new TestedResponse(array('EXECCODE' => '0000')))
                ->boolean($response->isError())
                    ->isFalse()
            ->if($response = new TestedResponse(array()))
                ->boolean($response->isError())
                    ->isTrue()
        ;
    }

    public function testIs3dSecureError()
    {
        $this
            ->if($response = new TestedResponse(array('EXECCODE' => '0001')))
                ->boolean($response->is3dSecureError())
                    ->isTrue()
            ->if($response = new TestedResponse(array('EXECCODE' => '0000')))
                ->boolean($response->is3dSecureError())
                    ->isFalse()
            ->if($response = new TestedResponse(array()))
                ->boolean($response->is3dSecureError())
                    ->isFalse()
        ;
    }

    public function testIsValidationError()
    {
        for ($i = 0; $i < 100; $i++) {
            $this
                ->if($response = new TestedResponse(array('EXECCODE' => sprintf('10%s', $i < 10 ? '0' . $i : $i))))
                    ->boolean($response->isValidationError())
                        ->isTrue()
            ;
        }
        $this
            ->if($response = new TestedResponse(array('EXECCODE' => '0000')))
                ->boolean($response->isValidationError())
                    ->isFalse()
            ->if($response = new TestedResponse(array()))
                ->boolean($response->isValidationError())
                    ->isFalse()
        ;
    }

    public function testIsTransactionUpdateError()
    {
        for ($i = 0; $i < 100; $i++) {
            $this
                ->if($response = new TestedResponse(array('EXECCODE' => sprintf('20%s', $i < 10 ? '0' . $i : $i))))
                    ->boolean($response->isTransactionUpdateError())
                        ->isTrue()
            ;
        }
        $this
            ->if($response = new TestedResponse(array('EXECCODE' => '0000')))
                ->boolean($response->isTransactionUpdateError())
                    ->isFalse()
            ->if($response = new TestedResponse(array()))
                ->boolean($response->isTransactionUpdateError())
                    ->isFalse()
        ;
    }

    public function testIsConfigurationError()
    {
        for ($i = 0; $i < 100; $i++) {
            $this
                ->if($response = new TestedResponse(array('EXECCODE' => sprintf('30%s', $i < 10 ? '0' . $i : $i))))
                    ->boolean($response->isConfigurationError())
                        ->isTrue()
            ;
        }
        $this
            ->if($response = new TestedResponse(array('EXECCODE' => '0000')))
                ->boolean($response->isConfigurationError())
                    ->isFalse()
            ->if($response = new TestedResponse(array()))
                ->boolean($response->isConfigurationError())
                    ->isFalse()
        ;
    }

    public function testIsBankError()
    {
        for ($i = 0; $i < 100; $i++) {
            $this
                ->if($response = new TestedResponse(array('EXECCODE' => sprintf('40%s', $i < 10 ? '0' . $i : $i))))
                    ->boolean($response->isBankError())
                        ->isTrue()
            ;
        }
        $this
            ->if($response = new TestedResponse(array('EXECCODE' => '0000')))
                ->boolean($response->isBankError())
                    ->isFalse()
            ->if($response = new TestedResponse(array()))
                ->boolean($response->isBankError())
                    ->isFalse()
        ;
    }

    public function testIsInternalError()
    {
        for ($i = 0; $i < 100; $i++) {
            $this
                ->if($response = new TestedResponse(array('EXECCODE' => sprintf('50%s', $i < 10 ? '0' . $i : $i))))
                    ->boolean($response->isInternalError())
                        ->isTrue()
            ;
        }
        $this
            ->if($response = new TestedResponse(array('EXECCODE' => '0000')))
                ->boolean($response->isInternalError())
                    ->isFalse()
            ->if($response = new TestedResponse(array()))
                ->boolean($response->isInternalError())
                    ->isFalse()
        ;
    }

    public function testToString()
    {
        $this
            ->if($response = new TestedResponse(array('chuck' => 'norris')))
                ->object($response->body)
                    ->isInstanceOf('Symfony\Component\HttpFoundation\ParameterBag')
                ->string((string) $response)
                    ->isIdenticalTo(json_encode(array('chuck' => 'norris')))
        ;
    }

    public function testToArray()
    {
        $this
            ->if($response = new TestedResponse(array('chuck' => 'norris')))
                ->object($response->body)
                    ->isInstanceOf('Symfony\Component\HttpFoundation\ParameterBag')
                ->array($response->toArray())
                    ->isIdenticalTo(array('chuck' => 'norris'))
        ;
    }

    public function testToJson()
    {
        $this
            ->if($response = new TestedResponse(array('chuck' => 'norris')))
                ->object($response->body)
                    ->isInstanceOf('Symfony\Component\HttpFoundation\ParameterBag')
                ->string($response->toJson())
                    ->isIdenticalTo(json_encode(array('chuck' => 'norris')))
        ;
    }
}
