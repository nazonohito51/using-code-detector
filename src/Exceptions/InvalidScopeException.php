<?php
namespace CodeDetector\Exceptions;


class InvalidScopeException extends CodeDetectorException
{
    public function __construct()
    {
        parent::__construct('scope must be directory.');
    }
}
