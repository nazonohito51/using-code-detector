<?php
namespace CodeDetector;

use CodeDetector\Detector\CoverageData\File;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $fixtures;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $dir = $this->fixturesDir();
        // Under php5.4, expression in default value(for property and constant) is not allowed.
        $this->fixtures = array(
            'file1' => array(
                'path' => $dir . '/hoge.php',
                'storageKey' => File::STORAGE_KEY_PREFIX . ':tests/fixtures/hoge.php:7790190cbd3eba546205c88ce0682472'
            ),
            'file2' => array(
                'path' => $dir . '/directory/fuga.php',
                'storageKey' => File::STORAGE_KEY_PREFIX . ':tests/fixtures/directory/fuga.php:65ac6a57264dcf93c28bbaf87660fce7'
            ),
            'file3' => array(
                'path' => $dir . '/directory/piyo.php',
                'storageKey' => File::STORAGE_KEY_PREFIX . ':tests/fixtures/directory/piyo.php:478de0143325e325388a60c6935981b8'
            )
        );
    }

    protected function reposRootDir()
    {
        return realpath(__DIR__ . '/../');
    }

    protected function fixturesDir()
    {
        return realpath(__DIR__ . '/fixtures');
    }
}
