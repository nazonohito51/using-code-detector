<?php
namespace CodeDetector;

use CodeDetector\Detector\CoverageData\File;
use Mockery as m;

class DetectorTest extends TestCase
{
    const ID1 = 'fromXDebug';
    const ID2 = 'fromStorage';

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function coverageDataProvider()
    {
        return array(
            'only_xdebug' => array(
                array(
                    $this->fixtures['file1']['path'] => array(
                        1 => 1,
                        2 => 1,
                        3 => 1,
                    ),
                    $this->fixtures['file2']['path'] => array(
                        4 => 1,
                        5 => 1,
                        6 => 1,
                    )
                ),
                array(),
                array(
                    $this->fixtures['file1']['storageKey'] => serialize(array(
                        'coverage' => array(
                            1 => array(self::ID1),
                            2 => array(self::ID1),
                            3 => array(self::ID1),
                        ),
                        'sample' => 1
                    )),
                    $this->fixtures['file2']['storageKey'] => serialize(array(
                        'coverage' => array(
                            4 => array(self::ID1),
                            5 => array(self::ID1),
                            6 => array(self::ID1),
                        ),
                        'sample' => 1
                    )),
                    $this->fixtures['file3']['storageKey'] => serialize(array(
                    )),
                ),
            ),
            'only_storage' => array(
                array(),
                array(
                    $this->fixtures['file1']['storageKey'] => serialize(array()),
                    $this->fixtures['file2']['storageKey'] => serialize(array(
                        'coverage' => array(
                            6 => array(self::ID2),
                            7 => array(self::ID2),
                            8 => array(self::ID2),
                        ),
                        'sample' => 1
                    )),
                    $this->fixtures['file3']['storageKey'] => serialize(array(
                        'coverage' => array(
                            10 => array(self::ID2),
                            11 => array(self::ID2),
                            12 => array(self::ID2),
                        ),
                        'sample' => 1
                    )),
                ),
                array(
                    $this->fixtures['file1']['storageKey'] => serialize(array(
                    )),
                    $this->fixtures['file2']['storageKey'] => serialize(array(
                        'coverage' => array(
                            6 => array(self::ID2),
                            7 => array(self::ID2),
                            8 => array(self::ID2),
                        ),
                        'sample' => 1
                    )),
                    $this->fixtures['file3']['storageKey'] => serialize(array(
                        'coverage' => array(
                            10 => array(self::ID2),
                            11 => array(self::ID2),
                            12 => array(self::ID2),
                        ),
                        'sample' => 1
                    )),
                ),
            ),
            'both' => array(
                array(
                    $this->fixtures['file1']['path'] => array(
                        1 => 1,
                        2 => 1,
                        3 => 1,
                    ),
                    $this->fixtures['file2']['path'] => array(
                        4 => 1,
                        5 => 1,
                        6 => 1,
                    )
                ),
                array(
                    $this->fixtures['file1']['storageKey'] => serialize(array()),
                    $this->fixtures['file2']['storageKey'] => serialize(array(
                        'coverage' => array(
                            6 => array(self::ID2),
                            7 => array(self::ID2),
                            8 => array(self::ID2),
                        ),
                        'sample' => 1
                    )),
                    $this->fixtures['file3']['storageKey'] => serialize(array(
                        'coverage' => array(
                            10 => array(self::ID2),
                            11 => array(self::ID2),
                            12 => array(self::ID2),
                        ),
                        'sample' => 1
                    )),
                ),
                array(
                    $this->fixtures['file1']['storageKey'] => serialize(array(
                        'coverage' => array(
                            1 => array(self::ID1),
                            2 => array(self::ID1),
                            3 => array(self::ID1),
                        ),
                        'sample' => 1
                    )),
                    $this->fixtures['file2']['storageKey'] => serialize(array(
                        'coverage' => array(
                            4 => array(self::ID1),
                            5 => array(self::ID1),
                            6 => array(self::ID2, self::ID1),
                            7 => array(self::ID2),
                            8 => array(self::ID2),
                        ),
                        'sample' => 1
                    )),
                    $this->fixtures['file3']['storageKey'] => serialize(array(
                        'coverage' => array(
                            10 => array(self::ID2),
                            11 => array(self::ID2),
                            12 => array(self::ID2),
                        ),
                        'sample' => 1
                    )),
                ),
            )
        );
    }

    /**
     * @dataProvider coverageDataProvider
     */
    public function testStop($fromXDebug, $fromStorage, $expected)
    {
        $driverMock = m::mock('CodeDetector\Detector\Driver');
        $driverMock->shouldReceive('start');
        $driverMock->shouldReceive('stop')->andReturn($fromXDebug);

        $storageMock = m::mock('CodeDetector\Detector\Storage\StorageInterface');
        $storageMock->shouldReceive('getAll')->andReturn($fromStorage);
        $storageMock->shouldReceive('set')->with($this->fixtures['file1']['storageKey'], $expected[$this->fixtures['file1']['storageKey']]);
        $storageMock->shouldReceive('set')->with($this->fixtures['file2']['storageKey'], $expected[$this->fixtures['file2']['storageKey']]);
        $storageMock->shouldReceive('set')->with($this->fixtures['file3']['storageKey'], $expected[$this->fixtures['file3']['storageKey']]);

        $detector = new Detector($this->reposRootDir(), $driverMock, $storageMock);
        $detector->start(self::ID1);
        $detector->stop();
    }

    public function testGetData()
    {
        $driverMock = m::mock('CodeDetector\Detector\Driver');

        $storageMock = m::mock('CodeDetector\Detector\Storage\StorageInterface');
        $storageMock->shouldReceive('getAll')->andReturn(array(
            $this->fixtures['file1']['storageKey'] => serialize(array(
                'coverage' => array(
                    1 => array(self::ID1),
                    2 => array(self::ID1),
                    3 => array(self::ID1),
                ),
                'sample' => 1
            )),
            $this->fixtures['file2']['storageKey'] => serialize(array(
                'coverage' => array(
                    4 => array(self::ID1),
                    5 => array(self::ID1),
                    6 => array(self::ID2, self::ID1),
                    7 => array(self::ID2),
                    8 => array(self::ID2),
                ),
                'sample' => 1
            )),
            $this->fixtures['file3']['storageKey'] => serialize(array(
                'coverage' => array(
                    10 => array(self::ID2),
                    11 => array(self::ID2),
                    12 => array(self::ID2),
                ),
                'sample' => 1
            )),
            File::STORAGE_KEY_PREFIX . ':not_exist:file' => serialize(array(
                'coverage' => array(
                    1 => array(self::ID1),
                ),
                'sample' => 1
            )),
        ));
        $storageMock->shouldReceive('del')->with(File::STORAGE_KEY_PREFIX . ':not_exist:file')->andReturn(true);

        $detector = new Detector($this->reposRootDir(), $driverMock, $storageMock);
        $data = $detector->getData();

        $this->assertEquals(array(
            1 => array(self::ID1),
            2 => array(self::ID1),
            3 => array(self::ID1),
        ), $data[$this->fixtures['file1']['path']]);
        $this->assertEquals(array(
            4 => array(self::ID1),
            5 => array(self::ID1),
            6 => array(self::ID2, self::ID1),
            7 => array(self::ID2),
            8 => array(self::ID2),
        ), $data[$this->fixtures['file2']['path']]);
        $this->assertEquals(array(
            10 => array(self::ID2),
            11 => array(self::ID2),
            12 => array(self::ID2),
        ), $data[$this->fixtures['file3']['path']]);
        $this->assertArrayNotHasKey('not_exist_file', $data);
    }
}
