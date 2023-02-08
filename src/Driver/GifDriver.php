<?php

namespace Hl\ImageBundle\Driver;

class GifDriver extends AbstractImDriver
{
    public function __construct($filePath)
    {
        $this->setImage($filePath);
    }

    public function write($image)
    {
        // dd($image);
        $tmp = $image->getimageDirName() . '/' . $image->getimageFileName()  . '.tmp.' . $image->getimageFileExtension();
        exec('convert ' . realpath($this->getImage()) . ' -coalesce ' . $tmp);
        exec('convert ' . $tmp . $this->getOperation() . ($this->getCrop() ? ' +repage' : '') . ' ' . $image->getImageFilePath());
        if (file_exists($tmp)) {
            unlink($tmp);
        }
    }
}
