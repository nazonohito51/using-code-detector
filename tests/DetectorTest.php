<?php
namespace CodeDetector;

use PHPUnit\Framework\TestCase;
use Mockery as m;

class DetectorTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testStop()
    {
        $hoge = __DIR__ . '/fixtures/hoge.php';
        $fuga = __DIR__ . '/fixtures/directory/fuga.php';
        $piyo = __DIR__ . '/fixtures/directory/piyo.php';
        $hogeStorageKey = Detector::STORAGE_KEY_PREFIX . ":$hoge:7790190cbd3eba546205c88ce0682472";
        $fugaStorageKey = Detector::STORAGE_KEY_PREFIX . ":$fuga:65ac6a57264dcf93c28bbaf87660fce7";
        $piyoStorageKey = Detector::STORAGE_KEY_PREFIX . ":$piyo:478de0143325e325388a60c6935981b8";

        $driverMock = m::mock('CodeDetector\Detector\Driver');
        $driverMock->shouldReceive('start');
        $driverMock->shouldReceive('stop')->andReturn(array(
            $hoge => array(
                1 => 1,
                2 => 1,
                3 => 1,
            ),
            $fuga => array(
                4 => 1,
                5 => 1,
                6 => 1,
            )
        ));

        $storageMock = m::mock('CodeDetector\Detector\StorageInterface');
        $storageMock->shouldReceive('getAll')->andReturn(array(
            $fugaStorageKey => array(
                6 => array('id2'),
                7 => array('id2'),
                8 => array('id2'),
            ),
            $piyoStorageKey => array(
                10 => array('id2'),
                11 => array('id2'),
                12 => array('id2'),
            )
        ));
        $storageMock->shouldReceive('set')->with($hogeStorageKey, array(
            1 => array('id1'),
            2 => array('id1'),
            3 => array('id1'),
        ));
        $storageMock->shouldReceive('set')->with($fugaStorageKey, array(
            4 => array('id1'),
            5 => array('id1'),
            6 => array('id2', 'id1'),
            7 => array('id2'),
            8 => array('id2'),
        ));
        $storageMock->shouldReceive('set')->with($piyoStorageKey, array(
            10 => array('id2'),
            11 => array('id2'),
            12 => array('id2'),
        ));

        $detector = new Detector($driverMock, $storageMock);
        $detector->start('id1');
        $detector->stop();
    }
}
