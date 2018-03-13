<?php
namespace CodeDetector\Detector;

use CodeDetector\Exceptions\Storage\ConnectionException;
use CodeDetector\Exceptions\Storage\UndefinedException;
use Predis\Client;
use Predis\PredisException;

class RedisStorage implements StorageInterface
{
    private $host;
    private $port;
    private $database;

    private $driver;

    public function __construct($host, $port = 6379, $database = 0)
    {
        if (!class_exists('\Predis\Client')) {
            throw new UndefinedException();
        }

        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
    }

    private function driver()
    {
        if (is_null($this->driver)) {
            try {
                $this->driver = new Client("tcp://{$this->host}:{$this->port}");
                $this->driver->select($this->database);
            } catch (PredisException $e) {
                $exception = new ConnectionException();
                $exception->setDriverException($e);
                throw $exception;
            }
        }

        return $this->driver;
    }

    public function get($key)
    {
        try {
            return $this->driver()->get($key);
        } catch (PredisException $e) {
            $exception = new ConnectionException();
            $exception->setDriverException($e);
            throw $exception;
        }
    }

    public function set($key, $value)
    {
        try {
            $this->driver()->set($key, $value);
        } catch (PredisException $e) {
            $exception = new ConnectionException();
            $exception->setDriverException($e);
            throw $exception;
        }
    }
}
