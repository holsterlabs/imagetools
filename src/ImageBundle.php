<?php

namespace Hl\ImageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Hl\ImageBundle\DependencyInjection\TwigExtension;

/**
 * ImageBundle
 * v0.0.1
 */
class ImageBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new TwigExtension();
    }
}
