<?php

namespace Hl\ImageTools\Driver;

class SvgDriver // extends AbstractImDriver
{
    private $image;
    private $width = null;
    private $height = null;
    private $crop = null;
    private $domXml;

    public function __construct($filePath)
    {
        $this->setImage($filePath);
    }

    public function write($image)
    {
        if (file_exists($this->getImage())) {
            $originalSizes = self::getSizes(realpath($this->getImage()));

            $svgContent = file_get_contents(realpath($this->getImage()));
            $svgContent = preg_replace('/<script[\s\S]*?>[\s\S]*?<\/script>/i', '', $svgContent);
            $svgElement = simplexml_load_string($svgContent);
            $domXml = dom_import_simplexml($svgElement);

            if (null !== $this->width) {
                $domXml->setAttribute('width', $this->width);
            }
            if (null !== $this->height) {
                $domXml->setAttribute('height', $this->height);
            }

            if (null !== $this->crop) {
                $x = ($originalSizes[0] - $this->crop[0]) / 2;
                $y = ($originalSizes[1] - $this->crop[1]) / 2;
                $domXml->setAttribute('viewBox', implode(' ', [$x, $y, $this->crop[0], $this->crop[1]]));
            }


            $content = $domXml->ownerDocument->saveXML($domXml->ownerDocument->documentElement);
            file_put_contents($image->getImageFilePath(), $content);
        }
        // copy(realpath($this->getImage()), $image->getImageFilePath());
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

    public function resize($config)
    {
        if (isset($config['0'])) {
            $this->width = $config['0'];
        }
        if (isset($config['1'])) {
            $this->height = $config['1'];
        }
    }

    public function crop($config)
    {
        $this->crop = $config;

        // $image = imagecreatetruecolor($config[0], $config[1]);

        // imagecopyresampled(
        //     $image,
        //     $this->image,
        //     0,
        //     0,
        //     ($config[0] - imagesx($this->image)) / -2,
        //     ($config[1] - imagesy($this->image)) / -2,
        //     $config[0],
        //     $config[1],
        //     $config[0],
        //     $config[1]

        // );

        // $this->image = $image;
    }

    public static function getSizes($image)
    {
        $imagesSizes = [];

        $fileContent = file_get_contents($image);

        $xml = simplexml_load_string($fileContent, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);

        if ($xml === false) {
            return false;
        }

        $xmlAttributes = $xml->attributes();

        if (!empty($xmlAttributes['width']) && !empty($xmlAttributes['height'])) {
            $imagesSizes = [(int)$xmlAttributes['width'], (int)$xmlAttributes['height']];
        } elseif (!empty($xmlAttributes['viewBox'])) {
            $viewBox = explode(' ', $xmlAttributes['viewBox']);
            $imagesSizes = [(int)$viewBox[2], (int)$viewBox[3]];
        }

        return $imagesSizes !== [] ? $imagesSizes : false;
    }
}
