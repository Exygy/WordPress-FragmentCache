=== Plugin Name ===
Contributors: dkexygy
Tags: cache, caching, performance, wpengine, advanced
Requires at least: 3.0.1
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Boost your page performance by caching individual page fragments. Works with logged-in users too! 

== Description ==

There are a lot of great caching plugins out there, but this one solves a very particular need that none of the rest are doing. It allows you to cache particular sections of your page, using a technique known as "fragment caching", which is readily available in other frameworks such as Ruby on Rails. The key benefit here: you can cache content for logged-in users! This is the main limitation of most other caching plugins, which is where the need for this solution came about. There are a couple of reasons this works:

* You will only be caching certain sections of the page. Some sections of the page may be the same whether you're logged in or out, so you can cache those universally.
* You can use a flag to determine whether you'd like to cache the content for logged-in users or anonymous users (the default). Note, just like any caching solution, this will _never_ work for customized content that is particular to each user! But, if your page has a section that displays one version of the content for _all_ logged-in users, and one version for anonymous users, then you can fragment cache that section of your page. 

Note: Enabling this plugin does not automatically do anything for you -- you will still have to edit your templates in order to take advantage. So this is not as much of a plug-n-play solution as some of the other cache plugins, this is more of an advanced plugin for people who know some basic PHP and how to edit their template code.


== Installation ==

1. Install FragmentCache either via the WordPress.org plugin directory, or by uploading the files to your server
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Edit your template(s) to include FragmentCache like so:


```php
<?php
$cache = new FragmentCache();
if ( ! $cache->output() ) {     
  functions_that_do_stuff_live();
  these_should_echo();
  // IMPORTANT
  $cache->store();
}
?>      
```

To wrap this around a general PHP/HTML template you could use:

```php
$cache = new FragmentCache();
if ( ! $cache->output() ):     
  <div>
    <?php output_expensive_queries(); ?>
  </div>

<?php
  $cache->store();
endif;
?>      
```


== Frequently Asked Questions ==

= How do I store multiple fragment caches on the same page (or share a fragment cache across multiple pages)? =

By default, calling `new FragmentCache()` will create a key based on the current permalink URL (e.g. '/posts/12345'). To get around that, you'll have to pass a unique caching key like so:

```php
$cache = new FragmentCache(array('key' => 'my-unique-key')); //uses unique key. NOTE: this key is global across your whole site -- so don't use the same key on a different fragment!
```


= How is the cache stored? =

Right now it is using a file-based cache system, meaning it will store .txt files in wp-content/cache. 

= How does the cache expire? =

The cache is based on a date that you pass in, or the current page's last updated date, by default. Meaning if the page gets updated, the cache will be invalidated, and a new fragment cache will be generated on the next page load. Old cache files never get deleted automatically, you have to do this through the WP Admin plugin settings page. 

== Changelog ==

= 0.5 =
* First version of the plugin.


