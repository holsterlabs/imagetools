<?php

namespace Hl\ImageTools;

use Symfony\Component\Mime\MimeTypes;
use Hl\ImageTools\Driver as Driver;

class Image
{
    protected $imageDate;
    protected $imageDebug = [];
    protected $imageBits;
    protected $imageChannels;
    protected $imageDimensions;
    protected $imageDirName;
    protected $imageFileExtension;
    protected $imageFileName;
    protected $imageHeight;
    protected $imageMimetype;
    protected $imageType;
    protected $imageTargetFileExtension;
    protected $imageWidth;
    private $baseImage;

    protected $editableTypes = [
        'png',
        'jpe',
        'jpeg',
        'jpg',
        'gif',
        'webp',
    ];

    protected $mimeTypes = [
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
    ];

    protected $fileExtensions = [
        'png' => 'png',
        'jpg' => 'jpg',
        'gif' => 'gif',
        'webp' => 'webp',
    ];

    protected $actions = [];

    const IMAGE_RATIO_SQUARE = '1';
    const IMAGE_RATIO_LANDSCAPE = '2';
    const IMAGE_RATIO_PORTRAIT = '3';
    const IMAGE_RATIO_UNDEFINED = '4';

    public function __construct($image = false)
    {
        $this->imageDate = date('d.m.Y H:i:s');
        $this->baseImage = $image;
        $data = $this->getImageData($image);

        if ($data === false) {
            $mimeTypes = new MimeTypes();
            $mimeType = $mimeTypes->guessMimeType($image);
            if ($mimeType === 'image/svg+xml') {
                $data = Driver\SvgDriver::getSizes($image);
                $data['3'] = $data['0'] . 'x' . $data['1'];
                $data['mime'] = 'image/svg+xml';
            }
            if ($mimeType === 'application/pdf') {
                $data = Driver\PdfDriver::getSizes($image);
                $data['3'] = $data['0'] . 'x' . $data['1'];
                $data['mime'] = 'application/pdf';
                $this->setImageTargetFileExtension('jpg');
            }
        }

        $this->setImageWidth($data['0']);
        $this->setImageHeight($data['1']);
        $this->setImageDimensions($data['3']);
        $this->setimageMimetype($data['mime']);
        if (isset($data['bits'])) {
            $this->setImageBits($data['bits']);
        }
        if (isset($data['channels'])) {
            $this->setImageChannels($data['channels']);
        }

        $pathInfo = pathinfo($image);
        $this->setImageFileName($pathInfo['filename']);
        $this->setImageDirName($pathInfo['dirname']);
        $this->setImageFileExtension($pathInfo['extension']);
        $this->setImageType(array_flip($this->mimeTypes)[$data['mime']]);
    }

    protected function getImageData($image)
    {
        if (function_exists('getimagesize')) {
            $data = @getimagesize($image);
        }

        if ($data === false) {
            return false;
        }

        return $data;
    }

    protected function setAction($action, $config)
    {
        $this->actions[] = [
            'type' => $action,
            'config' => $config
        ];
    }

    public function executeActions()
    {
        switch ($this->getimageType()) {
            case 'webp':
                $image = new Driver\WebPDriver($this->baseImage);
                break;
            case 'gif':
                $image = new Driver\GifDriver($this->baseImage);
                break;
            case 'pdf':
                $image = new Driver\PdfDriver($this->baseImage);
                break;
            case 'svg':
            case 'svgz':
                $image = new Driver\SvgDriver($this->baseImage);
                break;
            default:
                $image = new Driver\DefaultDriver($this->baseImage);
        }

        foreach ($this->actions as $action) {
            \call_user_func([$image, $action['type']], $action['config']);
        }

        $image->write($this);
    }

    public function crop($width, $height)
    {
        $this->setImageWidth($width);
        $this->setImageHeight($height);
        $this->setImageDimensions('width="' . $this->getImageWidth() . '" height="' . $this->getimageHeight() . '"');

        $this->setAction('crop', [$width, $height]);

        return $this;
    }

    public function resize($width, $height = null, $scaleUp = false): self
    {
        $newSize = $this->calculateImageSize($width, $height, $scaleUp);

        $this->setImageWidth($newSize['width']);
        $this->setImageHeight($newSize['height']);
        $this->setImageDimensions('width="' . $this->getImageWidth() . '" height="' . $this->getimageHeight() . '"');

        $this->setAction('resize', [$newSize['width'], $newSize['height']]);

        return $this;
    }

