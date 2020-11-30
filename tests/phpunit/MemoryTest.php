<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\SimpleCache\Tests;


class MemoryTest extends \PHPUnit\Framework\TestCase
{


    /**
     * @var Rundiz\SimpleCache\Drivers\Memory
     */
    protected $SimpleCache;


    public function setup(): void
    {
        $this->SimpleCache = new \Rundiz\SimpleCache\Drivers\Memory();
    }// setup


    public function tearDown(): void
    {
        $this->SimpleCache->clear();
    }// tearDown


    public function testClear()
    {
        $this->SimpleCache->set('string', 'Hello world');
        $this->SimpleCache->set('string2', 'Hello world 2');

        $clearResult = $this->SimpleCache->clear();
        $this->assertTrue($clearResult);
    }// testClear


    public function testDelete()
    {
        $this->SimpleCache->set('string', 'Hello world');
        $this->SimpleCache->set('string2', 'Hello world 2');

        $this->assertTrue($this->SimpleCache->delete('string'));
        $this->assertTrue($this->SimpleCache->has('string2'));
    }// testDelete


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
