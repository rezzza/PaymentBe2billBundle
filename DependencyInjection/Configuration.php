<?php

namespace Rezzza\PaymentBe2billBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Jérémy Romey <jeremy@free-agent.fr>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        return $treeBuilder
            ->root('rezzza_payment_be2bill','array')
                ->children()
                    ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                    ->scalarNode('identifier')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                ->end()
            ->end()
        ;
    }
}
