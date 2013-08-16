<?php
/**
 * @package Fragment_Cache
 * @version 0.5
 */
/*
Plugin Name: Fragment Cache
Plugin URI: http://wordpress.org/extend/plugins/
Description: Boost your page performance by caching individual page fragments. Works with logged-in users too! 
Author: Dave Kaplan
Version: 0.5
Author URI: http://exygy.com/
*/

/*
---- File-based Fragment Caching -----
adapted from https://gist.github.com/markjaquith/2653957#file-gistfile1-aw

Usage:
  $cache = new FragmentCache( $options );
  if ( ! $cache->output() ) { // testing for a value of false
    functions_that_do_stuff_live();
    these_should_echo();
    // IMPORTANT
    $cache->store();
    // YOU CANNOT FORGET THIS. If you do, the site will break.
  }
*/
 

class FragmentCache {
  const GROUP = 'fragment-cache';
  var $filesystem = true; 
  var $key;

  public function __construct( $options=array() ) {
    // object cache is UNTESTED! disabled for now
    // global $_wp_using_ext_object_cache;
    // if ($_wp_using_ext_object_cache) $this->filesystem = false; 

    if ($this->filesystem) {
      if ( ! file_exists(ABSPATH.'wp-content/cache/fragment-cache')) {
        wp_mkdir_p(ABSPATH.'wp-content/cache/fragment-cache');
      }      
    }

    $date       = isset($options['date']) ? $options['date'] : null;
    $key        = isset($options['key']) ? $options['key'] : self::page_cache_key($date);
    $logged_in  = isset($options['logged_in']) ? intval($options['logged_in']) : 0; //convert true,false to 1,0 

    $fullkey = "{$key}__{$logged_in}";

    if ($this->filesystem) {
      $this->key = "wp-content/cache/fragment-cache/$fullkey.txt";    
    } else {
      $this->key = $fullkey;
    }
  }

  public static function page_cache_key($date=null) {
    $url = parse_url(get_permalink());
    $path = $url['path'];
    $path =  rtrim(substr($path, 1), '/');
    // replace '/' with '-'
    $path = preg_replace('[\/]', '__', $path);

    if ($path == '') $path = 'home';

    if ($date) {
      $date = date('Ymd_His', strtotime($date));
    } else {
      $date = get_the_date('Ymd_His');
    }

    $path .= '__'.$date;
    return $path;   
  }

  public static function flush() {
    if ($this->filesystem) {
      $files = glob(ABSPATH.'wp-content/cache/fragment-cache/*'); // get all file names
      foreach ($files as $file) { // iterate files
        if (is_file($file)) unlink($file); // delete file
      }      
    } else {
      wp_cache_flush();
    }
  }


  public function output($echo=true) {
    $cached = false; 
    if ($this->filesystem && (file_exists($this->key))) {
      $cached = file_get_contents($this->key);
    } else {
      $cached = wp_cache_get($this->key, self::GROUP);
    }

    if ($cached) {
      // cache was found...
      if ($echo) echo $cached . "\n <!-- Serving FragmentCache from: {$this->key} --> \n";
      return true;
    } else {
      ob_start();
      return false;
    }
  }
 
  public function store() {
    $output = ob_get_flush(); // Flushes the buffers
    if ($this->filesystem) {
      $fh = fopen($this->key, 'w'); //or die("can't open file");
      fwrite($fh, $output);
      fclose($fh);      
    } else {
      wp_cache_add($this->key, $output, self::GROUP, 3600*48);    
    }
    return true; 
  }
}


function plugin_menu() {
  add_submenu_page('plugins.php', __('Clear FragmentCache'), __('Clear FragmentCache'), 'manage_options', 'frag-cache-opts', 'fragment_cache_options');
}
add_action('admin_menu', 'plugin_menu');


function fragment_cache_options() {
?>
  <?php if ( !empty($_POST['submit'] ) ) : ?>
  <? FragmentCache::flush(); ?>
  <div id="message" class="updated fade"><p><strong><?php _e('Cache has been cleared!') ?></strong></p></div>
  <? endif; ?>

  <form action="" method="post" id="frag-cache">
    <h1>Fragment Cache options:</h1>
    <input type="submit" name="submit" value="Clear the cache" />

  </form>

<?
}


function fragment_cache_plugin_action_links( $links, $file ) {
  if ( $file == plugin_basename( dirname(__FILE__).'/fragment_cache.php' ) ) {
    $links[] = '<a href="' . admin_url( 'admin.php?page=frag-cache-opts' ) . '">'.__( 'Settings' ).'</a>';
  }

  return $links;
}

add_filter( 'plugin_action_links', 'fragment_cache_plugin_action_links', 10, 2 );


?>