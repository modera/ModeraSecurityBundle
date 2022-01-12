<?php

namespace Modera\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Modera\SecurityBundle\PasswordStrength\PasswordConfigInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('modera_security');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('root_user_handler')
                    ->cannotBeEmpty()
                    ->defaultValue('modera_security.root_user_handler.semantic_config_root_user_handler')
                ->end()
                ->arrayNode('root_user')
                    ->addDefaultsIfNotSet()
                    ->children()
                        // these configuration properties are only used when
                        // 'modera_security.root_user_handler.semantic_config_root_user_handler' service is used
                        // as 'root_user_handler'
                        ->variableNode('query')
                            ->defaultValue(array('id' => 1))
                            ->cannotBeEmpty()
                        ->end()
                        ->variableNode('roles') // * - means all privileges
                            // it can also be array with roles names
                            ->defaultValue('*')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->variableNode('switch_user')
                    ->defaultValue(false)
                ->end()
                ->arrayNode('firewalls')
                    ->defaultValue(array())
                    ->prototype('array')
                        ->prototype('variable')->end()
                    ->end()
                ->end()
                ->arrayNode('access_control')
                    ->defaultValue(array())
                    ->prototype('array')
                        ->prototype('variable')->end()
                    ->end()
                ->end()
                ->arrayNode('password_strength') // since 2.56.0
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('mail')
                            ->addDefaultsIfNotSet()
                            ->children()
                                // Must contain service container ID of an \Modera\SecurityBundle\PasswordStrength\Mail\MailServiceInterface
                                // implementation.
                                ->scalarNode('service')
                                    ->cannotBeEmpty()
                                    ->defaultValue('modera_security.password_strength.mail.default_mail_service')
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('enabled') // in 3.0 this flag is going to be removed and feature will be enabled by default
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('min_length')
                            ->defaultValue(6)
                        ->end()
                        ->scalarNode('number_required')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('letter_required')
                            ->beforeNormalization()
                                ->always(function ($v) {
                                    $default = PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL;
                                    if (is_bool($v) && $v) {
                                        return $default;
                                    } else if (is_string($v)) {
                                        if (!in_array($v, PasswordConfigInterface::LETTER_REQUIRED_TYPES)) {
                                            return $default;
                                        }

                                        return $v;
                                    }

                                    return false;
                                })
                            ->end()
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('rotation_period')
                            ->info('If a password has been changed in last X days then it will not be possible to reuse it again the next X days')
                            ->defaultValue(90)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sorting_position')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('categories')
                            ->defaultValue(array())
                            ->beforeNormalization()
                                ->ifArray()
                                ->then(function ($v) {
                                    if (array_keys($v) !== range(0, count($v) - 1)) {
                                        return $v;
                                    }
                                    $arr = array_flip(array_reverse($v));
                                    array_walk($arr, function (&$position) {
                                        $position++;
                                    });
                                    return $arr;
                                })
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('perrmissions')
                            ->defaultValue(array())
                            ->beforeNormalization()
                                ->ifArray()
                                ->then(function ($v) {
                                    if (array_keys($v) !== range(0, count($v) - 1)) {
                                        return $v;
                                    }
                                    $arr = array_flip(array_reverse($v));
                                    array_walk($arr, function (&$position) {
                                        $position++;
                                    });
                                    return $arr;
                                })
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
