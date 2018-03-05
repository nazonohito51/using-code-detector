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

    public static function shutdown()
    {
        $data = self::$detector->stop();

        // TODO: get past data
        // TODO: merge data
        // TODO: save merge data
    }

    public static function registerDefault($scope, StorageInterface $storage, $id = null)
    {
        if (!is_dir($scope)) {
            throw new InvalidScopeException();
        }

        $id = !is_null($id) ? $id : 'test';
        $detector = new Detector(new Driver());
        $detector->filter()->addDirectoryToWhitelist($scope);

        $detector->start($id);
        register_shutdown_function(array('\CodeDetector\Registrar', 'shutdown'));

        self::$detector = $detector;
    }
}
