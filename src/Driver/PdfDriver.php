<?php

namespace Hl\ImageTools\Driver;

class PdfDriver extends AbstractImDriver
{
    public function __construct($filePath)
    {
        $this->setImage($filePath);
    }

    public function write($image)
    {
        // dd('convert ' . realpath($this->getImage()) . '[0]' . $this->getOperation() . ' ' . $image->getImageTargetFilePath());
        exec('convert ' . realpath($this->getImage()). '[0]' . $this->getOperation() . ' ' . $image->getImageTargetFilePath());
    }

    public static function getSizes($image): ?array
    {
        $output = shell_exec('identify ' . realpath($image));
        if ($output) {
            $splitinfo = explode(' ', $output);
            $dim = false;
            foreach ($splitinfo as $key => $val) {
                $temp = '';
                if ($val) {
                    $temp = explode('x', $val);
                }
                if ((int)$temp[0] && (int)$temp[1]) {
                    $dim = $temp;
                    break;
                }
            }
            if (!empty($dim[0]) && !empty($dim[1])) {
                return [$dim[0], $dim[1]];
            }
        }

        return null;
    }
}
