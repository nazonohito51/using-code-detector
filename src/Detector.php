<?php
namespace CodeDetector;

use CodeDetector\Detector\Driver;
use CodeDetector\Detector\StorageInterface;
use CodeDetector\Exceptions\InvalidScopeException;
use PHP_CodeCoverage;

class Detector
{
    /**
     * @var Detector
     */
    static private $instance;

    private $coverage;
    private $storage;

    public function __construct(PHP_CodeCoverage $coverage, StorageInterface $storage)
    {
        $this->coverage = $coverage;
        $this->storage = $storage;
    }

    public static function shutdown()
    {
        $instance = self::$instance;
        $data = $instance->coverage->stop();
var_dump($data);
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

        $coverage = new PHP_CodeCoverage(new Driver());
        $coverage->filter()->addDirectoryToWhitelist($scope);
        $coverage->start($id);

        $instance = new self($coverage, $storage);
        register_shutdown_function(array('\CodeDetector\Detector', 'shutdown'));

        self::$instance = $instance;
    }
}
