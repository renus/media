# RenusMediaBundle
RenusMediaBundle is a collection of code within a Symfony 2 Bundle to  handle image and video, you can create an 
image or animated gif from a video source or resize, obfuscate and crop an image in Symfony 2.

To use RenusMediaBundle in your project add it via composer


# Installation

## prerequisites
To use RenusMediaBundle you must install and know the path of 'ffmpeg' binary (on Debian):


    apt-get install ffmpeg php5-ffmpeg php5-imagick 
    
## configuration 
if you use a non standard Debian installation, you must specify the path to ffmpeg in your parameters file :

    parameters:
      binary: '/usr/bin/ffmpeg'
    
## installation
    
1. Add this bundle to your project in composer.json:
    
<pre>
{
    "require": {
        "renus/media": "0.*",
    }
}
</pre>

2. Install with composer

<pre>
composer.phar require renus/media dev-master
</pre>

3. Register the bundle


```php
<?php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new \Renus\MediaBundle\RenusMediaBundle(),
    );
    // ...
}
```

# Usage
In Symfony 2 controller, you can use it like a service 
   
## extract an image from video
extract the image at 55 seconds

```php
<?php

$this->container->get('renus.video')->getPicsFromVideo(
    '/path/to/video/vid1.m4v', 55, '/path/to/generate/image/vid1.jpg'
);
```   

## create an animated gif from video
get an image every 10 seconds to build the animated gif

```php
<?php

$this->container->get('renus.video')->generateAnimatedGifFromVideo(
    '/path/to/video/vid1.m4v', 10, '/path/to/generate/image/vid1.gif'
);
```

## resize an image
Choose the destination path and the max size (300px here)

```php
<?php

$this->container->get('renus.image')
                ->init('/path/to/image.jpg')
                ->createThumb('/path/to/resize-thumb.jpg', 300);
```

## crop an image
Choose the destination path , define the start X point and the start Y point, the width
and the height of the selection.

```php
<?php

$this->container->get('renus.image')
                ->init('/path/to/image.jpg')
                ->crop('/path/to/crop-thumb.jpg', 100, 25, 300, 250);
```