    public function updatePath($filePath)
    {
        if ($filePath) {
            $pathInfo = pathinfo($filePath);

            if (in_array($pathInfo['extension'], $this->getEditableTypes())) {
                $this->setImageType($pathInfo['extension']);
                $this->setImageFileExtension($pathInfo['extension']);
                $this->setImageMimetype($this->mimeTypes[$pathInfo['extension']]);
            }

            if (file_exists($pathInfo['dirname']) && is_dir($pathInfo['dirname'])) {
                $this->setImageDirName($pathInfo['dirname']);
            }

            if ($pathInfo['filename']) {
                $this->setImageFileName($pathInfo['filename']);
            }
        }
    }

    public function updateTargetPath($filePath)
    {
        if ($filePath) {
            $pathInfo = pathinfo($filePath);

            if (in_array($pathInfo['extension'], $this->getEditableTypes())) {
                // $this->setImageType($pathInfo['extension']);
                // $this->setImageFileExtension($pathInfo['extension']);
                // $this->setImageMimetype($this->mimeTypes[$pathInfo['extension']]);
            }

            if (file_exists($pathInfo['dirname']) && is_dir($pathInfo['dirname'])) {
                $this->setImageDirName($pathInfo['dirname']);
            }

            if ($pathInfo['filename']) {
                $this->setImageFileName($pathInfo['filename']);
            }
        }
    }

    /**
     * Getter/Setter
     */
    public function getEditableTypes()
    {
        return $this->editableTypes;
    }

    public function getimageBits()
    {
        return $this->imageBits;
    }

    protected function setimageBits($imageBits)
    {
        $this->imageBits = $imageBits;

        return $this;
    }

    public function getImageChannels()
    {
        return $this->imageChannels;
    }

    protected function setImageChannels($imageCannels)
    {
        $this->imageChannels = $imageCannels;

        return $this;
    }

    public function getImageDebug()
    {
        return $this->imageDebug;
    }

    public function setImageDebug($key, $message)
    {
        $this->imageDebug[$key] = $message;

        return $this;
    }

    public function getImageDimensions()
    {
        return $this->imageDimensions;
    }

    protected function setImageDimensions($imageDimensions)
    {
        $this->imageDimensions = $imageDimensions;

        return $this;
    }

    public function getimageDirName()
    {
        return $this->imageDirName;
    }

    protected function setimageDirName($imageDirName)
    {
        $this->imageDirName = $imageDirName;

        return $this;
    }

    public function getimageFileName()
    {
        return $this->imageFileName;
    }

    protected function setimageFileName($imageFileName = '')
    {
        if (!$imageFileName) {
            $imageFileName = substr(md5(uniqid(microtime())), 0, 10);
        }
        $this->imageFileName = $imageFileName;

        return $this;
    }

    public function getimageFileExtension()
    {
        return $this->imageFileExtension;
    }

    protected function setimageFileExtension($imageFileExtension = 'png')
    {
        $this->imageFileExtension = $imageFileExtension;

        return $this;
    }

    public function getImageFilePath()
    {
        return $this->imageDirName . '/' . $this->imageFileName . '.' . $this->imageFileExtension;
    }

    public function getimageHeight()
    {
        return $this->imageHeight;
    }

    protected function setimageHeight($imageHeight)
    {
        $this->imageHeight = (int) $imageHeight;

        return $this;
    }

    public function getimageMimetype()
    {
        return $this->imageMimetype;
    }

    protected function setimageMimetype($imageMimetype)
    {
        $this->imageMimetype = $imageMimetype;

        return $this;
    }

    public function getimageType()
    {
        return $this->imageType;
    }

    protected function setimageType($imageType)
    {
        $this->imageType = $imageType;

        return $this;
    }

    public function getImageTargetFileExtension()
    {
        return $this->imageTargetFileExtension ?? $this->imageFileExtension;
    }

    public function setImageTargetFileExtension($imageTargetFileExtension)
    {
        $this->imageTargetFileExtension = $imageTargetFileExtension;

        return $this;
    }

    public function getImageTargetFilePath()
    {
        return $this->imageDirName . '/' . $this->imageFileName . '.' . $this->getImageTargetFileExtension();
    }

    public function getimageWidth()
    {
        return $this->imageWidth;
    }

    protected function setimageWidth($imageWidth)
    {
        $this->imageWidth = (int) $imageWidth;

        return $this;
    }

