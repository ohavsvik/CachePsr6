[![Build Status](https://travis-ci.org/ohavsvik/CachePsr6.svg?branch=master)](https://travis-ci.org/ohavsvik/CachePsr6)

## A PSR-6 cache library


A cache library that follows the PSR-6 standard.

Run `webroot/cacheTest.php` on your localhost to try it.

### Using the library with Anax-MVC

- Install the `hav/cache` package via composer
- Move the folder `vendor/hav/cache/src/Cache` to your `app/src` folder

- Add the `hav/cache/webroot/index.php` file to your `webroot` folder
- Create folder named `Cache` in your `app/views` folder and move the `hav/cache/webroot/example.tpl.php` file to it

- Use the route `cache` to view the example
