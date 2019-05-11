<?php
namespace common\helpers;

class Identicon
{
    private $instance = null;
    
    public function __construct()
    {
        $this->instance = new \Identicon\Identicon();
    }

    public function createUserImg($uid)
    {
        return $this->createImg("identicon_user_{$uid}.png", 512);
    }
    
    public function createImg($filename, $size = 64, $hexaColor = null)
    {
        $data = $this->getImageUrl($filename, $size, $hexaColor);
        $url = $this->getObj('TfsClientProxy')->uploadFileContent($data, $filename);
        return $url;
    }
    
    public function getImageUrl($string, $size = 64, $hexaColor = null)
    {
        return $this->instance->getImageDataUri($string, $size, $hexaColor);
    }


}