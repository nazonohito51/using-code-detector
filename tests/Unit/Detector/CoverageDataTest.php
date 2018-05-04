<?php
namespace CodeDetector\Detector;

use CodeDetector\TestCase;
use Mockery as m;

class CoverageDataTest extends TestCase
{
    const ID_FROM_XDEBUG = 'fromXDebug';
    const ID_FROM_STORAGE = 'fromStorage';

    public function tearDown()
    {
        m::close();
    }

    public function testIterativeAccess()
    {
        $coverageData = CoverageData::createFromXDebug($this->getXDebugCoverageDataMock(), $this->reposRootDir(), self::ID_FROM_XDEBUG);

        $data = array();
        foreach ($coverageData as $file => $lines) {
            $data[$file] = $lines;
        }

        $this->assertCount(2, $data);
        $this->assertEquals(array(
            1 => array(self::ID_FROM_XDEBUG),
            2 => array(self::ID_FROM_XDEBUG),
            3 => array(self::ID_FROM_XDEBUG),
        ), $data[$this->fixtures['file1']['storageKey']]);
        $this->assertEquals(array(
            10 => array(self::ID_FROM_XDEBUG),
            11 => array(self::ID_FROM_XDEBUG),
            12 => array(self::ID_FROM_XDEBUG),
            13 => array(self::ID_FROM_XDEBUG),
            14 => array(self::ID_FROM_XDEBUG),
        ), $data[$this->fixtures['file2']['storageKey']]);
    }

    public function testCreateFromXDebug()
    {
        $coverageData = CoverageData::createFromXDebug($this->getXDebugCoverageDataMock(), $this->reposRootDir(), self::ID_FROM_XDEBUG);
        $data = $coverageData->getData();

        $this->assertCount(2, $data);
        $this->assertEquals(array(
            1 => array(self::ID_FROM_XDEBUG),
            2 => array(self::ID_FROM_XDEBUG),
            3 => array(self::ID_FROM_XDEBUG),
        ), $data[$this->fixtures['file1']['storageKey']]);
        $this->assertEquals(array(
            10 => array(self::ID_FROM_XDEBUG),
            11 => array(self::ID_FROM_XDEBUG),
            12 => array(self::ID_FROM_XDEBUG),
            13 => array(self::ID_FROM_XDEBUG),
            14 => array(self::ID_FROM_XDEBUG),
        ), $data[$this->fixtures['file2']['storageKey']]);

        return $coverageData;
    }

    public function testCreateFromStorage()
    {
        $coverageData = CoverageData::createFromStorage($this->getStorageMock());
        $data = $coverageData->getData();

        $this->assertCount(2, $data);
        $this->assertEquals(array(
            3 => array(self::ID_FROM_STORAGE),
            4 => array(self::ID_FROM_STORAGE),
            5 => array(self::ID_FROM_STORAGE),
        ), $data[$this->fixtures['file1']['storageKey']]);
        $this->assertEquals(array(
            10 => array(self::ID_FROM_STORAGE),
            11 => array(self::ID_FROM_STORAGE),
            12 => array(self::ID_FROM_STORAGE),
            13 => array(self::ID_FROM_STORAGE),
            14 => array(self::ID_FROM_STORAGE),
        ), $data[$this->fixtures['file2']['storageKey']]);

        return $coverageData;
    }

    /**
     * @depends testCreateFromXDebug
     * @depends testCreateFromStorage
     */
    public function testMerge(CoverageData $fromXDebug, CoverageData $fromStorage)
    {
        $fromStorage->merge($fromXDebug);
        $data = $fromStorage->getData();

        $this->assertCount(2, $data);
        $this->assertEquals(array(
            1 => array(self::ID_FROM_XDEBUG),
            2 => array(self::ID_FROM_XDEBUG),
            3 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
            4 => array(self::ID_FROM_STORAGE),
            5 => array(self::ID_FROM_STORAGE),
        ), $data[$this->fixtures['file1']['storageKey']]);
        $this->assertEquals(array(
            10 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
            11 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
            12 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
            13 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
            14 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
        ), $data[$this->fixtures['file2']['storageKey']]);

        return $fromStorage;
    }

    /**
     * @depends testMerge
     */
    public function testSave(CoverageData $coverageData)
    {
        $storage_mock = $this->getStorageMock();
        $storage_mock->shouldReceive('set')->with($this->fixtures['file1']['storageKey'], array(
            1 => array(self::ID_FROM_XDEBUG),
            2 => array(self::ID_FROM_XDEBUG),
            3 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
            4 => array(self::ID_FROM_STORAGE),
            5 => array(self::ID_FROM_STORAGE),
        ))->once();
        $storage_mock->shouldReceive('set')->with($this->fixtures['file2']['storageKey'], array(
            10 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
            11 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
            12 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
            13 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
            14 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
        ))->once();

        $coverageData->save($storage_mock);

        return $coverageData;
    }

    /**
     * @depends testSave
     */
    public function testGetPHP_CodeCoverageData(CoverageData $coverageData)
    {
        $data = $coverageData->getPHP_CodeCoverageData($this->reposRootDir());

        $this->assertEquals(array(
            $this->fixtures['file1']['path'] => array(
                1 => array(self::ID_FROM_XDEBUG),
                2 => array(self::ID_FROM_XDEBUG),
                3 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
                4 => array(self::ID_FROM_STORAGE),
                5 => array(self::ID_FROM_STORAGE),
            ),
            $this->fixtures['file2']['path'] => array(
                10 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
                11 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
                12 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
                13 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
                14 => array(self::ID_FROM_STORAGE, self::ID_FROM_XDEBUG),
            ),
        ), $data);
    }

    private function getXDebugCoverageDataMock()
    {
        return array(
            $this->fixtures['file1']['path'] => array(
                1 => 1,
                2 => 1,
                3 => 1,
            ),
            $this->fixtures['file2']['path'] => array(
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
        $storage = m::mock('CodeDetector\Detector\Storage\StorageInterface');
        $storage->shouldReceive('getAll')->andReturn(array(
            $this->fixtures['file1']['storageKey'] => array(
                3 => array(self::ID_FROM_STORAGE),
                4 => array(self::ID_FROM_STORAGE),
                5 => array(self::ID_FROM_STORAGE),
            ),
            $this->fixtures['file2']['storageKey'] => array(
                10 => array(self::ID_FROM_STORAGE),
                11 => array(self::ID_FROM_STORAGE),
                12 => array(self::ID_FROM_STORAGE),
                13 => array(self::ID_FROM_STORAGE),
                14 => array(self::ID_FROM_STORAGE),
            )
        ));

        return $storage;
    }
}
