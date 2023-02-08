<?php

namespace Hl\ImageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * ImageBundle
 * v1.0.0
 */
class ImageBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        // return new LogReaderExtension();
    }
}
