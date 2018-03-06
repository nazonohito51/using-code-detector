<?php
namespace CodeDetector\Detector;

use CodeDetector\Exceptions\Storage\ConnectionException;

interface StorageInterface
{
    /**
     * @param string $key
     * @return mixed
     * @throws ConnectionException
     */
    public function get($key);

    /**
     * @param string $key
     * @param mixed $value
     * @throws ConnectionException
     */
    public function set($key, $value);
}
