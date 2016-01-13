<?php

// Used for loadeing the classes
require "../autoloader.php";

// Start a session
session_start();

/**
 * Small example how to cache works
 *
 */

// Create the cache
$cache = new \Anax\Cache\CCachePool();

// Generate a key from an unique string
$key = $cache->generateKey('unique');

// Retrieve the associated item
$item = $cache->getItem($key);

// Check if a value is saved in a file
if ($item->isHit()) {
    echo "The item was found in the cache, calling get() to retrieve content. </br>";

    // Retrieve the value
    $content = $item->get();
} else {
    echo "The item was not found in the cache, sets the item's value with set() saves the item with save(). </br>";

    // Otherwise, create the content and set the items value
    $content = "<p>HTML content  for cache</p> </br>";
    $item->set($content);

    // Save the item in the cache
    $cache->save($item);
}

// Uncomment to clear the cache
// $cache->clear();

echo "<p>Content: <p>";
echo $content;

// Tell the cache to save the session
$cache->saveInSession(true);
