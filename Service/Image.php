<?php

namespace Renus\MediaBundle\Service;

/**
 * Created by Renaud Mioque
 * Date: 15/04/15
 */

class Image
{
    /**
     * @var String file
     */
    private $file;

    /**
     * @var Integer width
     */
    private $width;

    /**
     * @var Integer height
     */
    private $height;

    /**
     * @var String type
     */
    private $type;

    /**
     * Initialize image information
     * @throws RuntimeException
     */
    public function init($file)
    {
        if(!file_exists($file) ) {
            throw \InvalidArgumentException("source image does not exists");
        }

        $this->file   = $file;

        if (! ($info = getimagesize($this->file ))) {
            throw new \RuntimeException("source image does not exists : " . $this->file);
        }

        $this->width  = $info[0];
        $this->height = $info[1];

        $mime       = $info['mime'];
        $this->type = str_replace('image/', '', $mime);

        $func = 'imagecreatefrom' . $this->type;
        if( !function_exists($func) ) {
            throw \RuntimeException("Format not supported");
        }
        $this->file = $func($this->file);
        return $this;
    }

    /**
     * @param string $dest
     * @param int $maxSize
     */
    public function createThumb($dest = NULL , $maxSize = 100)
    {
        if (empty($this->file)) {
            throw new \RuntimeException('no file init');
        }

        $format   = $this->getNewDimension($maxSize);
        $newImage = $this->createNewImage($format);

        $this->treatOpacity($this->file, $newImage);
        $this->resize($this->file, $newImage, $format);
        $this->save($newImage, $dest);
    }

    /**
     * @param $dst destination path
     * @param $srcX start x source image
     * @param $srcY start y source image
     * @param $width width of crop
     * @param $height height of crop
     */
    public function crop($dst, $srcX, $srcY, $width, $height)
    {
        $srcImageW = $this->width;
        $srcImageH = $this->height;

        $tmpImageW = $width;
        $tmpImageH = $height;

        $dstImageW = $width;
        $dstImageH = $height;


        if ($srcX <= -$tmpImageW || $srcX > $srcImageW) {
            $srcX = $srcW = $dstX = $dstW = 0;
        } else if ($srcX <= 0) {
            $dstX = -$srcX;
            $srcX = 0;
            $srcW = $dstW = min($srcImageW, $tmpImageW + $srcX);
        } else if ($srcX <= $srcImageW) {
            $dstX = 0;
            $srcW = $dstW = min($tmpImageW, $srcImageW - $srcX);
        }

        if ($srcW <= 0 || $srcY <= -$tmpImageH || $srcY > $srcImageH) {
            $srcY = $srcH = $dstY = $dstH = 0;
        } else if ($srcY <= 0) {
            $dstY = -$srcY;
            $srcY = 0;
            $srcH = $dstH = min($srcImageH, $tmpImageH + $srcY);
        } else if ($srcY <= $srcImageH) {
            $dstY = 0;
            $srcH = $dstH = min($tmpImageH, $srcImageH - $srcY);
        }

        $ratio = $tmpImageW / $dstImageW;
        $dstX /= $ratio;
        $dstY /= $ratio;
        $dstW /= $ratio;
        $dstH /= $ratio;

        $dstImage = $this->createNewImage([
            "width"  => $dstImageW,
            "height" => $dstImageH
        ]);

        $this->treatOpacity($this->file, $dstImage);
        imagecopyresampled($dstImage, $this->file, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
        $this->save($dstImage, $dst);
    }


    /**
     * @param $maxSize
     * @param $format
     * @return resource
     */
    private function createNewImage($format)
    {
        $newImage = imagecreatetruecolor($format['width'],$format['height']);
        return $newImage;
    }

    /**
     * @param $image
     * @param $dest
     */
    private function save($image, $dest)
    {
        $func = 'image'. $this->type;
        $func($image, $dest);
        imagedestroy($image);
    }

    /**
     * resize the image tto crate thumb
     * @param $source
     * @param $image
     * @param array $dimensions
     */
    private function resize($source, $image, Array $dimensions)
    {
        imagecopyresampled(
            $image, $source,
            0, 0, 0, 0,
            $dimensions['width'], $dimensions['height'],
            $this->width, $this->height
        );
    }

    /**
     * calculate the dimension of the image thumb
     *
     * @param $maxSize
     * @return array
     */
    private function getNewDimension($maxSize)
    {
        $ratio      = $this->height / $this->width;

        if ($ratio < 1) {
            $newWidth  = $maxSize;
            $newHeight = round( $maxSize * $ratio );
        } else {
            $newWidth  = round( $maxSize * ($this->width * $this->height));
            $newHeight = $maxSize;
        }

        return [
            'width'  => $newWidth,
            'height' => $newHeight
        ];
    }

    /**
     * @param $source
     * @param $image
     */
    private function treatOpacity($source, $image)
    {
        switch ($this->type) {

            case 'png':
                imagealphablending($image,false);
                imagesavealpha($image,true);
                break;

            case 'gif':
                $index = imagecolortransparent($source);
                $color = imagecolorsforindex($source, $index);
                $index = imagecolorallocate($image, $color['red'], $color['green'], $color['blue']);
                imagefill($image, 0, 0, $index);
                imagecolortransparent($image, $index);
                break;

            default:
                break;
        }
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }
}