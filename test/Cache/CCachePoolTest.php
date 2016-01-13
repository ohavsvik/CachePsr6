<?php

namespace Anax\Cache;

class CCachePoolTest extends \PHPUnit_Framework_TestCase
{

    public function testHasItem()
    {
        $cache = new \Anax\Cache\CCachePool();

        $res = $cache->hasItem("random key");

        $this->assertFalse($res, "The item should not exist in the cache");
    }
}
