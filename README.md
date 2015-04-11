# RenusMediaBundle
RenusMediaBundle is a collection of code within a Symfony Bundle to  handle image and video

To use MopaBootstrapBundle and twitters Bootstrap 3 in your project add it via composer


# Installation

## prerequisites
To use RenusMediaBundle you must install and know the path of 'ffmpeg' binary (on Debian):


    apt-get install ffmpeg && ffmpeg-php5
    
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

Register the bundle:

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

$service = $this->get('renus.video')->getPicsFromVideo(
            '/path/to/video/vid1.m4v', 55, '/path/to/generate/image/vid1.jpg'
        );
```   