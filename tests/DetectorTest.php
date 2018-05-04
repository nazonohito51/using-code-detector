<?php
namespace CodeDetector;

use PHPUnit\Framework\TestCase;
use Mockery as m;

class DetectorTest extends TestCase
{
    const ID1 = 'id1';
    const ID2 = 'id2';

    private function fixtures()
    {
        // In php5.3 and php5.4, expression in default value(for property and constant) is not allowed.
        return array(
            'file1' => array(
                'path' => __DIR__ . '/fixtures/hoge.php',
                'storageKey' => Detector::STORAGE_KEY_PREFIX . ":tests/fixtures/hoge.php:7790190cbd3eba546205c88ce0682472"
            ),
            'file2' => array(
                'path' => __DIR__ . '/fixtures/directory/fuga.php',
                'storageKey' => Detector::STORAGE_KEY_PREFIX . ":tests/fixtures/directory/fuga.php:65ac6a57264dcf93c28bbaf87660fce7"
            ),
            'file3' => array(
                'path' => __DIR__ . '/fixtures/directory/piyo.php',
                'storageKey' => Detector::STORAGE_KEY_PREFIX . ":tests/fixtures/directory/piyo.php:478de0143325e325388a60c6935981b8"
            )
        );
    }

    public function tearDown()
    {
        m::close();
    }

    public function coverageDataProvider()
    {
        $fixtures = $this->fixtures();
        return array(
            'only_xdebug' => array(
                array(
                    $fixtures['file1']['path'] => array(
                        1 => 1,
                        2 => 1,
                        3 => 1,
                    ),
                    $fixtures['file2']['path'] => array(
                        4 => 1,
                        5 => 1,
                        6 => 1,
                    )
                ),
                array(),
                array(
                    $fixtures['file1']['path'] => array(
                        1 => array(self::ID1),
                        2 => array(self::ID1),
                        3 => array(self::ID1),
                    ),
                    $fixtures['file2']['path'] => array(
                        4 => array(self::ID1),
                        5 => array(self::ID1),
                        6 => array(self::ID1),
                    ),
                    $fixtures['file3']['path'] => array(
                    ),
                ),
            ),
            'only_storage' => array(
                array(),
                array(
                    $fixtures['file1']['storageKey'] => array(),
                    $fixtures['file2']['storageKey'] => array(
                        6 => array(self::ID2),
                        7 => array(self::ID2),
                        8 => array(self::ID2),
                    ),
                    $fixtures['file3']['storageKey'] => array(
                        10 => array(self::ID2),
                        11 => array(self::ID2),
                        12 => array(self::ID2),
                    ),
                ),
                array(
                    $fixtures['file1']['path'] => array(
                    ),
                    $fixtures['file2']['path'] => array(
                        6 => array(self::ID2),
                        7 => array(self::ID2),
                        8 => array(self::ID2),
                    ),
                    $fixtures['file3']['path'] => array(
                        10 => array(self::ID2),
                        11 => array(self::ID2),
                        12 => array(self::ID2),
                    ),
                ),
            ),
            'both' => array(
                array(
                    $fixtures['file1']['path'] => array(
                        1 => 1,
                        2 => 1,
                        3 => 1,
                    ),
                    $fixtures['file2']['path'] => array(
                        4 => 1,
                        5 => 1,
                        6 => 1,
                    )
                ),
                array(
                    $fixtures['file1']['storageKey'] => array(),
                    $fixtures['file2']['storageKey'] => array(
                        6 => array(self::ID2),
                        7 => array(self::ID2),
                        8 => array(self::ID2),
                    ),
                    $fixtures['file3']['storageKey'] => array(
                        10 => array(self::ID2),
                        11 => array(self::ID2),
                        12 => array(self::ID2),
                    ),
                ),
                // TODO: expected is storageKey
                array(
                    $fixtures['file1']['path'] => array(
                        1 => array(self::ID1),
                        2 => array(self::ID1),
                        3 => array(self::ID1),
                    ),
                    $fixtures['file2']['path'] => array(
                        4 => array(self::ID1),
                        5 => array(self::ID1),
                        6 => array(self::ID2, self::ID1),
                        7 => array(self::ID2),
                        8 => array(self::ID2),
                    ),
                    $fixtures['file3']['path'] => array(
                        10 => array(self::ID2),
                        11 => array(self::ID2),
                        12 => array(self::ID2),
                    ),
                ),
            )
        );
    }

    /**
     * @dataProvider coverageDataProvider
     */
    public function testStop($fromXDebug, $fromStorage, $expected)
    {
        $fixtures = $this->fixtures();

        $driverMock = m::mock('CodeDetector\Detector\Driver');
        $driverMock->shouldReceive('start');
        $driverMock->shouldReceive('stop')->andReturn($fromXDebug);

        $storageMock = m::mock('CodeDetector\Detector\Storage\StorageInterface');
        $storageMock->shouldReceive('getAll')->andReturn($fromStorage);
        $storageMock->shouldReceive('set')->with($fixtures['file1']['storageKey'], $expected[$fixtures['file1']['path']]);
        $storageMock->shouldReceive('set')->with($fixtures['file2']['storageKey'], $expected[$fixtures['file2']['path']]);
        $storageMock->shouldReceive('set')->with($fixtures['file3']['storageKey'], $expected[$fixtures['file3']['path']]);

        $detector = new Detector(__DIR__ . '/../', $driverMock, $storageMock);
        $detector->start(self::ID1);
        $detector->stop();
    }
    
    public function testGetData()
    {
        $fixtures = $this->fixtures();

        $driverMock = m::mock('CodeDetector\Detector\Driver');

        $storageMock = m::mock('CodeDetector\Detector\Storage\StorageInterface');
        $storageMock->shouldReceive('getAll')->andReturn(array(
            $fixtures['file1']['storageKey'] => array(
                1 => array(self::ID1),
                2 => array(self::ID1),
                3 => array(self::ID1),
            ),
            $fixtures['file2']['storageKey'] => array(
                4 => array(self::ID1),
                5 => array(self::ID1),
                6 => array(self::ID2, self::ID1),
                7 => array(self::ID2),
                8 => array(self::ID2),
            ),
            $fixtures['file3']['storageKey'] => array(
                10 => array(self::ID2),
                11 => array(self::ID2),
                12 => array(self::ID2),
            ),
        ));

        $detector = new Detector(__DIR__ . '/../', $driverMock, $storageMock);
        $data = $detector->getData();

        $this->assertEquals(array(
            1 => array(self::ID1),
            2 => array(self::ID1),
            3 => array(self::ID1),
        ), $data[$fixtures['file1']['path']]);
        $this->assertEquals(array(
            4 => array(self::ID1),
            5 => array(self::ID1),
            6 => array(self::ID2, self::ID1),
            7 => array(self::ID2),
            8 => array(self::ID2),
        ), $data[$fixtures['file2']['path']]);
        $this->assertEquals(array(
            10 => array(self::ID2),
            11 => array(self::ID2),
            12 => array(self::ID2),
        ), $data[$fixtures['file3']['path']]);
    }
}
