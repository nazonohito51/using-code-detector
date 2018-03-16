<?php
namespace CodeDetector\Detector;

class CoverageData implements \IteratorAggregate
{
    const STORAGE_KEY_PREFIX = 'CodeDetector';

    private $data;
    private $id;
    private $ignore_root_dir;

    public function __construct(array $xdebug_coverage_data, $ignore_root_dir, $id = null)
    {
        $this->id = $id;
        $this->ignore_root_dir = substr($ignore_root_dir, -1) == '/' ? $ignore_root_dir : $ignore_root_dir . '/';
        $this->data = $this->convertXDebug($xdebug_coverage_data);
    }

    public static function createFromStorage()
    {

    }

    private function convertXDebug(array $xdebug_coverage_data)
    {
        $data = array();

        foreach ($xdebug_coverage_data as $file => $lines) {
            $key = $this->convertStorageKey($file);
            $data[$key] = $lines;
        }

        return $data;
    }

    private function convertStorageKey($path)
    {
        $hash = hash_file('md5', $path);
        $path = str_replace($this->ignore_root_dir, '', $path);

        return self::STORAGE_KEY_PREFIX . ":{$path}:{$hash}";
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
}
