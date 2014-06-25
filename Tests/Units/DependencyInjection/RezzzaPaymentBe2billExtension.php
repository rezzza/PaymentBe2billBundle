<?php

namespace Rezzza\PaymentBe2billBundle\Tests\Units\DependencyInjection;

use mageekguy\atoum;
use Rezzza\PaymentBe2billBundle\DependencyInjection\RezzzaPaymentBe2billExtension as TestedExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
            ->if(
                $container = new ContainerBuilder(),
                $extension = new TestedExtension(),
                $config = array('rezzza_payment_be2bill' =>
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
            ->if(
                $container = new ContainerBuilder(),
                $extension = new TestedExtension(),
                $config = array('rezzza_payment_be2bill' =>
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
            ->if(
                $container = new ContainerBuilder(),
                $config = array('rezzza_payment_be2bill' =>
                array('identifier' => 'test', 'password' => 'test', 'default_3ds_display_mode' => 'INVALID')
            ))
            ->and($extension = new TestedExtension())
                ->exception(function () use ($extension, $config, $container) {
                    $extension->load($config, $container);
                })
        ;
    }

    public function testCallback3dsHandler()
    {
        $this
            ->if(
                $container = new ContainerBuilder(),
                $extension = new TestedExtension(),
                $config = array('rezzza_payment_be2bill' =>
                    array('identifier' => 'test', 'password' => 'test')
                ),
                $extension->load($config, $container),
                $definition = $container->getDefinition('payment.be2bill.callback.3ds_controller'),
                $class = $container->getParameter('payment.be2bill.callback.3ds_controller.class')
            )
            ->string($class)
                ->isEqualTo('Rezzza\PaymentBe2billBundle\Callback\Controller\EntityCallback3dsController')
            ->string($definition->getClass())
                ->isEqualTo('%payment.be2bill.callback.3ds_controller.class%')
            ->object($definition->getArgument(0))
                ->isEqualTo(new Reference('doctrine.orm.entity_manager'))
            ->string($definition->getArgument(1))
                ->isEqualTo('%payment.plugin_controller.entity.options.financial_transaction_class%')
        ;
    }
}
