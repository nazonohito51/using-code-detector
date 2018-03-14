<?php
namespace CodeDetector\Exceptions;


class InvalidFilePathException extends CodeDetectorException
{
    public function __construct()
    {
        parent::__construct('scope must be directory.');
    }
}
