<?php
namespace CodeDetector\Detector;

use CodeDetector\Exceptions\Storage\ConnectionException;
use CodeDetector\Exceptions\Storage\UndefinedException;
use Predis\Client;
use Predis\PredisException;

class RedisStorage implements StorageInterface
{
    private $driver;

    public function __construct($host, $port = 6379, $database = 0)
    {
        if (!class_exists('\Redis\Client')) {
            throw new UndefinedException();
        }

        try {
            $this->driver = new Client(array(
                'host' => $host,
                'port' => $port,
                'database' => $database
            ));
        } catch (PredisException $e) {
            $exp = new ConnectionException();
            $exp->setDriverException($e);
            throw $exp;
        }
    }

    public function get($key)
    {
        $this->driver->get($key);
    }

    public function set($key, $value)
    {

    }
}
