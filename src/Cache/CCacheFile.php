<?php

namespace Anax\Cache;

/**
 *
 */
class CCacheFile implements \Psr\Cache\CacheItemInterface
{
    /**
     * The key associated with the object
     * @var string
     */
    private $key;

    /**
     * The expiration time
     * @var \DateTime
     */
    public $expiration;

    /**
     * The default expiration time
     * @var string
     */
    public $defaultExpiration;

    /**
     * The value associated with the object
     * @var mixed
     */
    private $value;

    /**
     * Determines the timezone used
     * @var \DateTimeZone
     */
    public $timeZone;


    /**
     * Constructor
     *
     * @param string       $key        The object key
     * @param mixed        $value      The object key
     * @param DateTime     $expiration The expiration date
     * @param DateTimeZone $timeZone   The timezone used with the date
     */
    public function __construct($key, $value = null, $expiration = null, $timeZone = null)
    {
        $this->key = $key;
        $this->value = $value;

        $this->timeZone = is_null($timeZone) ?  new \DateTimeZone('Europe/London') : $timeZone;
        $this->defaultExpiration = '2999-12-12';

        $this->expiration = is_null($expiration) ?
            new \DateTime($this->defaultExpiration, $this->timeZone) : $expiration;
    }

    /**
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     *   The key string for this cache item.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the value for the current cache item.
     *
     * @return mixed The value for the current cache item
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Generate a filename for the cached object.
     *
     * @param string $key to the cached object.
     *
     * @return string The filename.
     */
    public function filename($key, $expiration = null)
    {
        if (is_null($expiration)) {
            return \Anax\Cache\CCachePool::getPath() . '/' . $key . '.val';
        }

        // return \Anax\Cache\CCachePool::getPath() . '/'. $expiration .'/' . $key;
        return \Anax\Cache\CCachePool::getPath() . '/' . $key . '.meta';
    }

    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     *
     * The value returned must be identical to the value originally stored by set().
     *
     * If isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return mixed
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function get()
    {
        if ($this->isHit()) {
            $file = $this->filename($this->key);
            return unserialize(file_get_contents($file));
        } else {
            return null;
        }
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return bool
     *   True if the request resulted in a cache hit. False otherwise.
     */
    public function isHit()
    {
        $file = $this->filename($this->key);

        $now = new \DateTime("now", $this->timeZone);
        $hasExpired = ($now > $this->expiration) ? true : false;

        if (is_file($file) && $hasExpired === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets the value represented by this cache item.
     *
     * The $value argument may be any item that can be serialized by PHP,
     * although the method of serialization is left up to the Implementing
     * Library.
     *
     * @param mixed $value
     *   The serializable value to be stored.
     *
     * @return static
     *   The invoked object.
     */
    public function set($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Sets the timezone that should be used for the expiration time
     *
     * @param DateTimeZone $timeZone The timezone used for the expiration time
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param \DateTimeInterface $expiration
     *   The point in time after which the item MUST be considered expired.
     *   If null is passed explicitly, a default value MAY be used. If none is set,
     *   the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAt($expiration)
    {
        $this->expiration = $expiration;

        if (is_null($expiration)) {
            $this->expiration = new \DateTime($this->defaultExpiration, $this->timeZone);
        }

        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param int|\DateInterval $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAfter($time)
    {
        $now = new \DateTime("now", $this->timeZone);

        if (is_null($time)) {
            $this->expiration = new \DateTime($this->defaultExpiration, $this->timeZone);
        } else {
            $dateInterval = 'PT' . $time . 'S';
            $this->expiration = $now->add(new \DateInterval($dateInterval));
        }

        return $this;
    }
}
