<?php

namespace Anax\Cache;

class CCacheFileTest extends \PHPUnit_Framework_TestCase
{
    public $cache;

    /**
     * @before
     */
    public function setup()
    {
        $this->cache = new \Anax\Cache\CCachePool();
    }

    public function testGetHit()
    {
        $key = "unique";
        $item = $this->cache->getItem($key);
        $item->set("content");

        $res = $item->get();
        $this->assertNull($res, "Should not be able to find a value");

        $this->cache->save($item);
        $res = $item->get();
        $this->assertTrue(!is_null($res), "Should have a value");
    }

    public function testSetTimeZone()
    {
        $timeZone = new \DateTimeZone('Europe/Amsterdam');
        $item = $this->cache->getItem("timezone");

        $item->setTimeZone($timeZone);

        $this->assertEquals($item->timeZone, $timeZone);
    }

    public function testExpiresAt()
    {
        $timeZone = new \DateTimeZone('Europe/London');
        $item = $this->cache->getItem("expires");
        $item->setTimeZone($timeZone);

        $dateTime = new \DateTime('2020-12-10', $timeZone);
        $item->expiresAt($dateTime);

        $this->assertEquals($item->expiration->format("Y-m-d H:i:s"), $dateTime->format("Y-m-d H:i:s"), "Should have the set expiration date");

        $item->expiresAt(null);

        $compareDateTime = new \DateTime($item->defaultExpiration, $timeZone);

        $this->assertEquals($item->expiration->format("Y-m-d H:i:s"), $compareDateTime->format("Y-m-d H:i:s"), "Should have the default expiration date set in the class");
    }

    public function testExpiresAfter()
    {
        $timeZone = new \DateTimeZone('Europe/London');
        $item = $this->cache->getItem("expires");
        $item->setTimeZone($timeZone);

        $item->expiresAfter(10);
        
        $compareDateTime = new \DateTime("now", $timeZone);
        $compareDateTime = $compareDateTime->add(new \DateInterval("PT10S"));

        $this->assertEquals($item->expiration->format("Y-m-d H:i:s"), $compareDateTime->format("Y-m-d H:i:s"), "Should be equal");
    }
}
