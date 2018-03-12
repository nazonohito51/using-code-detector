<?php
namespace CodeDetector\Exceptions;

class DependencyException extends CodeDetectorException
{
    public function __construct($msg = null)
    {
        parent::__construct(!is_null($msg) ? $msg : 'resolving dependency is failed.');
    }
}
