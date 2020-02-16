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
  // trigger our function that registers the custom post type
  create_wp_sls_dir();
  create_search_feed();
}

add_action( 'init', 'create_wp_sls_dir' );
register_activation_hook( __FILE__, 'wp_sls_search_install' );

/**
 * Create WP SLS Dir
 */

function create_wp_sls_dir() {
  
  $upload_dir = wp_get_upload_dir();
  $save_path = $upload_dir['basedir'] . '/wp-sls/search/.';
  $dirname = dirname($save_path);

  if (!is_dir($dirname)) {
      mkdir($dirname, 0755, true);
  }
}

/**
 * Create Search Feed
 */

function create_search_feed() {

  $args = [
    'post_type' => 'any',
    'post_status' => 'publish',
    'posts_per_page' => 10
  ];

  $query = new WP_Query( $args );
  $posts = [];
  $upload_dir = wp_get_upload_dir();
  $file_name = 'data.json';
  $save_path = $upload_dir['basedir'] . '/wp-sls/search/' . $file_name;
  $f = fopen( $save_path , "w" );

  while( $query->have_posts() ) : $query->the_post();

    $data = [
      'id' => get_the_id(),
      'title' => get_the_title()
    ];

    $content = json_encode($data) . PHP_EOL;

    fwrite($f, $content);

  endwhile;

  wp_reset_query();

  fclose($f);

}

add_action( 'save_post', 'create_search_feed' ); 

/**
 * Set Plugin Defaults
 */

function wp_sls_search_default_options() {
    $options = array(
        'wp_sls_search_form' => '[role=search]',
        'wp_sls_search_form_input' => 'input[type=search]',
    );

    foreach ( $options as $key => $value ) {
        update_option($key, $value);
    }
}

if (!get_option('wp_sls_search_form')) {
  register_activation_hook(__FILE__, 'wp_sls_search_default_options');
}

/**
 * Admin Settings Menu
 */

add_action( 'admin_menu', 'wp_sls_search' );
function wp_sls_search() {
  add_options_page(
    'WP Serverless Search',
    'WP Serverless Search',
    'manage_options',
    'wp-sls-search',
    'wp_sls_search_options'
  );
}

require_once('lib/includes.php');

/*
* Scripts
*/

add_action('wp_enqueue_scripts', 'wp_sls_search_assets' );
add_action('admin_enqueue_scripts', 'wp_sls_search_assets' );

function wp_sls_search_assets() {

  $upload_dir = wp_get_upload_dir();
  $wp_sls_search_data = $upload_dir['baseurl'] . '/wp-sls/search/data.json';

  $wp_sls_search_js = plugins_url( 'main/main.js', __FILE__ );

  $search_params = array(
    'searchForm' => get_option('wp_sls_search_form'),
    'searchFormInput' => get_option('wp_sls_search_form_input')
  );
  
  wp_register_script('wp-sls-search-js', $wp_sls_search_js, array( 'jquery', 'micromodal', 'fusejs', 'flexsearch' ), null, true);
  wp_localize_script( 'wp-sls-search-js', 'searchParams', $search_params );
  wp_enqueue_script('wp-sls-search-js');

  wp_register_script('flexsearch', 'https://rawcdn.githack.com/nextapps-de/flexsearch/master/dist/flexsearch.min.js', null, null, true);
  wp_enqueue_script('flexsearch');

  wp_register_script('wp-sls-search-data', $wp_sls_search_data, array( 'jquery', 'micromodal', 'flexsearch' ), null, true);
  wp_enqueue_script('wp-sls-search-data');

  wp_register_script('micromodal', 'https://cdn.jsdelivr.net/npm/micromodal/dist/micromodal.min.js', null, null, true);
  wp_enqueue_script('micromodal');

  wp_register_style("wp-sls-search-css", plugins_url( '/main/main.css', __FILE__ ));
  wp_enqueue_style("wp-sls-search-css");

}

function wp_sls_search_modal() { ?>
  <div class="wp-sls-search-modal" id="wp-sls-search-modal" aria-hidden="true">
    <div class="wp-sls-search-modal__overlay" tabindex="-1" data-micromodal-overlay>
      <div class="wp-sls-search-modal__container" role="dialog" aria-labelledby="modal__title" aria-describedby="modal__content">
        <header class="wp-sls-search-modal__header">
          <a href="#" aria-label="Close modal" data-micromodal-close></a>
        </header>
        <form role="search" method="get" class="search-form">
          <label for="wp-sls-earch-field">
            <span class="screen-reader-text">Search for:</span>
          </label>
          <input id="wp-sls-earch-field" class="wp-sls-search-field" type="search" autocomplete="off" class="search-field" placeholder="Search …" value="" name="s">
        </form>
        <div role="document"></div>
      </div>
    </div>
  </div>
<?php }

add_action('wp_footer', 'wp_sls_search_modal');