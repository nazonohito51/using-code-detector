<?php
namespace CodeDetector;

use CodeDetector\Detector\CoverageData;
use CodeDetector\Detector\Driver;
use CodeDetector\Detector\Storage\StorageInterface;
use CodeDetector\Exceptions\InvalidFilePathException;

class Detector
{
    const STORAGE_KEY_PREFIX = 'CodeDetector';

    private $dir;
    private $driver;
    private $storage;

    private $id;

    public function __construct($dir, Driver $driver, StorageInterface $storage)
    {
        if (realpath($dir) === false) {
            throw new InvalidFilePathException();
        }

        $this->dir = realpath($dir);
        $this->driver = $driver;
        $this->storage = $storage;
    }

    public function start($id)
    {
        $this->id = $id;
        $this->driver->start();
    }

    public function stop()
    {
        $xDebugCoverage = $this->filterFiles($this->driver->stop());
        $data = CoverageData::createFromXDebug($xDebugCoverage, $this->dir, $this->id);
        $storageData = CoverageData::createFromStorage($this->storage, $this->dir);
        $storageData->merge($data);
        $storageData->save($this->storage, $this->dir);
    }

    public function getData()
    {
        $storageData = CoverageData::createFromStorage($this->storage, $this->dir);
        return $storageData->getPHP_CodeCoverageData($this->dir);
    }

    private function filterFiles(array $xDebugCoverage)
    {
        $pattern = '/' . preg_quote($this->dir, '/') . '/';

        $ret = array();
        foreach ($xDebugCoverage as $path => $coverage) {
            if (preg_match($pattern, $path)) {
                $ret[$path] = $coverage;
            }
        }
        return $ret;
    }
}
