<?php
namespace CodeDetector;

use CodeDetector\Detector\Driver;
use CodeDetector\Detector\StorageInterface;
use CodeDetector\Exceptions\InvalidFilePathException;

class Registrar
{
    /**
     * @var Detector
     */
    static private $detector;

    public static function register(Detector $detector, $id = null)
    {
        if (is_null($id)) {
            if (isset($_SERVER['SCRIPT_NAME']) && !empty($_SERVER['SCRIPT_NAME'])) {
                $id = $_SERVER['SCRIPT_NAME'];
            } else {
                $id = 'unknown';
            }
        }
        $detector->start($id);

        register_shutdown_function(array('\CodeDetector\Registrar', 'shutdown'));

        self::$detector = $detector;
    }

    public static function registerDefault($scope, StorageInterface $storage, $reposRootRegexp = null, $id = null)
    {
        if (!is_dir($scope)) {
            throw new InvalidFilePathException();
        }
        $detector = new Detector(self::createDefaultDriver(), $storage);
        if (!is_null($reposRootRegexp)) {
            $detector->setIgnoreFilePathRegexp($reposRootRegexp);
        }
        self::register($detector, $id);
    }

    public static function shutdown()
    {
        self::$detector->stop();
    }

    private static function createDefaultDriver()
    {
        return new Driver();
    }

    private static function createDefaultFilter($scope)
    {
        $filter = new \PHP_CodeCoverage_Filter();
        $filter->addDirectoryToWhitelist($scope);

        return $filter;
    }
}
