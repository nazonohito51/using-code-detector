<?php
namespace CodeDetector;

use CodeDetector\Detector\Driver;
use CodeDetector\Detector\StorageInterface;
use CodeDetector\Exceptions\InvalidScopeException;

class Registrar
{
    /**
     * @var Detector
     */
    static private $detector;

    public static function registerDefault($scope, StorageInterface $storage, $id = null)
    {
        if (!is_dir($scope)) {
            throw new InvalidScopeException();
        }

        $id = !is_null($id) ? $id : 'test';

        $coverage = new \PHP_CodeCoverage(self::createDriver(), self::createFilter($scope));

        $detector = new Detector($coverage, $storage);
        $detector->start($id);

        register_shutdown_function(array('\CodeDetector\Registrar', 'shutdown'));

        self::$detector = $detector;
    }

    public static function shutdown()
    {
        self::$detector->stop();
    }

    private static function createDriver()
    {
        return new Driver();
    }

    private static function createFilter($scope)
    {
        $filter = new \PHP_CodeCoverage_Filter();
        $filter->addDirectoryToWhitelist($scope);

        return $filter;
    }
}
