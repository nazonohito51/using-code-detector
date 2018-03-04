<?php
namespace CodeDetector;

use CodeDetector\Detector\Driver;

class Registrar
{
    /**
     * @var Detector
     */
    static private $detector;

    private function shutdown()
    {
        $data = self::$detector->stop();

        // TODO: get past data
        // TODO: merge data
        // TODO: save merge data
    }

    public static function registerDefault($id = null)
    {
        $id = !is_null($id) ? $id : 'test';

        $detector = new Detector(new Driver());
        $detector->start($id);

        register_shutdown_function(array($detector, 'shutdown'));

        self::$detector = $detector;
    }
}
