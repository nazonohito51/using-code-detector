<?php
namespace CodeDetector;

use CodeDetector\Detector\Driver;
use CodeDetector\Detector\StorageInterface;
use CodeDetector\Exceptions\InvalidFilePathException;
use PHP_CodeCoverage_Report_HTML;

class Reporter
{
    public static function generate(StorageInterface $storage, $target)
    {
        if (!is_dir($target)) {
            throw new InvalidFilePathException();
        }

        // TODO: get data from Detector
        $detector = new Detector(self::createDefaultDriver(), $storage);
        // TODO: filtering data by file_exists and hash_file
        $data = $detector->getAllData();
        // TODO: set data to PHP_CodeCoverage
        $coverage = new \PHP_CodeCoverage();
        $coverage->setData($data);

        $writer = new PHP_CodeCoverage_Report_HTML;
        $writer->process($coverage, '/tmp/coverage');
    }

    private static function createDefaultDriver()
    {
        return new Driver();
    }
}
