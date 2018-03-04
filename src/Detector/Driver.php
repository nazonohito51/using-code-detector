<?php
namespace CodeDetector\Detector;

class Driver extends \PHP_CodeCoverage_Driver_Xdebug
{
    public function start()
    {
        xdebug_start_code_coverage();
    }
}
