<?php

namespace Hl\ImageBundle\Twig;

use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Hl\ImageBundle\Image;

class TwigImageExtension extends AbstractExtension
{
    private $cache;
    const REGISTERDARGUMENTS = [
        'crop',
        'fileExtension',
        'width',
        'height',
        'minWidth',
        'minHeight',
        'maxWidth',
        'maxHeight',
    ];

    public function __construct(CacheInterface $cache, Filesystem $filesystem, KernelInterface $appKernel)
    {
        $this->cache = $cache;
        $filesystem = new Filesystem();
        $filesystem->mkdir($appKernel->getProjectDir() . '/public/processed/img/', 0700);
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('img_resize', [$this, 'resize']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('image', [$this, 'image']),
        ];
    }

    public function image($src, $config = []): Image
    {
        $src = './' . $src;
        $image = new Image($src);

        $image->setImageDebug('crdate', date('y-m-d H:i:s'));

        $path = 'processed/' .
            $image->getimageFileName() .
            (isset($config['width']) ? '.w' . substr(preg_replace('/[^0-9]/', '', $config['width']), 0, 4) : '') .
            (isset($config['height']) ? '.h' . substr(preg_replace('/[^0-9]/', '', $config['height']), 0, 4) : '') .
            (isset($config['crop']) ? '.c' . substr(preg_replace('/[^0-9]/', '', $config['crop']), 0, 4) : '');

        $fullPath = $path . '.' . $image->getimageFileExtension();

        $imgCache = $this->cache->getItem(md5($fullPath));
        $imgCache->expiresAfter(\DateInterval::createFromDateString('1 year'));

        // if (!file_exists($fullPath) || filemtime($src) > filemtime($fullPath)) {
        if (!$imgCache->isHit() || !file_exists($fullPath) || filemtime($src) > filemtime($fullPath)) {
            $image->updatePath($fullPath);

            if (isset($config['crop'])) {
                if ($image->getImageHeight() * $config['crop'] > $image->getImageWidth()) {
                    $height = $image->getImageWidth() / $config['crop'];
                    $width = $image->getImageWidth();
                } else {
                    $width = $image->getImageHeight() * $config['crop'];
                    $height = $image->getImageHeight();
                }

                $image->crop($width, $height);
            }

            if (isset($config['width']) && isset($config['height'])) {
                $image->resize($config['width'], $config['height']);
            } elseif (isset($config['width'])) {
                $image->resize($config['width']);
            } elseif (isset($config['height'])) {
                $image->resize(null, $config['height']);
            }

            $image->executeActions();

            $this->cache->save($imgCache->set($image));
        } else {
            // $image = new Image($fullPath);
            $image = $imgCache->get();
        }

        return $image;
    }

    public function resize($imageFile, $width)
    {
        trigger_deprecation('app', '1.0.0', 'Using "%s" is deprecated, use "%s" instead.', '|img_resize', 'image()');

        return $this->image($imageFile, ['width' => $width]);
    }
}
