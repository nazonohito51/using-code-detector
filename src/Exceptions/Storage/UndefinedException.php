<?php
namespace CodeDetector\Exceptions\Storage;

use CodeDetector\Exceptions\CodeDetectorException;

class UndefinedException extends CodeDetectorException
{
    public function __construct()
    {
        parent::__construct('Storage class is not defined.');
    }
}
