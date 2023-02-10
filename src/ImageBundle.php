<?php

namespace Hl\ImageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Hl\ImageBundle\DependencyInjection\ImageExtension;

/**
 * ImageBundle
 * v1.0.5
 */
class ImageBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new ImageExtension();
    }
}
