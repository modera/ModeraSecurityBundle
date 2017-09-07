<?php

namespace Modera\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('modera_security');

        $rootNode
            ->children()
                ->scalarNode('root_user_handler')
                    ->cannotBeEmpty()
                    ->defaultValue('modera_security.root_user_handler.semantic_config_root_user_handler')
                ->end()
                ->arrayNode('root_user')
                    ->addDefaultsIfNotSet()
                    ->cannotBeEmpty()
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
                ->arrayNode('access_control')
                    ->defaultValue(array())
                    ->prototype('array')
                        ->prototype('variable')->end()
                    ->end()
                ->end()
                ->arrayNode('password_strength') // since 2.56.0
                    ->addDefaultsIfNotSet()
                    ->cannotBeEmpty()
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
                                ->scalarNode('sender')
                                    ->defaultValue('no-reply@no-reply')
                                    ->cannotBeEmpty()
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
                            // capital_or_non_capital, capital_and_non_capital, capital, non_capital
                            ->beforeNormalization()
                                ->ifTrue(function ($v) { return is_bool($v); })
                                ->then(function ($v) {
                                    if (is_bool($v) && $v) {
                                        return 'capital_or_non_capital';
                                    }
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
            ->end()
        ;

        return $treeBuilder;
    }
}
