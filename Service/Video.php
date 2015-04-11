<?php

namespace Renus\MediaBundle\Service;

/**
 * Created by Renaud Mioque
 * Date: 10/04/15
 */

class Video
{
    /**
     * binary command
     */
    private $binary;

    public function __construct($binary)
    {
        $this->binary = $binary;
    }

    /**
     * generate one picture from the video
     *
     * @param Integer $second
     * @return string
     */
    public function getPicsFromVideo($video, $second, $file)
    {
        $video = escapeshellarg($video);
        $file  = escapeshellarg($file);

        $cmd = sprintf("%s -i %s -ss %d -t 00:00:01 -r 1 %s 2>&1",
            $this->binary, $video, $second, $file);
        $response = shell_exec($cmd);

        return $response;
    }

    /**
     * Crate an animated gif from a video
     *
     * @param String $video (video source)
     * @param Integer $interval (get frame interval)
     * @param String $outfile (file destination)
     */
    public function generateAnimatedGifFromVideo($video, $interval, $outfile)
    {
        $video  = escapeshellarg($video);
        $frames = [];

        for ($i = 1; $i < $this->getDuration($video); $i += $interval) {
            $frames[] = $this->getFrame($i, $video);
        }

        $gif = $this->createAnimation($frames);
        file_put_contents($outfile, $gif);
    }

    /**
     * Get a video frame from a certain point in time
     *
     * @param integer $second seconds from start
     * @return string binary image contents
     */
    private function getFrame($second, $video)
    {
        $out = '/tmp/' . uniqid();
        $cmd = sprintf("%s -i %s -ss %s -t 00:00:01 -r 1  -f mjpeg %s 2>&1",
            $this->binary, $video, $second, $out);

        shell_exec($cmd);
        $frame = file_get_contents($out);

        return $frame;
    }

    /**
     * Generate the animated gif
     *
     * @return string binary image data
     */
    private function createAnimation($images)
    {
        $animation = new \Imagick();
        $animation->setFormat('gif');

        foreach ($images as $image) {
            $frame = new \Imagick();
            $frame->readImageBlob($image);

            $animation->addImage($frame);
            $animation->setImageDelay(50);
        }

        return $animation->getImagesBlob();
    }

    /**
     * Get the video duration
     *
     * @return integer
     */
    private function getDuration($video)
    {
        $cmd     = sprintf("%s -i %s 2>&1", $this->binary, $video);
        $response= shell_exec($cmd);

        if (preg_match('/Duration: ((\d+):(\d+):(\d+))/s', $response, $time)) {
            return ($time[2] * 3600) + ($time[3] * 60) + $time[4];
        }
        return 0;
    }
}