<?php
namespace Rezzza\PaymentBe2billBundle\Client;

use Guzzle\Http\ClientInterface as HttpClient;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Exception\BadResponseException;
use JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException;

/**
 * This file is part of the RezzzaPaymentBe2billBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

/**
 * @author Jérémy Romey <jeremy@free-agent.fr>
 */
class Client
{
    const PAYMENT_OPERATION = 'payment';
    const AUTHORIZATION_OPERATION = 'authorization';
    const REFUND_OPERATION = 'refund';

    const SECURE_3DS_PARAM = '3DSECURE';
    const DISPLAY_MODE_3DS_PARAM = '3DSECUREDISPLAYMODE';

    protected $httpClient;
    protected $apiEndPoints;
    protected $identifier;
    protected $hashGenerator;
    protected $isDebug;
    protected $default3dsDisplayMode;

    public function __construct(HttpClient $httpClient, $identifier, ParametersHashGenerator $hashGenerator, $isDebug, $default3dsDisplayMode)
    {
        $this->httpClient = $httpClient;
        $this->identifier = $identifier;
        $this->hashGenerator = $hashGenerator;
        $this->isDebug = (bool) $isDebug;
        $this->default3dsDisplayMode = $default3dsDisplayMode;
        $this->apiEndPoints = array(
            'sandbox' => array(
                'https://secure-test.be2bill.com/front/service/rest/process',
            ),
            'production' => array(
                'https://secure-magenta1.be2bill.com/front/service/rest/process.php',
                'https://secure-magenta2.be2bill.com/front/service/rest/process.php',
            ),
        );
    }

    public function setDebug($isDebug)
    {
        $this->isDebug = !!$isDebug;
    }

    public function requestPayment(array $parameters)
    {
        return $this->sendApiRequest(
            $this->configureParameters(self::PAYMENT_OPERATION, $parameters)
        );
    }

    public function requestRefund(array $parameters)
    {
        return $this->sendApiRequest(
            $this->configureParameters(self::REFUND_OPERATION, $parameters)
        );
    }

    protected function configureParameters($operation, array $parameters)
    {
        $this->strip3dsParametersForUnsupportedOperation($operation, $parameters);
        $this->configure3dsParameters($parameters);

        $parameters['IDENTIFIER'] = $this->identifier;
        $parameters['OPERATIONTYPE'] = $operation;

        if (isset($parameters['AMOUNT'])) {
            $parameters['AMOUNT'] = $this->convertAmountToBe2billFormat($parameters['AMOUNT']);
        }

        $parameters['HASH'] = $this->hashGenerator->hash($parameters);

        $parameters = array(
            'method' => $operation,
            'params' => $parameters,
        );

        return $parameters;
    }

    protected function sendApiRequest(array $parameters)
    {
        $apiEndPoints = $this->getApiEndpoints();
        $guzzleException = null;

        if (empty($apiEndPoints)) {
            throw new CommunicationException('No Api Endpoint configured.');
        }

        foreach ($apiEndPoints as $apiEndPoint) {
            try {
                $request = $this->httpClient->post($apiEndPoint, null, $parameters);
                $response = $this->httpClient->send($request);
            } catch (BadResponseException $e) {
                $guzzleException = $e;
                $response = $e->getResponse();
            }

            // If the request is secure, we set a flag on the response to process it easier
            $secure = $this->is3dsEnabledFromParameters($parameters['params']);

            if (200 === $response->getStatusCode()) {
                $parameters = $response->json();

                return new Response($parameters, $secure);
            }
        }

        throw new CommunicationException(
            'The API request was not successful (Status: '.$response->getStatusCode().'): '.$response->getBody(true),
            0,
            $guzzleException
        );
    }

    private function getApiEndpoints()
    {
        return true === $this->isDebug ? $this->apiEndPoints['sandbox'] : $this->apiEndPoints['production'];
    }

    private function strip3dsParametersForUnsupportedOperation($operation, array &$parameters)
    {
        // 3DS is only supported on payment and authorization operations
        if (self::AUTHORIZATION_OPERATION === $operation || self::PAYMENT_OPERATION === $operation) {
            return;
        }

        if (isset($parameters[self::SECURE_3DS_PARAM])) {
            unset($parameters[self::SECURE_3DS_PARAM]);
        }

        if (isset($parameters[self::DISPLAY_MODE_3DS_PARAM])) {
            unset($parameters[self::DISPLAY_MODE_3DS_PARAM]);
        }
    }

    private function configure3dsParameters(array &$parameters)
    {
        if (!$this->is3dsEnabledFromParameters($parameters) || isset($parameters[self::DISPLAY_MODE_3DS_PARAM])) {
            return;
        }

        $parameters[self::DISPLAY_MODE_3DS_PARAM] = $this->default3dsDisplayMode;
    }

    /**
     * Checks if 3DS is enabled by inspecting the parameters.
     *
     * @param array $parameters
     *
     * @return boolean
     */
    private function is3dsEnabledFromParameters(array $parameters)
    {
        return isset($parameters[self::SECURE_3DS_PARAM]) && 'yes' === $parameters[self::SECURE_3DS_PARAM];
    }

    private function convertAmountToBe2billFormat($amount)
    {
        return round($amount * 100, 0, PHP_ROUND_HALF_DOWN);
    }
}
