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
            throw \RuntimeException("source image does not exists");
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
}