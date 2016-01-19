<?php

namespace Anax\Cache;

class CCachePoolTest extends \PHPUnit_Framework_TestCase
{
    public $cache;

    /**
     * @before
     */
    public function setup()
    {
        $this->cache = new \Anax\Cache\CCachePool();
    }

    public function testHasItem()
    {
        $key = "key";
        $res = $this->cache->hasItem($key);
        $this->assertFalse($res, "The item should not exist in the cache");

        $item = $this->cache->getItem($key);
        $this->cache->save($item);
        $res2 = $this->cache->hasItem($key);
        $this->assertTrue($res2, "The item should exist in the cache");
    }

    public function testGenerateKey()
    {
        $this->cache = new \Anax\Cache\CCachePool();
        $res = $this->cache->generateKey("key");

        $this->assertInternalType('string', $res, "The key should be a string");
    }

    public function testGetItem()
    {
        $this->cache = new \Anax\Cache\CCachePool();

        $key = $this->cache->generateKey("key");
        $res = $this->cache->getItem($key);
        $this->assertInstanceOf('\Anax\Cache\CCacheFile', $res, "Should be an instance of \Anax\Cache\CCacheFile");
    }

    public function testClearCache()
    {
        $this->cache = new \Anax\Cache\CCachePool();
        $test = $this->cache->getItem("key");
        $this->cache->save($test);
        $res = $this->cache->clear();
        $this->assertTrue($res, "Should return true");
    }

    public function testDeleteGet()
    {
        $this->cache = new \Anax\Cache\CCachePool();
        $keys = ['key1', 'key2'];
        $items = [$this->cache->getItem($keys[0])];

        $this->cache->save($items[0]);

        $res = $this->cache->deleteItem($keys[0]);
        $this->assertTrue($res, "The item should be deleted and return true");

        $items = $this->cache->getItems($keys);

        $this->assertCount(2, $items, "The array length should be 2");

        $this->cache->save($items[$keys[0]]);
        $this->cache->save($items[$keys[1]]);

        $res = $this->cache->deleteItems($keys);

        $this->assertTrue($res, "The items should be deleted and return true");
    }

    public function testDeleteEmpty()
    {
        $res = $this->cache->deleteItem('key');
        $this->assertFalse($res, "Should not find an item to delete.");

        $res = $this->cache->deleteItems(['key1', 'key2']);
        $this->assertFalse($res, "Should not find items to delete.");
    }

    /**
     * @expectedException \Anax\Cache\InvalidKeyException
     */
    public function testInvalidKeyException()
    {
        $this->cache = new \Anax\Cache\CCachePool();
        $this->cache->checkKey("bw3{w3fg}3awt(aher)234fwe/avwrv\vawr@daw:deasd");
    }

    public function testSaveDefferedandCommit()
    {
        $item = $this->cache->getItem('key');
        $res = $this->cache->saveDeferred($item);

        $this->assertTrue($res, "Should be able to save deffered");

        $res = $this->cache->saveDeferred($item);

        $this->assertFalse($res, "Should already exist in the deffered array");

        $res = $this->cache->commit();
        $this->assertTrue($res, "Should be able to commit");
    }
}
