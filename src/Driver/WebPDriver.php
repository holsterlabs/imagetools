<?php

namespace Hl\ImageBundle\Driver;

class WebPDriver extends AbstractGdDriver
{
    public function __construct($filePath)
    {
        $this->setImage(imagecreatefromwebp($filePath));
    }

    public function write($image)
    {
        if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false) {
            imagewebp($this->getImage(), $image->getImageFilePath());
        } else {
            $jpgPath = $image->getimageDirName() . '/' . $image->getimageFileName() . '.jpg';
            $image->updatePath($jpgPath);
            imagejpeg($this->getImage(), $jpgPath);
        }
    }
}
