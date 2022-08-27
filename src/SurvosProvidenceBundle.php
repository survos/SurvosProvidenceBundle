<?php

namespace Survos\Providence;

use Survos\Providence\Command\ExportCommand;
use Survos\Providence\Services\ProfileService;
use Survos\Providence\Services\ProvidenceService;
use Survos\Providence\Twig\TwigExtension;
use Survos\Providence\XmlModel\XmlProfile;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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

        $builder
            ->setDefinition('survos.prov_twig', new Definition(TwigExtension::class))
            ->addTag('twig.extension')
//            ->setPublic(false)
            ->setArgument('$translator', new Reference('translator'))
        ;

        $builder
            ->autowire($id = 'survos.providence.xml_profile', XmlProfile::class)
            ->setPublic(true);
        $container->services()->alias(XmlProfile::class, $id);

        $providence_service_id = 'survos.providence_service';
        $builder
            ->autowire($providence_service_id, ProvidenceService::class)
            ->setArgument('$translator', new Reference('translator'))
            ->setArgument('$provPath', $config['prov_path'])
            ->setArgument('$bag', new Reference('parameter_bag'))
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
        $definition->setArgument('$persist', $config['persist']);
        $definition->setArgument('$confPath', $config['conf_path']);
        $definition->setArgument('$docPath', $config['doc_path']);
        $definition->setArgument('$fieldConfigPath', $config['field_config_path']);
//        $definition->setArgument('$height', $config['height']);
//        $definition->setArgument('$foregroundColor', $config['foregroundColor']);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        // since the configuration is short, we can add it here
        $definition->rootNode()
            ->children()
//            ->arrayNode('routes_to_skip')->defaultValue(['app_logout'])->end()
            ->scalarNode('conf_path')->defaultValue(__DIR__ . '/../config/providence-conf')->end()
            ->scalarNode('xml_dir')->defaultValue(__DIR__ . '/../config/xml')->end()
            ->scalarNode('doc_path')->defaultValue(__DIR__ . '/../config/')->end()
            ->scalarNode('prov_path')->defaultValue(__DIR__ . '/../providence/')->end()
            ->scalarNode('field_config_path')->defaultValue(__DIR__ . '/../config/ca_models.json')->end()

            // generated from    doc_path: '%kernel.project_dir%/../ac/config'
//file_put_contents($fn = dirname(__DIR__) . '/../survos/ca/ca-fix/config/ca_models.json', json_encode(BaseModel::$s_ca_models_definitions, JSON_PRETTY_PRINT)) && dd($fn);

            ->booleanNode('load_from_files')->defaultValue(true)->end()
            ->booleanNode('persist')->defaultValue(true)->end()
            ->end();
        ;
    }

}
