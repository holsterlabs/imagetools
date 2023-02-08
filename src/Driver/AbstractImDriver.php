<?php

namespace Hl\ImageBundle\Driver;

abstract class AbstractImDriver
{
    private $image;
    private $resize;
    private $crop;

    public function resize($config)
    {
        if (isset($config['0']) && isset($config['1'])) {
            $this->resize = $config['0'] . 'x' . $config['1'];
        } elseif (isset($config['0'])) {
            $this->resize = $config['0'];
        } elseif (isset($config['1'])) {
            $this->resize = 'x' . $config['1'];
        }
    }

    public function crop($config)
    {
        $this->crop = $config['0'] . 'x' . $config['1'] . '+0+0';
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

    public function getOperation()
    {
        return ($this->crop ? ' -gravity center -crop "' . $this->crop . '"' : '') . ($this->resize ? ' -resize ' . $this->resize : '');
    }

    public function getResize()
    {
        return $this->resize;
    }

    public function getCrop()
    {
        return $this->crop;
    }
}
