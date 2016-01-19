<?php

namespace Anax\Cache;

/**
 *
 */
class CCachePool implements \Psr\Cache\CacheItemPoolInterface
{
    /**
     * An array with all cache items,
     * @var \Psr\Cache\CacheItemInterface[]
     */
    private $cacheItems;

    /**
     * An array of items to be saved later
     * @var \Psr\Cache\CacheItemInterface[]
     */
    private $defferedItems;

    /**
     * The path to the cache items
     * @var string
     */
    public $path;

    /**
     * Inits one array with the cached items and one with the deffered
     *
     */
    public function __construct()
    {
        //$this->cacheItems    = isset($_SESSION['cacheItems']) ? $_SESSION['cacheItems'] : [];
        $this->defferedItems = [];

        $this->path = __DIR__ . "/../../cacheitems/";

        // Creates a writable cache folder on the server
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        $this->cacheItems = array();
        $this->initCache();
    }

    /**
     * Used to fill the array with existing cache items
     *
     * @return void
     */
    private function initCache()
    {
        // Init the cacheitems
        $files = glob($this->path . '/*.val');
        $expirations = glob($this->path . '/*.meta');

        sort($files);
        sort($expirations);

        $count = COUNT($files);

        for ($i = 0; $i < $count; $i++) {
            $key = basename($files[$i]);
            $key = preg_replace('/\\.[^.\\s]{3,4}$/', '', $key);

            $expiration = unserialize(file_get_contents($expirations[$i]));
            $this->cacheItems[] = new \Anax\Cache\CCacheFile($key, null, $expiration);
        }
    }

    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return \Psr\Cache\CacheItemInterface
     *   The corresponding Cache Item.
     */
    public function getItem($key)
    {
        // Checks if the key is valid
        $this->checkKey($key);

        // Iterate all items to find the right match
        foreach ($this->cacheItems as $cacheItem) {
            if ($cacheItem->getKey() == $key) {
                return $cacheItem;
            }
        }

        // If the item was not found in the current array, a new item is created and added to the array
        $item = new \Anax\Cache\CCacheFile($key);
        $this->cacheItems[] = $item;

        return $item;
    }

    /**
     * Generates a key from a string.
     *
     * @param string $str
     *   The value to convert to a key-string, must be unique
     *
     * @return string
     *   A generated key-string
     */
    public function generateKey($str)
    {
        $strEncoded = base64_encode($str);
        return 'ch_' . md5($strEncoded);
    }

    /**
     * Checks if a key string is valid, invalid values are {}()/\@:
     *
     * @param string $key
     * A string to ckeck, invalid values are {}()/\@:
     *
     * @throws InvalidArgumentException
     * If $key are not a legal value
     *
     * @return void
     */
    public function checkKey($key)
    {
        // The characters that are not legal/is invalid
        $invalidValues = "/[\/\{\}\(\)\@\:\.\\\]/";

        // Checks for matches in the key
        if (preg_match_all($invalidValues, $key, $matches)) {
            throw new \Anax\Cache\InvalidKeyException($matches);
        }
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys
     * An indexed array of keys of items to retrieve.
     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return array|\Traversable
     *   A traversable collection of Cache Items keyed by the cache keys of
     *   each item. A Cache item will be returned for each key, even if that
     *   key is not found. However, if no keys are specified then an empty
     *   traversable MUST be returned instead.
     */
    public function getItems(array $keys = array())
    {
        // Validating the keys
        foreach ($keys as $key) {
            $this->checkKey($key);
        }

        // Iterates all keys and gets the associated item
        $items = array();
        $nKeys = count($keys);
        for ($i=0; $i < $nKeys; $i++) {
            $items[(string) $keys[$i]] = $this->getItem($keys[$i]);
        }

        return $items;
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key
     *    The key for which to check existence.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *  True if item exists in the cache, false otherwise.
     */
    public function hasItem($key)
    {
        //Validates the key
        $this->checkKey($key);

        // is_file

        foreach ($this->cacheItems as $cacheItem) {
            if ($cacheItem->getKey() == $key) {
                return true;
            }
        }
        return false;
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     *   True if the pool was successfully cleared. False if there was an error.
     */
    public function clear()
    {
        $this->cacheItems = array();
        $this->defferedItems = array();

        $files = glob($this->path . '/*');

        if (!array_map('unlink', $files)) {
            return false;
        }

        return true;
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key
     *   The key for which to delete
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the item was successfully removed. False if there was an error.
     */
    public function deleteItem($key)
    {
        //Checks if the key is valid
        $this->checkKey($key);

        foreach ($this->cacheItems as $cacheItem) {
            if ($key == $cacheItem->getKey()) {
                $file = $cacheItem->filename($cacheItem->getKey());
                if (is_file($file)) {
                    unlink($file);
                    unset($cacheItem);
                } else {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param array $keys
     *   An array of keys that should be removed from the pool.

     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the items were successfully removed. False if there was an error.
     */
    public function deleteItems(array $keys)
    {
        //Checks if the keys are valid
        foreach ($keys as $key) {
            $this->checkKey($key);
        }

        foreach ($keys as $key) {
            if (!$this->deleteItem($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Persists a cache item immediately.
     *
     * @param \Psr\Cache\CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   True if the item was successfully persisted. False if there was an error.
     */
    public function save(\Psr\Cache\CacheItemInterface $item)
    {
        // Gets the filename
        $file = $item->filename($item->getKey());

        // Checks if the file write failes and serializes the value
        if (!file_put_contents($file, serialize($item->getValue()))) {
            return false;
        }

        $file = $item->filename($item->getKey(), 'expiration');

        // Checks if the file write failes and serializes the value
        if (!file_put_contents($file, serialize($item->expiration))) {
            return false;
        }

        return true;
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param \Psr\Cache\CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    public function saveDeferred(\Psr\Cache\CacheItemInterface $item)
    {
        // Checks if the item is already saved as deffered
        foreach ($this->defferedItems as $defferedItem) {
            if ($defferedItem->getKey() == $item->getKey()) {
                return false;
            }
        }

        // Adds the item to the array of deffered items and returns true if it was successfull
        if (array_push($this->defferedItems, $item)) {
            return true;
        }

        return false;
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit()
    {
        // Saves all items in $defferedItems
        foreach ($this->defferedItems as $item) {
            if (!$this->save($item)) {
                return false;
            }
        }

        // Resets the deffered array
        $this->defferedItems = array();
        return true;
    }
}
