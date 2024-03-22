GeekCache
=========

[![Build Status](https://travis-ci.org/karptonite/geekcache.svg)](https://travis-ci.org/karptonite/geekcache)
[![Code Climate](https://codeclimate.com/github/karptonite/geekcache/badges/gpa.svg)](https://codeclimate.com/github/karptonite/geekcache)

GeeKCache is a wrapper for Memcached with tags, soft invalidation, memoization,
and regeneration through callbacks. It is free (MIT license), fully tested and
easily extendable. It also beta, so all disclaimers regarding using beta
software in production apply, especially regarding functional changes, but it
is in active use on http://boardgamegeek.com. 

Installation
------------

GeekCache can be installed via composer.

    {
        "require": {
            "geekcache/geekcache": "0.1.*@beta"
        }
    }

This package has one package dependency: the service providers requires
Laravel's dependency injection container.

GeekCache also requires a key/value storage system for the back end. Currently,
GeekCache is implemented for only one system: Memcached, using the
Memcached PECL extension.

Usage
-----

To use GeekCache, it is most convenient to use the included service providers
to add the builders to the container.

```php
<?php
$container = new Illuminate\Container\Container;
$msp = new GeekCache\Provider\MemcachedServiceProvider($container);
$sp = new GeekCache\Provider\CacheServiceProvider($container);
$msp->register();
$sp->register();
```

If your Memcache server or servers are running anywhere other than the localhost, port
11211, you can set them as described in the Configuration section, below.

Once the service is registered, you can resolve the builders and the clearer
from the container.  The builder is the object you should inject into the
constructor of a class that will use caching, while the clearer is used for
clearing multiple cache items at once. 
Here is a simple usage example:

```php
<?php
// the builder and/or the clearer should be injected into the constructor
// of the relevant classes, as needed.
$builder = $container['geekcache.cachebuilder'];
$clearer = $container['geekcache.clearer'];

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

$clearer->flush();
// ALL items are removed from cache

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


// to retrieve an item from cache, the cache item must be created with the same
// tags it was stored with
$anothercacheitem = $builder->addTags('foo', 'bar')->make('cachekey');

$result = $anothercacheitem->get();
// $result === 'thevalue'

$clearer->clearTags('bar');
// the clearer can be used to clear all items tagged with a given tag or tags

$result = $cacheitem->get();
// $result === false
```

When any tag is cleared, all cache items that had that tag added will be
cleared as well. Note that if you add tags to an item when you store a value,
you must add the same tags, in the same order, to the cache item when you want
to retrieve the value.

Memoization
-----------

Memoization allows you to tell the cache item to store the value retrieved in a
local in-memory cache rather than going to the caching service for every
lookup. Ths can be useful if you have a cache item that may be looked up many
times on a given pageload. Memoization lasts only as long as a PHP process.

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

$clearer->flushLocal()
// the contents of all local caches will be removed, but the persistent cache
// will be unchanged. This can be useful if a cache may have been changed by
// another process after it was gotten and memoized locally

```

Regenerators
------------

Rather than putting data directly into cache, you can pass a callable into
`get()`. If the there is no value in cache, the regenerator will be run,
and the result of that will be put into cache (and returned) if it does not
return `false`.

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

You can also use a regenerator to trigger a process to queue regeneration at
some other time, so that the user does not have to wait for a slow process to
generate data. If you do so, GeekCache will allow you to return stale data to
the user while the regenerator executes, as follows.

```php
<?php
$cacheitem = $builder->addTags('foo','bar')->make('cachekey');
$cacheitem->put('cachevalue');

$clearer->flushTags('foo');
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

While this will get data that has been cleared because a tag has been cleared,
by default the same is not true of data that has expired due to time. However,
you can set a grace period for your cache item, so that even data that has gone
past its expiration time is available when a regenerator that returns false is
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
expire value of 0 for Memcached. If you add a grace period when you save an
item, you must also include the grace period when you get the result. (I hope
to remove this limitation a future version of GeekCache.)

Counter
-------

A counter is a simple cache with the same methods as CacheItem, plus
`increment()`.  Incrementing is atomic; even if multiple processes increment at
the same time, the total will remain correct. It can be built similarly to how
the CacheItem is built (counters have their own builder), with the caveat that
the only option available is memoization--Tags or grace periods are not
available for counters, in order to more easily maintain atomic incrementing.

```php
<?php
$builder = $container['geekcache.counterbuilder'];
$counter = $builder->memoize->make('counterkey', 3600);

$counter->put(5);
$result = $counter->increment(2);
// $result === 7

$result = $counter->get();
// $result === 7

$result = $counter->increment(-5);
// $result === 2

$result = $counter->increment(-5);
// $result === 0, because counter will not go negative
```

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

A regenerator is guaranteed to be called only once for a given `get()`. In
addition, a single parameter is passed to the regenerator: a boolean indicating
whether stale data is available to be returned. The intended purpose of this
boolean is so that the $regenerator can set the priority of the queued
process--high priority if blanks are being returned, low priority if stale data
is available.

Although a regenerator will be called only once for a `get()`, if get is
called multiple times, or in multiple processes, a regenerator that is queuing
regeneration processes may be called multiple times. For this reason, I
recommmend a queuing system such as Gearman, which has functionality to
coalesce duplicate processes.

Configuration
-------------

GeekCache is configured via setting variables on the container--they must be
set before GeekCache is used, but need not be set before
the providers are registered.

```php
<?php

// defaults to false. If set to true, Memoization will go to a null cache. This should
// always be set to true for long-running php processes, such as queue workers, even 
// if you aren't explicitly using memoization.
$container['geekcache.nolocalcache'] = false; 

// Defaults to localhost, port 11211.
$container['geekcache.memcache.servers'] = array(
    '192.168.1.2' => array(11211),
    //additional servers
);

// The maximum number of items to memoize, for tags (which are always memoized
// internally) and memos. These are the default values.
$container['geekcache.maxlocal.tags']  = 5000;
$container['geekcache.maxlocal.memos'] = 1000;

// Defaults to unset. If set, all keys will be prefixed by this namespace.
// This is useful if more than one application shares the same memcache server.
// There is a bit of overhead with this, so best not to use a namespace unless
// you need one.
$container['geekcache.namespace'] = "key_prefix_";
```
