<?php
/*
Plugin Name: WP Serverless Search
Author: Daniel Olson
Author URI: https://github.com/emaildano/wp-serverless-search
Description: Serverless WordPress Search
*/

/*
 * Scripts
 */
add_action('wp_enqueue_scripts', 'wp_sls_search_js' );
add_action('admin_enqueue_scripts', 'wp_sls_search_js' );

function wp_sls_search_js() {
  
  $shifter_js = plugins_url( 'main/main.js', __FILE__ );
  
  wp_register_script('wp-sls-search-js', $shifter_js, array( 'jquery' ));
  wp_localize_script('wp-sls-search-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

  wp_register_script( 'fusejs', 'https://cdnjs.cloudflare.com/ajax/libs/fuse.js/3.2.1/fuse.min.js', null, null, false );
  wp_localize_script('fusejs', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

  wp_register_script( 'micromodal', 'https://unpkg.com/micromodal@0.3.2/dist/micromodal.min.js', null, null, true );
  wp_localize_script('micromodal', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

  wp_enqueue_script('wp-sls-search-js');
  wp_enqueue_script('micromodal');
  // wp_enqueue_script('fiber');
  // wp_enqueue_script('lodash');
  wp_enqueue_script('fusejs');

} 