    protected function calculateImageSize($RequestedWidth, $RequestedHeight = false, $ScaleUp = false)
    {
        if (!$RequestedWidth && !$RequestedHeight) {
            return array(
                'width' => $this->getImageWidth(),
                'height' => $this->getImageHeight()
            );
        }

        if ($RequestedWidth && $RequestedHeight) {
            $ScaleX = $this->getImageWidth() / $RequestedWidth;
            $ScaleY = $this->getImageHeight() / $RequestedHeight;
        } elseif ($RequestedWidth) {
            $ScaleX = $ScaleY = $this->getImageWidth() / $RequestedWidth;
            $RequestedHeight = $this->getImageHeight() / $ScaleX;
        } elseif ($RequestedHeight) {
            $ScaleY = $ScaleX = $this->getImageHeight() / $RequestedHeight;
            $RequestedWidth = $this->getImageWidth() / $ScaleY;
        }

        // exit if scaleup is needed but not suggested
        if (($this->getImageWidth() < $RequestedWidth || $this->getImageHeight() < $RequestedHeight) && !$ScaleUp) {
            return array(
                'width' => $this->getImageWidth(),
                'height' => $this->getImageHeight()
            );
        }

        $RatioOriginal = $this->GetRatio($this->getImageWidth(), $this->getImageHeight());
        $RatioExpected = $this->GetRatio($RequestedWidth, $RequestedHeight);

        // What Do we need to to
        if ($RatioOriginal == self::IMAGE_RATIO_SQUARE && $RatioExpected == self::IMAGE_RATIO_SQUARE) {
            // squaretosquare
            return array(
                'width' => $RequestedWidth,
                'height' => $RequestedHeight
            );
        } elseif ($RatioOriginal == self::IMAGE_RATIO_SQUARE && $RatioExpected == self::IMAGE_RATIO_LANDSCAPE) {
            // SquareToLandscape
            return array(
                'width' => $RequestedHeight,
                'height' => $RequestedHeight
            );
        } elseif ($RatioOriginal == self::IMAGE_RATIO_SQUARE && $RatioExpected == self::IMAGE_RATIO_PORTRAIT) {
            // SquareToPortrait
            return array(
                'width' => $RequestedWidth,
                'height' => $RequestedWidth
            );
        } elseif ($RatioOriginal == self::IMAGE_RATIO_LANDSCAPE && $RatioExpected == self::IMAGE_RATIO_SQUARE) {
            // LandscapeToSquare
            return array(
                'width' => $RequestedWidth,
                'height' => ($this->getImageHeight() / $ScaleX)
            );
        } elseif ($RatioOriginal == self::IMAGE_RATIO_LANDSCAPE && $RatioExpected == self::IMAGE_RATIO_LANDSCAPE) {
            // LandscapeToLandscape
            if ($ScaleX > $ScaleY) {
                return ['width' => $RequestedWidth, 'height' => ($this->getImageHeight() / $ScaleX)];
            } else {
                return ['width' => ($this->getImageWidth() / $ScaleY), 'height' => $RequestedHeight];
            }
        } elseif ($RatioOriginal == self::IMAGE_RATIO_LANDSCAPE && $RatioExpected == self::IMAGE_RATIO_PORTRAIT) {
            // LandscapeToPortrait
            return array(
                'width' => $RequestedWidth,
                'height' => ($this->getImageHeight() / $ScaleX)
            );
        } elseif ($RatioOriginal == self::IMAGE_RATIO_PORTRAIT && $RatioExpected == self::IMAGE_RATIO_SQUARE) {
            // PortraitToSquare
            return array(
                'width' => ($this->getImageWidth() / $ScaleY),
                'height' => $RequestedHeight
            );
        } elseif ($RatioOriginal == self::IMAGE_RATIO_PORTRAIT && $RatioExpected == self::IMAGE_RATIO_LANDSCAPE) {
            // PortraitToLandscape
            return array(
                'width' => ($this->getImageWidth() / $ScaleY),
                'height' => $RequestedHeight
            );
        } elseif ($RatioOriginal == self::IMAGE_RATIO_PORTRAIT && $RatioExpected == self::IMAGE_RATIO_PORTRAIT) {
            // PortraitToPortrait
            if ($ScaleX < $ScaleY) {
                return ['width' => ($this->getImageWidth() / $ScaleY), 'height' => $RequestedHeight];
            } else {
                return ['width' => $RequestedWidth, 'height' => ($this->getImageHeight() / $ScaleX)];
            }
        }
    }

    public function getRatio($Width, $Height)
    {
        if ($Width == $Height) {
            $Ratio = self::IMAGE_RATIO_SQUARE;
        } elseif ($Width > $Height) {
            $Ratio = self::IMAGE_RATIO_LANDSCAPE;
        } elseif ($Width < $Height) {
            $Ratio = self::IMAGE_RATIO_PORTRAIT;
        } else {
            $Ratio = self::IMAGE_RATIO_UNDEFINED;
        }

        return $Ratio;
    }

    public function getImageDate()
    {
        return $this->imageDate;
    }
}
