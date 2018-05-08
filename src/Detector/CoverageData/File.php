<?php
namespace CodeDetector\Detector\CoverageData;

use CodeDetector\Detector\Storage\StorageInterface;
use CodeDetector\Exceptions\InvalidFilePathException;
use Webmozart\PathUtil\Path;

class File
{
    const STORAGE_KEY_PREFIX = 'CodeDetector';

    private $path;
    private $coverage;
    private $hash;
    private $sample;

    public function __construct($path, array $coverage = array(), $hash = null, $sample = 1)
    {
        $this->path = $path;
        $this->coverage = $coverage;
        $this->hash = $hash;
        $this->sample = (int)$sample;

        if (is_null($this->hash)) {
            if (!file_exists($path)) {
                throw new InvalidFilePathException();
            } else {
                $this->hash = hash_file('md5', $this->path);
            }
        }
    }

    /**
     * @param StorageInterface $storage
     * @param string $rootDir
     * @return File[]
     */
    public static function buildCollectionFromStorage(StorageInterface $storage, $rootDir)
    {
        $files = array();

        $data = $storage->getAll(self::STORAGE_KEY_PREFIX);
        foreach ($data as $storageKey => $serializedValue) {
            list($prefix, $path, $hash) = explode(':', $storageKey);
            $unserializedValue = unserialize($serializedValue);
            if (!isset($unserializedValue['coverage']) || !isset($unserializedValue['sample'])) {
                continue;
            }
            $coverage = $unserializedValue['coverage'];
            $sample = $unserializedValue['sample'];

            $realPath = Path::join($rootDir, $path);
            $files[$realPath] = new self($realPath, $coverage, $hash, $sample);
        }

        return $files;
    }

    public function isExist()
    {
        return file_exists($this->path) && $this->hash === hash_file('md5', $this->path);
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getCoverage()
    {
        return $this->coverage;
    }

    public function append($id, $coverageLines)
    {
        if (!is_array($coverageLines)) {
            $coverageLines = array($coverageLines);
        }

        foreach ($coverageLines as $line) {
            $this->appendLine($id, $line);
        }

        ksort($this->coverage);
    }

    public function appendFile(File $file)
    {
        $coverage = $file->getCoverage();
        foreach ($coverage as $line => $ids) {
            if (!isset($this->coverage[$line])) {
                $this->coverage[$line] = $ids;
            } else {
                $this->coverage[$line] = array_unique(array_merge($this->coverage[$line], $ids));
            }
        }

        ksort($this->coverage);
    }

    private function appendLine($id, $line)
    {
        if (!isset($this->coverage[$line]) || !in_array($id, $this->coverage[$line])) {
            $this->coverage[$line][] = $id;
        }
    }

    public function save(StorageInterface $storage, $rootDir)
    {
        $storage->set($this->storageKey($rootDir), serialize(array(
            'coverage' => $this->getCoverage(),
            'sample' => $this->sample
        )));
    }

    public function delete(StorageInterface $storage, $rootDir)
    {
        $storage->del($this->storageKey($rootDir));
    }

    private function storageKey($rootDir)
    {
        $relativePath = Path::makeRelative($this->path, $rootDir);

        return self::STORAGE_KEY_PREFIX . ":{$relativePath}:{$this->hash}";
    }

    public function isEqual(File $that)
    {
        return $this->getPath() == $that->getPath() && $this->getHash() == $that->getHash();
    }
}
