<?php
namespace CodeDetector;

use CodeDetector\Detector\Driver;
use CodeDetector\Detector\StorageInterface;
use CodeDetector\Exceptions\Storage\ConnectionException;

class Detector
{
    const STORAGE_KEY_PREFIX = 'CodeDetector';

    private $ignoreFilePath;
    private $driver;
    private $storage;

    private $id;

    public function __construct(Driver $driver, StorageInterface $storage)
    {
        $this->driver = $driver;
        $this->storage = $storage;
    }

    public function setIgnoreFilePathRegexp($regexp)
    {
        $this->ignoreFilePath = $regexp;
    }

    public function start($id)
    {
        $this->id = $id;
        $this->driver->start();
    }

    public function stop()
    {
        $coverageData = $this->driver->stop();

        foreach ($coverageData as $file => $lines) {
            // TODO: convert file path
            $key = $this->convertStorageKey($file);
            // TODO: get past data
            $pastData = $this->getData($key);
            // TODO: merge data
            foreach ($lines as $line => $execute) {
                if ($execute == Driver::LINE_EXECUTED) {
                    if (empty($pastData[$line]) || !in_array($this->id, $pastData[$line])) {
                        $pastData[$line][] = $this->id;
                    }
                }
            }
            // TODO: saveData
            $this->saveData($key, $pastData);

            // NOTICE: files in $data is checked by file_exists when merge, also setData...
        }

//        $this->saveData($coverageData);
    }

    private function convertStorageKey($path)
    {
        $hash = hash_file('md5', $path);
        $path = preg_replace($this->ignoreFilePath, '', $path);

        return self::STORAGE_KEY_PREFIX . ":{$path}:{$hash}";
    }

    private function getData($key)
    {
        try {
            $data = $this->storage->get($key);
            if (!is_null($data) && !empty($data)) {
                return unserialize($data);
            }
        } catch (ConnectionException $e) {
            // TODO: notification
        } catch (\Exception $e) {
            // TODO: notification
        }

        return null;
    }

    private function saveData($key, $data)
    {
        // if saving data is failed, Detector will only notify, and not throw Exception.
        try {
            $this->storage->set($key, serialize($data));
        } catch (ConnectionException $e) {
            // TODO: notification
        } catch (\Exception $e) {
            // TODO: notification
        }
    }

    public function getAllData()
    {
        try {
            return $this->storage->getAll();
        } catch (ConnectionException $e) {
            // TODO: notification
        } catch (\Exception $e) {
            // TODO: notification
        }
    }
}
