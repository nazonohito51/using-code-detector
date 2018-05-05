<?php
namespace CodeDetector\Detector\CoverageData;

use CodeDetector\TestCase;
use Mockery as m;

class FileTest extends TestCase
{
    const ID1 = 'fromXDebug';
    const ID2 = 'fromStorage';

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testIsExist()
    {
        $existRealFile = new File($this->fixtures['file1']['path'], array(1 => self::ID1));
        $existRealFileHasHash = new File($this->fixtures['file1']['path'], array(1 => self::ID1), hash_file('md5', $this->fixtures['file1']['path']));
        $notExistFile = new File($this->fixtures['file1']['path'], array(1 => self::ID1), md5('hoge'));

        $this->assertTrue($existRealFile->isExist());
        $this->assertTrue($existRealFileHasHash->isExist());
        $this->assertFalse($notExistFile->isExist());
    }

    public function testRealPath()
    {
        $existRealFile = new File($this->fixtures['file1']['path'], array(1 => self::ID1));
        $notExistFile = new File($this->fixtures['file1']['path'], array(1 => self::ID1), md5('hoge'));

        $this->assertTrue($existRealFile->isExist());
        $this->assertFalse($notExistFile->isExist());
    }

    public function testGetCoverage()
    {
        $file = new File($this->fixtures['file1']['path'], array(
            1 => array(self::ID1),
            2 => array(self::ID1),
            3 => array(self::ID1),
        ));

        $this->assertEquals(array(
            1 => array(self::ID1),
            2 => array(self::ID1),
            3 => array(self::ID1),
        ), $file->getCoverage());

        return $file;
    }

    /**
     * @depends testGetCoverage
     */
    public function testAppend(File $file)
    {
        $file->append(self::ID1, array(3, 4, 5));

        $this->assertEquals(array(
            1 => array(self::ID1),
            2 => array(self::ID1),
            3 => array(self::ID1),
            4 => array(self::ID1),
            5 => array(self::ID1),
        ), $file->getCoverage());

        return $file;
    }

    /**
     * @depends  testAppend
     */
    public function testAppendFile(File $file)
    {
        $appended = new File($this->fixtures['file1']['path'], array(
            5 => array(self::ID2),
            6 => array(self::ID2),
            7 => array(self::ID2),
        ));

        $file->appendFile($appended);

        $this->assertEquals(array(
            1 => array(self::ID1),
            2 => array(self::ID1),
            3 => array(self::ID1),
            4 => array(self::ID1),
            5 => array(self::ID1, self::ID2),
            6 => array(self::ID2),
            7 => array(self::ID2),
        ), $file->getCoverage());
    }

    public function testSave()
    {
        $storageMock = m::mock('CodeDetector\Detector\Storage\StorageInterface');
        $storageMock->shouldReceive('set')->with($this->fixtures['file1']['storageKey'], serialize(array(
            1 => array(self::ID1),
            2 => array(self::ID1),
            3 => array(self::ID1),
        )))->once();

        $file = new File($this->fixtures['file1']['path'], array(
            1 => array(self::ID1),
            2 => array(self::ID1),
            3 => array(self::ID1),
        ));

        $file->save($storageMock, $this->reposRootDir());
    }

    public function testBuildCollectionFromStorage()
    {
        $storageMock = m::mock('CodeDetector\Detector\Storage\StorageInterface');
        $storageMock->shouldReceive('getAll')->andReturn(array(
            $this->fixtures['file1']['storageKey'] => serialize(array(
                3 => array(self::ID1),
                4 => array(self::ID1),
                5 => array(self::ID1),
            )),
            $this->fixtures['file2']['storageKey'] => serialize(array(
                10 => array(self::ID1),
                11 => array(self::ID1),
                12 => array(self::ID1),
                13 => array(self::ID1),
                14 => array(self::ID1),
            ))
        ));

        $files = File::buildCollectionFromStorage($storageMock, $this->reposRootDir());

        $this->assertArrayHasKey($this->fixtures['file1']['path'], $files);
        $this->assertEquals(array(
            3 => array(self::ID1),
            4 => array(self::ID1),
            5 => array(self::ID1),
        ), $files[$this->fixtures['file1']['path']]->getCoverage());
        $this->assertArrayHasKey($this->fixtures['file2']['path'], $files);
        $this->assertEquals(array(
            10 => array(self::ID1),
            11 => array(self::ID1),
            12 => array(self::ID1),
            13 => array(self::ID1),
            14 => array(self::ID1),
        ), $files[$this->fixtures['file2']['path']]->getCoverage());
    }
}
