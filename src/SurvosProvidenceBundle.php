<?php

namespace Survos\Providence;

use Survos\Providence\Command\ExportCommand;
use Survos\Providence\Services\ProvidenceService;
use Survos\Providence\Twig\TwigExtension;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SurvosProvidenceBundle extends AbstractBundle
{

    /** @param array<mixed> $config */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {

        $builder->autowire(ExportCommand::class)
//            ->setArgument('$registry', new Reference('doctrine'))
            ->setArgument('$logger', new Reference('logger'))
            ->addTag('console.command')
        ;

        $providence_service_id = 'survos.providence_service';
        $builder
            ->autowire($providence_service_id, ProvidenceService::class)
            ->setPublic(true)
            ->setArgument('$userClass', $config['user_class'])
            ->setArgument('$loginPath', $config['login_path'])
            ->setArgument('$submitButtonSelector', $config['submit_button'])
            ->setArgument('$plaintextPassword', $config['plaintext_password'])
            ->setArgument('$initialPath', $config['initial_path'])
            ->setArgument('$baseUrl', $config['base_url']);
            ;
        $container->services()->alias(ProvidenceService::class, $providence_service_id);



//        $definition->setArgument('$widthFactor', $config['widthFactor']);
//        $definition->setArgument('$height', $config['height']);
//        $definition->setArgument('$foregroundColor', $config['foregroundColor']);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        // since the configuration is short, we can add it here
        $definition->rootNode()
            ->children()
//            ->arrayNode('routes_to_skip')->defaultValue(['app_logout'])->end()
            ->scalarNode('max_per_route')->defaultValue(3)->end()
            ->scalarNode('base_url')->defaultValue('https://127.0.0.1:8000')->end()
            ->scalarNode('initial_path')->defaultValue('/')->end()
            ->scalarNode('user')->defaultValue('juan@tt.com')->end()
            ->scalarNode('login_path')->defaultValue('/login')->end()
            ->scalarNode('username_form_variable')->defaultValue('_username')->end()
            ->scalarNode('password_form_variable')->defaultValue('_password')->end()
            ->scalarNode('plaintext_password')->defaultValue('password')->end()
            ->scalarNode('submit_button')->defaultValue('.btn')->end()
            ->scalarNode('user_class')->defaultValue('Survos\Providence\\Entity\\User')->end()

            ->end();
        ;
    }

}