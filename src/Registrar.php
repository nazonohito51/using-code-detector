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
        self::$detector->stop();
    }

    private static function createDefaultDriver()
    {
        return new Driver();
    }
}
