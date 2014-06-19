<?php

namespace Rezzza\PaymentBe2billBundle\Tests\Units\DependencyInjection;

require_once __DIR__ . '/../../../vendor/autoload.php';

use mageekguy\atoum;
use Rezzza\PaymentBe2billBundle\DependencyInjection\RezzzaPaymentBe2billExtension as TestedExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This file is part of the RezzzaPaymentBe2billBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

/**
 * RezzzaPaymentBe2billExtension.
 *
 * @uses atoum\test
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class RezzzaPaymentBe2billExtension extends atoum\test
{
    public function testCreateClientDefaultDisplayMode()
    {
        $this
            ->if($container = new ContainerBuilder())
            ->and($extension = new TestedExtension())
            ->and($config = array('rezzza_payment_be2bill' =>
                array('identifier' => 'test', 'password' => 'test')
            ))
            ->and($extension->load($config, $container))
                ->string($container->getParameter('payment.be2bill.identifier'))
                    ->isEqualTo('test')
                ->string($container->getParameter('payment.be2bill.password'))
                    ->isEqualTo('test')
                ->string($container->getParameter('payment.be2bill.default_3ds_display_mode'))
                    ->isEqualTo('main')
        ;
    }

    public function testCreateCient()
    {
        $this
            ->if($container = new ContainerBuilder())
            ->and($extension = new TestedExtension())
            ->and($config = array('rezzza_payment_be2bill' =>
                array('identifier' => 'test', 'password' => 'test', 'default_3ds_display_mode' => 'popup')
            ))
            ->and($extension->load($config, $container))
                ->string($container->getParameter('payment.be2bill.identifier'))
                    ->isEqualTo('test')
                ->string($container->getParameter('payment.be2bill.password'))
                    ->isEqualTo('test')
                ->string($container->getParameter('payment.be2bill.default_3ds_display_mode'))
                    ->isEqualTo('popup')
        ;
    }

    public function testCreateCientInvalidDisplayMode()
    {
        $this
            ->if($container = new ContainerBuilder())
            ->and($config = array('rezzza_payment_be2bill' =>
                array('identifier' => 'test', 'password' => 'test', 'default_3ds_display_mode' => 'INVALID')
            ))
            ->and($extension = new TestedExtension())
                ->exception(function () use ($extension, $config, $container) {
                    $extension->load($config, $container);
                })
        ;
    }
}
