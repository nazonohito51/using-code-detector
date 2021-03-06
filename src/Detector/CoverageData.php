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
        $files = File::buildCollectionFromStorage($storage, $rootDir);
        foreach ($files as $path => $file) {
            if (!$file->isExist()) {
                $file->delete($storage, $rootDir);
                unset($files[$path]);
            }
        }
        return new self($files);
    }

    public static function createFromXDebug(array $xdebugCoverageData, $id = null)
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
            if (!isset($this->files[$path])) {
                $this->files[$path] = clone $file;
            } elseif ($this->files[$path]->isEqual($file)) {
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

    public function getPHP_CodeCoverageData()
    {
        $result = array(
            'data' => array(),
            'tests' => array()
        );
        foreach ($this->getFiles() as $path => $file) {
            if ($file->isExist()) {
                $coverage = $file->getCoverage();
                $result['data'][$file->getPath()] = $coverage;

                foreach ($coverage as $line => $ids) {
                    foreach ($ids as $id) {
                        if (!isset($result['tests'][$id])) {
                            $result['tests'][$id] = array('size' => 'unknown', 'status' => null);
                        }
                    }
                 }
            }
        }

        return $result;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->files);
    }
}
