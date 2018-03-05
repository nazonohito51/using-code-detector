<?php
namespace CodeDetector\Exceptions\Storage;

class ConnectionException extends \RuntimeException
{
    private $driverException;

    public function __construct($message = null)
    {
        parent::__construct(!is_null($message) ? $message : 'An error occurred while connecting to storage');
    }

    public function setDriverException($e)
    {
        $this->driverException = $e;
    }
}
