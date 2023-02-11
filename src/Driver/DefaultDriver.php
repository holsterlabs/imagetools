<?php

namespace Hl\ImageTools\Driver;

class DefaultDriver extends AbstractImDriver
{
    public function __construct($filePath)
    {
        $this->setImage($filePath);
    }

    public function write($image)
    {
        exec('convert ' . realpath($this->getImage()) . $this->getOperation() . ' ' . $image->getImageFilePath());
    }
}
