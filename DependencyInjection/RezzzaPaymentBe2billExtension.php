<?php

namespace Rezzza\PaymentBe2billBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

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
class RezzzaPaymentBe2billExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor     = new Processor();
        $config        = $processor->processConfiguration($configuration, $configs);
        $xmlLoader     = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $xmlLoader->load('client.xml');
        $xmlLoader->load('gateway.xml');

        $container->setParameter('payment.be2bill.debug', $config['debug']);
        $container->setParameter('payment.be2bill.identifier', $config['identifier']);
        $container->setParameter('payment.be2bill.password', $config['password']);
        $container->setParameter('payment.be2bill.default_3ds_display_mode', $config['default_3ds_display_mode']);
    }
}
