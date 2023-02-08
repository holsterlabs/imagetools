<?php

namespace Hl\ImageBundle;

use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

final class Extension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('img_resize2', [TwigImageExtension::class, 'resize']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('image2', [TwigImageExtension::class, 'image']),
        ];
    }
}
