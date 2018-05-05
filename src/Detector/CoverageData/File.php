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

    public function __construct($path, array $coverage = array(), $hash = null)
    {
        $this->path = $path;
        $this->coverage = $coverage;
        $this->hash = $hash;

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
    public static function createFromStorage(StorageInterface $storage, $rootDir)
    {
        $files = array();

        $data = $storage->getAll(self::STORAGE_KEY_PREFIX);
        foreach ($data as $storageKey => $serializedCoverage) {
            list($prefix, $path, $hash) = explode(':', $storageKey);
            $coverage = unserialize($serializedCoverage);

            $realPath = Path::join($rootDir, $path);
            $files[$realPath] = new self($realPath, $coverage, $hash);
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
        $storage->set($this->storageKey($rootDir), serialize($this->getCoverage()));
    }

    private function storageKey($rootDir)
    {
        $relativePath = Path::makeRelative($this->path, $rootDir);

        return self::STORAGE_KEY_PREFIX . ":{$relativePath}:{$this->hash}";
    }
}
