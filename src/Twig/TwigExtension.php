<?php

namespace Survos\Providence\Twig;

use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function __construct(
        private TranslatorInterface $translator
    )
    {


    }

    public function getFilters(): array
    {
        // consider something like https://github.com/a-r-m-i-n/font-awesome-bundle
        return [
            new TwigFilter('optionalTrans', [$this, 'optionalTrans'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('link', [$this, 'link'], ['is_safe' => ['html']]),
            new TwigFunction('optionalTrans', [$this, 'optionalTrans']),
        ];
    }

    public function optionalTrans(?string $id, array $parameters = [], string $domain = null, string $locale = null): ?string
    {
//        $locale = $translator->getLocale();
        $catalogue = $this->translator->getCatalogue($locale);
//        dd($catalogue);
        return $catalogue->has($id, $domain) ? $this->translator->trans($id, $parameters, $domain, $locale): null;
    }

    public function link(string $value): string
    {
        // check security and do something if the link isn't valid
        return $value;
    }
}
