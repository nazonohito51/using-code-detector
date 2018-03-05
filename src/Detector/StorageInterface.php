<?php
namespace CodeDetector\Detector;

interface StorageInterface
{
    public function get($key);
    public function set($key, $value);
}
