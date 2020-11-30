<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\SimpleCache\Tests;


class FileSystemTest extends \PHPUnit\Framework\TestCase
{


    /**
     * @var string
     */
    protected $cachePath;


    /**
     * @var Rundiz\SimpleCache\Tests\FileSystemExtend
     */
    protected $SimpleCache;


    public function setup(): void
    {
        $this->cachePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cache';
        $this->SimpleCache = new FileSystemExtend($this->cachePath);
    }// setup


    public function tearDown(): void
    {
        if (is_dir($this->cachePath)) {
            if (function_exists('exec')) {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    exec('RMDIR /Q/S "' . $this->cachePath . '"');
                } else {
                    exec('rm -rf "' . $this->cachePath . '"');
                }
            }
        }
    }// tearDown


    public function testClear()
    {
        $this->SimpleCache->set('string', 'Hello world');
        $this->SimpleCache->set('string2', 'Hello world 2');

        $clearResult = $this->SimpleCache->clear();
        $this->assertTrue($clearResult);

        $iterator = new \FilesystemIterator($this->cachePath);
        $isDirEmpty = !$iterator->valid();
        $this->assertTrue($isDirEmpty);
    }// testClear


    public function testCreateFolderIfNotExists()
    {
        $this->assertTrue(is_dir($this->cachePath));// already create on `SimpleCache` class constructor.
        $this->SimpleCache->createFolderIfNotExists($this->cachePath . '/sub1/sub2');
        $this->assertTrue(is_dir($this->cachePath . '/sub1/sub2'));
    }// testCreateFolderIfNotExists


    public function testDelete()
    {
        $this->SimpleCache->set('string', 'Hello world');
        $this->SimpleCache->set('string2', 'Hello world 2');

        $this->assertTrue($this->SimpleCache->delete('string'));
        $this->assertTrue($this->SimpleCache->has('string2'));
    }// testDelete


    public function testDeleteCacheSubfolderRecursively()
    {
        $this->SimpleCache->createFolderIfNotExists($this->cachePath . '/sub1/sub2');
        file_put_contents($this->cachePath . '/sub1/sub2/test.txt', 'test');
        $this->assertTrue(is_dir($this->cachePath . '/sub1/sub2'));
        $this->assertTrue(is_file($this->cachePath . '/sub1/sub2/test.txt'));

        $this->SimpleCache->deleteCacheSubfolderRecursively($this->cachePath);
        $this->assertTrue(is_dir($this->cachePath));
        $this->assertFalse(is_dir($this->cachePath . '/sub1'));
    }// testDeleteCacheSubfolderRecursively


    public function testDeleteCacheSubfoldersIfEmpty()
    {
        $this->SimpleCache->createFolderIfNotExists($this->cachePath . '/sub1/sub2');
        $subPath = realpath($this->cachePath . '/sub1/sub2');
        $filePath = $subPath . DIRECTORY_SEPARATOR . 'test.txt';
        file_put_contents($filePath, 'test');
        $this->assertTrue(is_dir($subPath));
        $this->assertTrue(is_file($filePath));

        $result = $this->SimpleCache->deleteCacheSubfoldersIfEmpty($subPath);
        $this->assertFalse($result);// dir not empty.
        $this->assertTrue(is_file($filePath));// file still exists.

        unlink($filePath);
        $result = $this->SimpleCache->deleteCacheSubfoldersIfEmpty($subPath);
        $this->assertTrue($result);
        $this->assertFalse(is_file($filePath));
        $this->assertFalse(is_dir($subPath));// cache path/sub1/sub2 deleted
        $this->assertFalse(is_dir(dirname($subPath)));// cache path/sub1 deleted
        $this->assertTrue(is_dir(dirname(dirname($subPath))));// up to cache path (not deleted).
        $this->assertTrue(dirname(dirname($subPath)) === $this->cachePath);// make sure that it is really cache path
    }// testDeleteCacheSubfoldersIfEmpty


    public function testDeleteMultiple()
    {
        $TestClass = new \stdClass();
        $TestClass->saybye = 'Goobye world!';
        $TestClass->sayhi = 'Hello world.';

        $values = [
            'test.cachemultiple.string' => 'Hello โลก.',
            'test.cachemultiple.object' => $TestClass,
            'test.cachemultiple.array' => ['sayhi' => 'Hello สวัสดี', 'saybye' => 'Goodbye ลาก่อน', 'user' => ['name' => 'Vee W.', 'website' => 'http://rundiz.com'], 'object' => $TestClass],
        ];

        foreach ($values as $key => $item) {
            $this->SimpleCache->set($key, $item);
        }
        unset($item, $key);

        $this->assertTrue($this->SimpleCache->has('test.cachemultiple.string'));

        $deleteResult = $this->SimpleCache->deleteMultiple(['test.cachemultiple.string', 'test.cachemultiple.array']);
        $this->assertTrue($deleteResult);
        $this->assertFalse($this->SimpleCache->has('test.cachemultiple.string'));
        $this->assertTrue($this->SimpleCache->has('test.cachemultiple.object'));
        $this->assertEquals($TestClass, $this->SimpleCache->get('test.cachemultiple.object'));
    }// testDeleteMultiple


    public function testGet()
    {
        $TestClass = new \stdClass();
        $TestClass->saybye = 'Goobye world!';
        $TestClass->sayhi = 'Hello world.';

        $this->SimpleCache->set('string', 'Hello world');
        $this->SimpleCache->set('array', ['sayhi' => 'Hello สวัสดี', 'saybye' => 'Goodbye ลาก่อน', 'user' => ['name' => 'Vee W.', 'website' => 'http://rundiz.com']]);
        $this->SimpleCache->set('object', $TestClass);

        $this->assertEquals(['sayhi' => 'Hello สวัสดี', 'saybye' => 'Goodbye ลาก่อน', 'user' => ['name' => 'Vee W.', 'website' => 'http://rundiz.com']], $this->SimpleCache->get('array'));
        $this->assertEquals($TestClass, $this->SimpleCache->get('object'));
    }// testGet


    public function testGetMultiple()
    {
        $TestClass = new \stdClass();
        $TestClass->saybye = 'Goobye world!';
        $TestClass->sayhi = 'Hello world.';

        $values = [
            'test.cachemultiple.string' => 'Hello โลก.',
            'test.cachemultiple.object' => $TestClass,
            'test.cachemultiple.array' => ['sayhi' => 'Hello สวัสดี', 'saybye' => 'Goodbye ลาก่อน', 'user' => ['name' => 'Vee W.', 'website' => 'http://rundiz.com'], 'object' => $TestClass],
        ];

        foreach ($values as $key => $item) {
            $this->SimpleCache->set($key, $item);
        }
        unset($item, $key);

        $getMultiple = $this->SimpleCache->getMultiple(['test.cachemultiple.string', 'test.cachemultiple.object']);
        $this->assertArrayHasKey('test.cachemultiple.string', $getMultiple);
        $this->assertArrayHasKey('test.cachemultiple.object', $getMultiple);
        $this->assertArrayNotHasKey('test.cachemultiple.array', $getMultiple);
    }// testGetMultiple


    public function testHas()
    {
        $this->SimpleCache->set('string', 'Hello world');
        $this->assertFalse($this->SimpleCache->has('string2'));
        $this->assertTrue($this->SimpleCache->has('string'));
    }// testHas


    public function testKeyToPathAndFileName()
    {
        $this->assertStringStartsWith('mydir' . DIRECTORY_SEPARATOR . 'subdir1' . DIRECTORY_SEPARATOR . 'subdir2', $this->SimpleCache->keyToPathAndFileName('mydir.subdir1.subdir2'));
    }// testKeyToPathAndFileName


    public function testSanitizeFileName()
    {
        $this->assertSame('slashbackslashquestionAmpersand', $this->SimpleCache->sanitizeFileName('/slash\\backslash?question<Html>&Ampersand.'));
        $this->assertSame('slashbackslashquestionampersand', $this->SimpleCache->sanitizeFileName('/slash\\backslash?question<Html>&Ampersand.', true));
        $this->assertSame('Hello', $this->SimpleCache->sanitizeFileName('Helloภาษาไทย'));
    }// testSanitizeFileName


    public function testSet()
    {
        $this->assertTrue($this->SimpleCache->set('string', 'Hello world'));
    }// testSet


    public function testSetMultiple()
    {
        $TestClass = new \stdClass();
        $TestClass->saybye = 'Goobye world!';
        $TestClass->sayhi = 'Hello world.';

        $values = [
            'test.cachemultiple.string' => 'Hello โลก.',
            'test.cachemultiple.object' => $TestClass,
            'test.cachemultiple.array' => ['sayhi' => 'Hello สวัสดี', 'saybye' => 'Goodbye ลาก่อน', 'user' => ['name' => 'Vee W.', 'website' => 'http://rundiz.com'], 'object' => $TestClass],
        ];

        $result = $this->SimpleCache->setMultiple($values);
        $this->assertTrue($result);

        $getMultiple = $this->SimpleCache->getMultiple(['test.cachemultiple.string', 'test.cachemultiple.object']);
        $this->assertArrayHasKey('test.cachemultiple.string', $getMultiple);
        $this->assertArrayHasKey('test.cachemultiple.object', $getMultiple);
        $this->assertArrayNotHasKey('test.cachemultiple.array', $getMultiple);
    }// testSetMultiple


}
