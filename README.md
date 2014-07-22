GeekCache
=========

[![Build Status](https://travis-ci.org/karptonite/geekcache.svg)](https://travis-ci.org/karptonite/geekcache)

A wrapper for key/value storage with some nifty features

Installation
------------

GeekCache will be installable via Composer when it is released.

This package has one package dependency: it requires a dependency injection
container, and will work with either Pimple 1.1, or Laravel's container, which
can be installed separately from the laravel framework by adding
```"illuminate/container": "4.1.*"``` to your composer.json file.

geekcache also requires a key/value storage system for the back end. Currently,
GeekCache is implemented for only one system: Memcached, using either the
Memcache or Memcached PECL extension.

Usage
-----

To use GeekCache, it is most convenient to use the included service providers
to add the builders to the container.

```php
<?php
$container = new Illuminate\Container;
$msp = new GeekCache\Cache\MemcacheServiceProvider($container);
//or MemcacahedServiceProvider
$sp = new GeekCache\Cache\CacheServiceProvider($container);
$msp->register();
$sp->register();
```

If your Memcache server or servers are running anywhere other than the
localhost, port 11211, you can set them as follows:

```php
<?php
$container['geekcache.memcache.servers'] = array(
    '192.168.1.2' => array(11211),
    //additional servers
);
```

Once the service is registered, you can resolve the builder from the container.
The builder is the object you should inject into the constructor of a class
that will use caching. Its ```make()``` method returns a CacheItem. Here is a
simple usage example:

```php
<?php
$builder = $container['geekcache.cachebuilder'];

// this cacheitem will expire after 60 seconds
$cacheitem = $builder->make('cachekey', 60);

$cacheitem->put('cachevalue');

$result = $cacheitem->get();
// $result === 'cachevalue'

//this cache will never expire
$anothercache = $builder->make('anotherkey');

$result = $anothercache->get();
// $result === false, assuming there is no cache stored for that key

$cacheitem->delete();
$result = $cacheitem->get();
// $result=== false

```

The CacheItem has only three methods: get, put, and delete.  The builder is an
immutable object, and can be used to make multiple cache items with different
options.

Tags
----

Tags can be added to a CacheItem to allow all items tagged with the same tag to be
cleared together.

```php
<?php
$cacheitem = $builder->addTags('foo', 'bar')->make('cachekey');

$cacheitem->put('thevalue');

$result = $cacheitem->get();
// $result === 'thevalue'

// FIXME need an easier way to clear tags
$tagset = $container['tagsetfactory']->makeTagSet('bar');
$tagset->clearAll();

$result = $cacheitem->get();
// $result === false
```

When any tag is cleared, all cache items that had that tag added will be cleared as
well. Note that if you add tags to an item when you store a value, you must add
the same tags to the cache item when you want to retrieve the value.

Memoization
-----------

Memoization allows you to tell the cache item to store the value retrieved in a
local in-memory cache rather than going to the caching service for every
lookup. Ths can be useful if you have a cache item that may be looked up many times
on a given pageload. Memoization lasts only as long a a PHP process.

```php
<?php
// a value has previously been stored under the key "cachekey"
$cacheitem = $builder->memoize()->make('cachekey');

$result = $cacheitem->get();
//the result is gotten from the cache service

//later, in another part of the script

$cacheitem = $builder->memoize()->make('cachekey');

$result = $cacheitem->get();
// the result is immediately available without going to the cache service, 
// because it has been memoized
```

Regenerators
------------

Rather than putting data directly into cache, you can pass a callable into
```get()```. If the there is no value in the cache, the regenerator will be run,
and the result of that will be put into cache (and returned) if it does not return ```false```.

```php
<?php
$cacheitem = $builder->make('cachekey', 60);

$regenerator = function () {
    //Some code to generate the result, which is "foo"
    return $result;
}

$result = $cacheitem->get($regenerator);
// $result === 'foo'

$result = $cacheitem->get();
// $result is still 'foo'

$cacheitem->put('bar');

$result = $cacheitem->get($regenerator);
// result is 'bar', because we have put that value into cache, and there was no
// reason for the regenerator to run.
```

Soft invalidation and queued regeneration
-----------------------------------------

You can also use a regenerator to trigger some process to queue regeneration at
some other time, so that the user does not have to wait for a slow process to
generate data. If you do so, GeekCache will allow you to return stale data to
the user while the regenerator executes, as follows.

```php
<?php
$cacheitem = $builder->addTags('foo','bar')->make('cachekey');
$cacheitem->put('cachevalue');

$tagset = $container['tagsetfactory']->makeTagSet('bar');
$tagset->clearAll();
//at this point, $cacheitem->get() will return false

$regenerator = function () {
    // queue a process that will properly put the correct value into
    // the cache.
    return false;
}

$result = $cacheitem->get($regenerator);
// $result === 'cachevalue'. The regenerator has been executed, and
// the new value will be inserted into cache soon.
```

While this will get data that has been cleared because a tag has been cleaered,
by default the same is not true of data that has expired due to time. However,
you can set a grace period for your cache item, so that even data that has gone past
its expiration time is available when a regenerator that returns false is
passed.

```php
<?php
$cacheitem = $builder->addTags('foo', 'bar')->addGracePeriod(120)->make('cachekey', 60);

$cacheitem->put('cachevalue');

$regenerator = function () {
    // queue a process that will properly put the correct value into
    // the cache.
    return false;
}

// sleep some time between 60 and 180 seconds
$result = $cacheitem->get();
// $result is false. The grace period applies ONLY with a regenerator

$result = $cacheitem->get($regenerator);
// $result === 'cachevalue', and the $regenerator has been executed
// after another 120 seconds, the data would no longer be available from the
// cache service, because the grace period would expire
```

You can also set a grace period of 0, which indicates that the stale value
should ALWAYS be available when a $regenerator is passed, similar to setting an
expire value of 0 for Memcached.

Usage notes
-----------

You can safely cache any value or serializable data structure, with the
exception of the boolean false, which cannot be cached. If you need to cache a
value that may be false, I suggest serializing/unserializing the data. I'd like
to remove this limitation in a future version of this library.

A note regarding using these features together: Internally, the builder adds
features to the cache item in the order you call the add function. For this reason,
if you are going to memoize the cache item, call memoize first.

A grace period is useless unless you plan to pass in a regenerator which queues
a process for regeneration (and returns false).

A regenerator is guaranteed to be called only once for a given ```get()```. In
addition, a single parameter is passed to the regenerator: a boolean indicating
whether stale data is available to be returned. The intended purpose of this
boolean is so that the $regenerator can set the priority of the queued
process--high priority if blanks are being returned, low priority if stale data
is available.

Although a regenerator will be called only once for a ```get()```, if get is
called multiple times, or in multiple processes, a regenerator that is queuing
regeneration processes may be called multiple times. For this reason, I
recommmend a queuing system such as Gearman, which has functionality to
coalesce duplicate processes.

Configuration
-------------
Coming soon
