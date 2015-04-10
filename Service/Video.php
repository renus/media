<?php

namespace Renus\MediaBundle\Service;

/**
 * Created by Renaud Mioque
 * Date: 10/04/15
 */

class Video
{
    /**
     * binnary command
     */
    private $binary;

    public function __construct($binary)
    {
        $this->binary = $binary;
    }

    /**
     * @param Integer $second
     * @return string
     */
    public function getPicsFromVideo($video, $second, $file)
    {
        $video = escapeshellarg($video);
        $file  = escapeshellarg($file);

        $cmd = sprintf("%s -i %s -ss %d -t 00:00:01 -r 1 %s 2>&1",
                        $this->binary, $video, $second, $file);
        exec($cmd);

        return $file;
    }
}