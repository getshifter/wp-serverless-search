<?php
/**
* Plugin Name: WP Serverless Search
* Plugin URI: https://github.com/emaildano/wp-serverless-search
* Description: A static search plugin for WordPress.
* Version: v1.0.0
* Author: DigitalCube, Daniel Olson
* Author URI: https://digitalcube.jp
* License: GPL2
* Text Domain: wp-serverless-search
*/

/**
 * On Plugin Activation
 */

function wp_sls_search_install() {
  create_dir();
}

register_activation_hook( __FILE__, 'wp_sls_search_install' );

/**
 * On Plugin Deactivation
 */

function wp_sls_search_deactivate() {
  remove_dir();
}

register_deactivation_hook( __FILE__, 'wp_sls_search_deactivate' );

/**
 * On Plugin Uninstall
 */

function wp_sls_search_uninstall() {
  remove_dir();
}

register_uninstall_hook( __FILE__, 'wp_sls_search_uninstall' );


/**
 * Retrieve file and folder information
 */

function wp_get_search_index() {
  
  $upload_dir = wp_get_upload_dir(); // e.g.: /wp-content/uploads/
  $file_name = 'index.js';
  $folder_name = '/wp-sls-search/';
  $tmpfile_name = 'index.tmp';
  $basedir = $upload_dir['basedir'] . $folder_name;
  $baseurl = $upload_dir['baseurl'] . $folder_name;
  $file = $basedir . $file_name; // e.g.: /var/www/vhosts/example.com/wp-content/uploads/wp-sls-search/index.js
  $url = $baseurl . $file_name; // e.g.: https://example.com/wp-content/uploads/wp-sls-search/index.js
  $tmpfile = $basedir . $tmpfile_name; //e.g.: https://example.com/wp-content/uploads/wp-sls-search/index.tmp
  
  return [
    'file'    => $file,
    'basedir' => $basedir,
    'tmpfile' => $tmpfile,
    'url'     => $url,
  ];
}

/**
 * Create directory
 */

function create_dir() {
  
  $dirname = dirname(wp_get_search_index()['basedir'] . '.');

  if (!is_dir($dirname)) {
    mkdir($dirname, 0755, true);
  }
}

/**
 * Remove directory
 */

function remove_dir() {
  $dirname = dirname(wp_get_search_index()['basedir'] . '.');
  rmdir($dirname);
}

/**
 * Create index
 */

function create_index() {

  $args = [
    'post_type' => 'any',
    'post_status' => 'publish',
    'posts_per_page' => -1
  ];

  $query = new WP_Query( $args );
  $f = fopen( wp_get_search_index()['file'] , "w" );
  $content = "";

  while( $query->have_posts() ) : $query->the_post();

    $data = [
      'title' => get_the_title()
    ];

    $content .= '"' . $data['title'] . '",';

  endwhile;
  
  $content = "var data = [" . $content . "]";
  fwrite($f, $content);

  wp_reset_query();

  fclose($f);

}

add_action( 'admin_post_create_index', 'create_dir' );
add_action( 'admin_post_create_index', 'create_index' );

/**
 * Update index
 */

function update_index($id) {

    // Check autosave
     if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

    // Check permissions & remove action
    if ( !current_user_can('edit_post', $id) ) return;
    remove_action('save_post', 'update_index');

    // Restore action
    add_action('save_post', 'update_index');

    $replaced = false;
    $wp_sls_search = wp_get_search_index();
    $reading = fopen($wp_sls_search['file'], 'r');
    $writing = fopen($wp_sls_search['tmpfile'], 'w');

    while (!feof($reading)) {
      $line = fgets($reading);
      if (stristr( $line, '{"id":'.get_the_id($id) )) {
        $line = '{"id":'.get_the_id($id).',"title":"'.get_the_title($id).'"}' . PHP_EOL;
        $replaced = true;
      }
      fputs($writing, $line);
    }

    fclose($reading);
    fclose($writing);
    
    // Check or skip for changes.
    if ($replaced) {
      rename($wp_sls_search['tmpfile'], $wp_sls_search['file']);
    } else {
      unlink($wp_sls_search['tmpfile']);
    }
}

add_action('save_post', 'update_index');

/**
 * Admin Settings Menu
 */

function wp_sls_search() {
  add_options_page(
    'WP Serverless Search',
    'WP Serverless Search',
    'manage_options',
    'wp-sls-search',
    'wp_sls_search_options'
  );
}

add_action( 'admin_menu', 'wp_sls_search' );
require_once('lib/includes.php');

/*
* Scripts
*/

add_action('wp_enqueue_scripts', 'wp_sls_search_assets' );
add_action('admin_enqueue_scripts', 'wp_sls_search_assets' );

function wp_sls_search_assets() {
  
  $shifter_js = plugins_url( 'main/main.js', __FILE__ );
  $shifter_css = plugins_url( '/main/main.css', __FILE__ );
  $flexsearch_js = plugins_url( '/main/flexsearch.compact.js', __FILE__ );
  $data = wp_get_search_index()['url'];
  
  wp_register_script('wp-sls-search-js', $shifter_js, array( 'flexsearch-js', 'wp-sls-search-data' ), null, true);
  wp_enqueue_script('wp-sls-search-js');

  wp_register_script('flexsearch-js', $flexsearch_js, null, null, true);
  wp_enqueue_script('flexsearch-js');

  wp_register_script('wp-sls-search-data', $data, null, null, true);
  wp_enqueue_script('wp-sls-search-data');

  wp_register_style("wp-sls-search-css", $shifter_css);
  wp_enqueue_style("wp-sls-search-css");

}