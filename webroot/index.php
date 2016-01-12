<?php

/* Example with Anax-MVC */

require __DIR__.'/config_with_app.php';

$di->setShared("cache", function () {
    $cache = new \Anax\Cache\CCachePool();
    return $cache;
});

$app = new \Anax\MVC\CApplicationBasic($di);

$app->url->setUrlType(\Anax\Url\CUrl::URL_CLEAN);

//root
$app->router->add('cache', function () use ($app) {
    // Start the session
    $app->session();

    // Set a fitting title
    $app->theme->setTitle('Cache example');

    $clearCache = $app->request->getGet('clear-cache');

    // A check if the cache should be cleared
    if (isset($clearCache)) {
        $app->cache->clear();
    }

    // A file to load
    $fileName = 'README.md';

    // Generates a key, the value sent must be unique to avoid collisions
    $key = $app->cache->generateKey($fileName);

    // Retrieve the cache item from the pool using the generated key
    $cacheItem = $app->cache->getItem($key);

    // If the cacheItem is hit, use its content, otherwise the content is saved
    if ($cacheItem->isHit()) {
        // For debug
        $debug = "The cache item was found and used.";

        // The cache item was found
        $content = $cacheItem->get();
    } else {
        // The cache item was not found
        $content = $app->fileContent->get($fileName);
        $content = $app->textFilter->doFilter($content, 'shortcode, markdown');

        // Set the value of the item
        $cacheItem->set($content);

        // Set the lifetime of the cache (optional), 10 seconds
        $cacheItem->expiresAfter(10);

        //Checks if the cache should be saved and saves it if true
        if (!isset($clearCache)) {
            $app->cache->save($cacheItem);
            $debug = "The cache item was not found and was therefore saved.";
        } else {
            $debug = "The cache item was not found and was not saved because clear-cache is set.";
        }
    }

    // Save the cache session
    $app->cache->saveInSession(true);

    // Add a view with the results
    $app->views->add('cache/example', [
        'debug' => $debug,
        'content' => $content,
        'file' => $fileName
    ]);
});


// Check for matching routes and dispatch to controller/handler of route
$app->router->handle();

// Render the page
$app->theme->render();
