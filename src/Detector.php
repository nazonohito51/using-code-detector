<?php
namespace CodeDetector;

use CodeDetector\Detector\StorageInterface;
use CodeDetector\Exceptions\Storage\ConnectionException;
use PHP_CodeCoverage;

class Detector
{
    private $coverage;
    private $storage;

    public function __construct(PHP_CodeCoverage $coverage, StorageInterface $storage)
    {
        $this->coverage = $coverage;
        $this->storage = $storage;
    }

    public function start($id)
    {
        $this->coverage->start($id);
    }

    public function stop()
    {
        $data = $this->coverage->stop();

//        // TODO: get past data
//        $past_data = $this->storage->get(self::STORAGE_KEY);
//
//        // TODO: merge data
//        $coverage = new PHP_CodeCoverage();
//        $coverage->setData($past_data);
//        // TODO: files in $data is checked by file_exists when merge, also setData...
//        $this->coverage->merge($coverage);

        $this->saveData($data);
    }

    public function saveData($data)
    {
        // if saving data is failed, Detector will only notify, and not throw Exception.
        try {
            $this->storage->set('CodeDetector', serialize($data));
        } catch (ConnectionException $e) {
            // TODO: notification
        } catch (\Exception $e) {
            // TODO: notification
        }
    }
}
