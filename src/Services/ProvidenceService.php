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

        // first, move them to the local directory, so they can then be dumped.
        foreach ($finder->in($this->provPath . '/app/locale')->name('*.po')->files() as $splFileInfo) {
            $locale = $splFileInfo->getRelativePath();
            $dest = $projectDir . '/translations/ca.' . $locale . '.' . $splFileInfo->getExtension();
//            copy($splFileInfo->getRealPath(), $dest);
        }
//                dd('stopped');
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

    public function getPOExample()
    {
        // Goal: {{ 'elements.defined'|trans({n: n}, 'ca') }}

        // CA CODE:
//        if ($vn_element_count == 1) {
//            print "<div>"._t("1 element is defined")."</div>";
//        } else {
//            print "<div>"._t("%1 elements are defined", $vn_element_count)."</div>";
//        }

        return <<< END
#: ../../themes/default/views/manage/widget_comments_info_html.php:33
msgid "User comments"
msgstr "Comentarios de usuarios"

#: ../../themes/default/views/manage/widget_comments_info_html.php:36
msgid "1 comment needs moderation"
msgstr "1 comentario ha de ser moderado"

#: ../../themes/default/views/manage/widget_comments_info_html.php:38
msgid "%1 comments need moderation"
msgstr "%1 comentario ha de ser moderado"

#: ../../themes/default/views/manage/widget_comments_info_html.php:43
msgid "1 moderated comment"
msgstr "1 comentario moderado"

#: ../../themes/default/views/manage/widget_comments_info_html.php:45
msgid "%1 moderated comments"
msgstr "%1 comentarios moderados"

#: ../../themes/default/views/manage/widget_comments_info_html.php:50
msgid "1 comment total"
msgstr "1 comentario en total"

#: ../../themes/default/views/manage/widget_comments_info_html.php:52
msgid "%1 comments total"
msgstr "%1 comentarios en total"
END;

    }
}
