<?php

namespace Survos\Providence;

use Survos\Providence\Command\ExportCommand;
use Survos\Providence\Services\ProfileService;
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
            ;
        $container->services()->alias(ProvidenceService::class, $providence_service_id);

        $profile_service_id = 'survos.profile_service';
        $container->services()->alias(ProfileService::class, $profile_service_id);
        $definition = $builder
            ->autowire($profile_service_id, ProfileService::class)
            ->setPublic(true)
        ;
        $definition->setArgument('$xmlDir', $config['xml_dir']);
        $definition->setArgument('$loadFromFiles', $config['load_from_files']);
//        $definition->setArgument('$height', $config['height']);
//        $definition->setArgument('$foregroundColor', $config['foregroundColor']);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        // since the configuration is short, we can add it here
        $definition->rootNode()
            ->children()
//            ->arrayNode('routes_to_skip')->defaultValue(['app_logout'])->end()
            ->scalarNode('xml_dir')->defaultValue("vendor/collectiveaccess/install/xml")->end()
            ->booleanNode('load_from_files')->defaultValue(true)->end()
            ->end();
        ;
    }

}
