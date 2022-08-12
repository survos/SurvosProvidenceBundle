<?php

namespace Survos\Providence\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Catalogue\OperationInterface;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\String\u;

class ProvidenceService
{

    public function __construct(
        private TranslatorInterface $translator,
        private ParameterBagInterface $bag,
        private string $provPath,
    )
    {

    }

    public function convertLocales()
    {
        $projectDir = $this->bag->get('kernel.project_dir');

        $finder = new Finder();
        foreach ($finder->in($this->provPath . '/app/locale')->name('*.po')->files() as $splFileInfo) {
            $locale = $splFileInfo->getRelativePath();
            $dest = $projectDir . '/translations/ca.' . $locale . '.' . $splFileInfo->getExtension();
            copy($splFileInfo->getRealPath(), $dest);
        }
        //        dd('stopped');
        // change the .po to .yaml.  Then use the +icu message style to overwrite those messages.
        foreach (['es' => ['es_ES', 'es_MX'], 'en' => ['en_US', 'en_GB', 'en_CA']] as $lang => $locales) {
            foreach ($locales as $locale) {
                /** @var MessageCatalogueInterface|OperationInterface $catalogue */
                $catalogue = $this->translator->getCatalogue($locale);
                $messages = $catalogue->all();
                if (!array_key_exists('ca', $messages)) {
                    continue;
                }
//                dd($messages);
                ksort($messages['ca']);
                $newTrans = [];
                foreach ($messages['ca'] as $key => $trans) {
                    // @todo: better wordcount
//                    $wc = str_word_count($key, )
                    $wc = count(explode(' ', (string)$key));
                    if ($wc < 3) {
                        $key = str_replace(' ', '_', (string)$key);
                        $newTrans[$key] = $trans;
                    }
                }
                $newFilename = sprintf('%s/translations/%s.%s.yaml', $projectDir, 'ca', $locale);
                file_put_contents($newFilename, Yaml::dump($newTrans));
//                    dd($newTrans, $newFilename);
//                dd($messages, $translator->getCatalogue('es')->all());
            }
        }
    }

}
