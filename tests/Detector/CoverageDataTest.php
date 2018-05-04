<?php
namespace CodeDetector\Detector;

use CodeDetector\TestCase;
use Mockery as m;

class CoverageDataTest extends TestCase
{
    const ID_FROM_XDEBUG = 'from_xdebug';
    const ID_FROM_STORAGE = 'from_storage';

    public function tearDown()
    {
        m::close();
    }

    public function testGet()
    {
        $id = self::ID_FROM_XDEBUG;

        $coverageData = CoverageData::createFromXDebug($this->getXDebugCoverageDataMock(), realpath(__DIR__ . '/../fixtures/'), $id);

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
                $this->assertEquals(CoverageData::STORAGE_KEY_PREFIX . ':hoge.php:7790190cbd3eba546205c88ce0682472', $file);
                $this->assertEquals(array(
                    3 => array($id),
                    4 => array($id),
                    5 => array($id),
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

    public function testMerge()
    {
        $id_xdebug = self::ID_FROM_XDEBUG;
        $id_storage = self::ID_FROM_STORAGE;
        $coverageDataFromXDebug = CoverageData::createFromXDebug($this->getXDebugCoverageDataMock(), realpath(__DIR__ . '/../fixtures'), $id_xdebug);
        $coverageDataFromStorage = CoverageData::createFromStorage($this->getStorageMock());

        $coverageDataFromStorage->merge($coverageDataFromXDebug);

        $index = 0;
        foreach ($coverageDataFromStorage as $file => $lines) {
            if ($index === 0) {
                $this->assertEquals(CoverageData::STORAGE_KEY_PREFIX . ':hoge.php:7790190cbd3eba546205c88ce0682472', $file);
                $this->assertEquals(array(
                    1 => array($id_xdebug),
                    2 => array($id_xdebug),
                    3 => array($id_storage, $id_xdebug),
                    4 => array($id_storage),
                    5 => array($id_storage),
                ), $lines);
            } elseif ($index === 1) {
                $this->assertEquals(CoverageData::STORAGE_KEY_PREFIX . ':directory/fuga.php:fef885e0b393d4f33f9183ec08ea7046', $file);
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
        $storage_mock = $this->getStorageMock();
        $storage_mock->shouldReceive('set')->with(CoverageData::STORAGE_KEY_PREFIX . ':hoge.php:7790190cbd3eba546205c88ce0682472', array(
            1 => array($id_xdebug),
            2 => array($id_xdebug),
            3 => array($id_storage, $id_xdebug),
            4 => array($id_storage),
            5 => array($id_storage),
        ))->once();
        $storage_mock->shouldReceive('set')->with(CoverageData::STORAGE_KEY_PREFIX . ':directory/fuga.php:fef885e0b393d4f33f9183ec08ea7046', array(
            10 => array($id_storage, $id_xdebug),
            11 => array($id_storage, $id_xdebug),
            12 => array($id_storage, $id_xdebug),
            13 => array($id_storage, $id_xdebug),
            14 => array($id_storage, $id_xdebug),
        ))->once();
        $coverageDataFromXDebug = CoverageData::createFromXDebug($this->getXDebugCoverageDataMock(), realpath(__DIR__ . '/../fixtures'), $id_xdebug);
        $coverageDataFromStorage = CoverageData::createFromStorage($storage_mock);

        $coverageDataFromStorage->merge($coverageDataFromXDebug);
        $coverageDataFromStorage->save($storage_mock);
    }

    public function testGetPHP_CodeCoverageData()
    {
        $id_xdebug = self::ID_FROM_XDEBUG;
        $id_storage = self::ID_FROM_STORAGE;
        $coverageDataFromXDebug = CoverageData::createFromXDebug($this->getXDebugCoverageDataMock(), realpath(__DIR__ . '/../fixtures'), $id_xdebug);
        $coverageDataFromStorage = CoverageData::createFromStorage($this->getStorageMock());

        $coverageDataFromStorage->merge($coverageDataFromXDebug);
        $data = $coverageDataFromStorage->getPHP_CodeCoverageData(realpath(__DIR__ . '/../fixtures'));

        $this->assertEquals(array(
            realpath(__DIR__ . '/../fixtures/hoge.php') => array(
                1 => array($id_xdebug),
                2 => array($id_xdebug),
                3 => array($id_storage, $id_xdebug),
                4 => array($id_storage),
                5 => array($id_storage),
            ),
            realpath(__DIR__ . '/../fixtures/directory/fuga.php') => array(
                10 => array($id_storage, $id_xdebug),
                11 => array($id_storage, $id_xdebug),
                12 => array($id_storage, $id_xdebug),
                13 => array($id_storage, $id_xdebug),
                14 => array($id_storage, $id_xdebug),
            ),
        ), $data);
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
        $storage = m::mock('CodeDetector\Detector\Storage\StorageInterface');
        $storage->shouldReceive('getAll')->andReturn(array(
            CoverageData::STORAGE_KEY_PREFIX . ':hoge.php:7790190cbd3eba546205c88ce0682472' => array(
                3 => array($id),
                4 => array($id),
                5 => array($id),
            ),
            CoverageData::STORAGE_KEY_PREFIX . ':directory/fuga.php:fef885e0b393d4f33f9183ec08ea7046' => array(
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
