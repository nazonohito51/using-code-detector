<?php
namespace CodeDetector;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function fixturesDir()
    {
        // Under php5.4, expression in default value(for property and constant) is not allowed.
        return __DIR__ . '/fixtures';
    }

    protected function fixtures()
    {
        // Under php5.4, expression in default value(for property and constant) is not allowed.
        $dir = $this->fixturesDir();
        return array(
            'file1' => array(
                'path' => $dir . '/hoge.php',
                'storageKey' => Detector::STORAGE_KEY_PREFIX . ':tests/fixtures/hoge.php:7790190cbd3eba546205c88ce0682472'
            ),
            'file2' => array(
                'path' => $dir . '/directory/fuga.php',
                'storageKey' => Detector::STORAGE_KEY_PREFIX . ':tests/fixtures/directory/fuga.php:65ac6a57264dcf93c28bbaf87660fce7'
            ),
            'file3' => array(
                'path' => $dir . '/directory/piyo.php',
                'storageKey' => Detector::STORAGE_KEY_PREFIX . ':tests/fixtures/directory/piyo.php:478de0143325e325388a60c6935981b8'
            )
        );
    }
}
