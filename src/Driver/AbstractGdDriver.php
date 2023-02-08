<?php

namespace Hl\ImageBundle\Driver;

abstract class AbstractGdDriver
{
    private $image;

    public function resize($config)
    {
        $image = imagecreatetruecolor($config[0], $config[1]);
        imagecopyresampled(
            $image,
            $this->image,
            0,
            0,
            0,
            0,
            $config[0],
            $config[1],
            imagesx($this->image),
            imagesy($this->image)
        );

        $this->image = $image;
    }

    public function crop($config)
    {
        $image = imagecreatetruecolor($config[0], $config[1]);

        imagecopyresampled(
            $image,
            $this->image,
            0,
            0,
            ($config[0] - imagesx($this->image)) / -2,
            ($config[1] - imagesy($this->image)) / -2,
            $config[0],
            $config[1],
            $config[0],
            $config[1]

        );

        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }
}
