<?php
namespace CodeDetector\Detector;

use CodeDetector\Detector\CoverageData\File;
use CodeDetector\Detector\Storage\StorageInterface;
use Webmozart\PathUtil\Path;

class CoverageData implements \IteratorAggregate
{
    private $files;

    /**
     * @param File[] $files
     */
    private function __construct(array $files)
    {
        $this->files = $files;
    }

    public static function createFromStorage(StorageInterface $storage, $rootDir)
    {
        $files = File::createFromStorage($storage, $rootDir);
        return new self($files);
    }

    public static function createFromXDebug(array $xdebugCoverageData, $rootDir, $id = null)
    {
        $files = array();

        foreach ($xdebugCoverageData as $path => $lines) {
            $files[$path] = new File($path);
            foreach ($lines as $line => $execute) {
                if ($execute == Driver::LINE_EXECUTED) {
                    $files[$path]->append($id, $line);
                }
            }
        }

        return new self($files);
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function merge(CoverageData $that)
    {
        foreach ($that->getFiles() as $path => $file) {
            // TODO: when path is equal, but hash is not equal
            if (!isset($this->files[$path])) {
                $this->files[$path] = clone $file;
            } else {
                $this->files[$path]->appendFile($file);
            }
        }
    }

    public function save(StorageInterface $storage, $rootDir)
    {
        foreach ($this->getFiles() as $path => $file) {
            $file->save($storage, $rootDir);
        }
    }

    public function getPHP_CodeCoverageData($rootDir)
    {
        $result = array();
        foreach ($this->getFiles() as $path => $file) {
            if ($file->isExist()) {
                $result[$file->getPath()] = $file->getCoverage();
            }
        }

        return $result;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->files);
    }
}
