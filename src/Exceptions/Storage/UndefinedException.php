<?php
namespace CodeDetector\Exceptions\Storage;

class UndefinedException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('Storage class is not defined.');
    }
}
