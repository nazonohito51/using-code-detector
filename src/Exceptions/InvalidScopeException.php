<?php
namespace CodeDetector\Exceptions;


class InvalidScopeException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('scope must be directory.');
    }
}
