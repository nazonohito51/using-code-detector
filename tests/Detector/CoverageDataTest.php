<?php
namespace CodeDetector\Detector;

use PHPUnit\Framework\TestCase;
use Mockery as m;

class CoverageDataTest extends TestCase
{
    const ID_FROM_XDEBUG = 'from_xdebug';
    const ID_FROM_STORAGE = 'from_storage';

    public function testGet()
    {
        $id = self::ID_FROM_XDEBUG;

        $coverageData = new CoverageData($this->getXDebugCoverageDataMock(), realpath(__DIR__ . '/../fixtures/'), $id);

        $index = 0;
        foreach ($coverageData as $file => $lines) {
            if ($index === 0) {
                $this->assertEquals(CoverageData::STORAGE_KEY_PREFIX . ':hoge.php:7790190cbd3eba546205c88ce0682472', $file);
                $this->assertEquals(array(
                    1 => array($id),
                    2 => array($id),
                    3 => array($id),
                ), $lines);
            } elseif ($index === 1) {
                $this->assertEquals(CoverageData::STORAGE_KEY_PREFIX . ':directory/fuga.php:fef885e0b393d4f33f9183ec08ea7046', $file);
                $this->assertEquals(array(
                    10 => array($id),
                    11 => array($id),
                    12 => array($id),
                    13 => array($id),
                    14 => array($id),
                ), $lines);
            }

            $index++;
        }
        $this->assertEquals(2, $index);
    }

    public function testCreateFromStorage()
    {
        $id = self::ID_FROM_STORAGE;

        $coverageData = CoverageData::createFromStorage($this->getStorageMock());

        $index = 0;
        foreach ($coverageData as $file => $lines) {
            if ($index === 0) {
                $this->assertEquals('hoge.php:7790190cbd3eba546205c88ce0682472', $file);
                $this->assertEquals(array(
                    1 => array($id),
                    2 => array($id),
                    3 => array($id),
                ), $lines);
            } elseif ($index === 1) {
                $this->assertEquals('directory/fuga.php:fef885e0b393d4f33f9183ec08ea7046', $file);
                $this->assertEquals(array(
                    10 => array($id),
                    11 => array($id),
                    12 => array($id),
                    13 => array($id),
                    14 => array($id),
                ), $lines);
            }

            $index++;
        }
        $this->assertEquals(2, $index);
    }

    public function testMerge()
    {
        $id_xdebug = self::ID_FROM_XDEBUG;
        $id_storage = self::ID_FROM_STORAGE;
        $coverageDataFromXDebug = new CoverageData($this->getXDebugCoverageDataMock(), realpath(__DIR__ . '/../fixtures'), $id);
        $coverageDataFromStorage = CoverageData::createFromStorage($this->getStorageMock());

        $coverageDataFromXDebug->merge($coverageDataFromStorage);

        $index = 0;
        foreach ($coverageDataFromXDebug as $file => $lines) {
            if ($index === 0) {
                $this->assertEquals('hoge.php:7790190cbd3eba546205c88ce0682472', $file);
                $this->assertEquals(array(
                    1 => array($id_storage),
                    2 => array($id_storage),
                    3 => array($id_storage, $id_xdebug),
                    4 => array($id_xdebug),
                    5 => array($id_xdebug),
                ), $lines);
            } elseif ($index === 1) {
                $this->assertEquals('directory/fuga.php:fef885e0b393d4f33f9183ec08ea7046', $file);
                $this->assertEquals(array(
                    10 => array($id_storage, $id_xdebug),
                    11 => array($id_storage, $id_xdebug),
                    12 => array($id_storage, $id_xdebug),
                    13 => array($id_storage, $id_xdebug),
                    14 => array($id_storage, $id_xdebug),
                ), $lines);
            }

            $index++;
        }
        $this->assertEquals(2, $index);
    }

    public function testSave()
    {
        $id_xdebug = self::ID_FROM_XDEBUG;
        $id_storage = self::ID_FROM_STORAGE;
        $storage = $this->getStorageMock();
        $storage->shouldReceive('set')->with('hoge.php:7790190cbd3eba546205c88ce0682472', serialize(array(
            1 => array($id_storage),
            2 => array($id_storage),
            3 => array($id_storage, $id_xdebug),
            4 => array($id_xdebug),
            5 => array($id_xdebug),
        )));
        $storage->shouldReceive('set')->with('directory/fuga.php:fef885e0b393d4f33f9183ec08ea7046', serialize(array(
            10 => array($id_storage, $id_xdebug),
            11 => array($id_storage, $id_xdebug),
            12 => array($id_storage, $id_xdebug),
            13 => array($id_storage, $id_xdebug),
            14 => array($id_storage, $id_xdebug),
        )));
        $coverageDataFromXDebug = new CoverageData($this->getXDebugCoverageDataMock(), realpath(__DIR__ . '/../fixtures'), $id);
        $coverageDataFromStorage = CoverageData::createFromStorage();

        $coverageDataFromXDebug->merge($coverageDataFromStorage);
        $coverageDataFromXDebug->save($storage);
    }

    public function testGetPHP_CodeCoverageData()
    {
        $id = self::ID_FROM_XDEBUG;

        $coverageData = new CoverageData($this->getXDebugCoverageDataMock(), realpath(__DIR__ . '/../fixtures'), $id);

        $index = 0;
        foreach ($coverageData as $file => $lines) {
            if ($index === 0) {
                $this->assertEquals(realpath(__DIR__ . '/../fixtures/hoge.php'), $file);
                $this->assertEquals(array(
                    1 => array($id),
                    2 => array($id),
                    3 => array($id),
                ), $lines);
            } elseif ($index === 1) {
                $this->assertEquals(realpath(__DIR__ . '/../fixtures/directory/fuga.php'), $file);
                $this->assertEquals(array(
                    10 => array($id),
                    11 => array($id),
                    12 => array($id),
                    13 => array($id),
                    14 => array($id),
                ), $lines);
            }

            $index++;
        }
        $this->assertEquals(2, $index);
    }

    private function getXDebugCoverageDataMock()
    {
        return array(
            realpath(__DIR__ . '/../fixtures/hoge.php') => array(
                1 => 1,
                2 => 1,
                3 => 1,
            ),
            realpath(__DIR__ . '/../fixtures/directory/fuga.php') => array(
                10 => 1,
                11 => 1,
                12 => 1,
                13 => 1,
                14 => 1,
            )
        );
    }

    private function getStorageMock()
    {
        $id = self::ID_FROM_STORAGE;
        $storage = m::mock('CodeDetector\Detector\StorageInterface');
        $storage->shouldReceive('getAll')->andReturn(array(
            realpath('hoge.php:7790190cbd3eba546205c88ce0682472') => array(
                3 => array($id),
                4 => array($id),
                5 => array($id),
            ),
            realpath('directory/fuga.php:fef885e0b393d4f33f9183ec08ea7046') => array(
                10 => array($id),
                11 => array($id),
                12 => array($id),
                13 => array($id),
                14 => array($id),
            )
        ));

        return $storage;
    }
}
