<?php
namespace CodeDetector\Detector;

use CodeDetector\Exceptions\DependencyException;

class Driver
{
    const LINE_EXECUTED = 1;

    public function __construct()
    {
        if (!extension_loaded('xdebug')) {
            throw new DependencyException('CodeDetector requires Xdebug');
        }

        if (version_compare(phpversion('xdebug'), '2.2.0-dev', '>=') &&
            !ini_get('xdebug.coverage_enable')) {
            throw new DependencyException(
                'xdebug.coverage_enable=On has to be set in php.ini'
            );
        }
    }

    public function start()
    {
        xdebug_start_code_coverage();
    }

    public function stop()
    {
        $coverageData = xdebug_get_code_coverage();
        xdebug_stop_code_coverage();

        return $coverageData;
    }
}
