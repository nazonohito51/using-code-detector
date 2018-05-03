<?php
namespace CodeDetector;

use CodeDetector\Detector\CoverageData;
use CodeDetector\Detector\Driver;
use CodeDetector\Detector\StorageInterface;
use CodeDetector\Exceptions\Storage\ConnectionException;

class Detector
{
    const STORAGE_KEY_PREFIX = 'CodeDetector';

    private $driver;
    private $storage;

    private $id;

    public function __construct(Driver $driver, StorageInterface $storage)
    {
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
        $data = CoverageData::createFromXDebug($this->driver->stop(), $this->id);
        $storageData = CoverageData::createFromStorage($this->storage);
        $storageData->merge($data);
        $storageData->save($this->storage);

//        foreach ($coverageData as $file => $lines) {
//            // TODO: convert file path
//            $key = $this->convertStorageKey($file);
//            // TODO: get past data
//            $pastData = $this->getData($key);
//            // TODO: merge data
//            foreach ($lines as $line => $execute) {
//                if ($execute == Driver::LINE_EXECUTED) {
//                    if (empty($pastData[$line]) || !in_array($this->id, $pastData[$line])) {
//                        $pastData[$line][] = $this->id;
//                    }
//                }
//            }
//            // TODO: saveData
//            $this->saveData($key, $pastData);
//        }
    }

//    private function convertStorageKey($path)
//    {
//        $hash = hash_file('md5', $path);
//        $path = preg_replace($this->ignoreFilePath, '', $path);
//
//        return self::STORAGE_KEY_PREFIX . ":{$path}:{$hash}";
//    }
//
//    private function getData($key)
//    {
//        try {
//            $data = $this->storage->get($key);
//            if (!is_null($data) && !empty($data)) {
//                return unserialize($data);
//            }
//        } catch (ConnectionException $e) {
//            // TODO: notification
//        } catch (\Exception $e) {
//            // TODO: notification
//        }
//
//        return null;
//    }
//
//    private function saveData($key, $data)
//    {
//        // if saving data is failed, Detector will only notify, and not throw Exception.
//        try {
//            $this->storage->set($key, serialize($data));
//        } catch (ConnectionException $e) {
//            // TODO: notification
//        } catch (\Exception $e) {
//            // TODO: notification
//        }
//    }
//
//    public function getAllData()
//    {
//        try {
//            return $this->storage->getAll();
//        } catch (ConnectionException $e) {
//            // TODO: notification
//        } catch (\Exception $e) {
//            // TODO: notification
//        }
//    }
}
