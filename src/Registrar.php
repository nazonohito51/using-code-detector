<?php
namespace CodeDetector;

use CodeDetector\Detector\Driver;
use CodeDetector\Detector\Storage\StorageInterface;
use CodeDetector\Exceptions\InvalidFilePathException;

class Registrar
{
    /**
     * @var Detector
     */
    static private $detector;

    public static function register(Detector $detector, $id = null)
    {
        try {
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
        } catch (\Exception $e) {
            // logging
        }
    }

    public static function registerDefault($scope, StorageInterface $storage, $id = null)
    {
        if (!is_dir($scope)) {
            throw new InvalidFilePathException();
        }
        $detector = new Detector($scope, self::createDefaultDriver(), $storage);
        self::register($detector, $id);
    }

    public static function shutdown()
    {
        if (self::$detector instanceof Detector) {
            try {
                self::$detector->stop();
            } catch (\Exception $e) {
                // logging
            }
        }
    }

    public static function report($scope, StorageInterface $storage, $destPath)
    {
        if (!is_dir($destPath)) {
            throw new InvalidFilePathException();
        }

        $detector = new Detector($scope, self::createDefaultDriver(), $storage);
        $php_CodeCoverageData = $detector->getData();

        $coverage = new \PHP_CodeCoverage();
        $coverage->setData($php_CodeCoverageData);

        $writer = new \PHP_CodeCoverage_Report_HTML();
        $writer->process($coverage, $destPath);
    }

    public static function clear($scope, StorageInterface $storage)
    {
        if (!is_dir($scope)) {
            throw new InvalidFilePathException();
        }
        $detector = new Detector($scope, self::createDefaultDriver(), $storage);
        $detector->clearData();
    }

    private static function createDefaultDriver()
    {
        return new Driver();
    }
}
