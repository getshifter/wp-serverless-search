<?php
/*
Plugin Name: WP Serverless Search
Author: Daniel Olson
Author URI: https://github.com/emaildano/wp-serverless-search
Description: Serverless WordPress Search
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
  $save_path = $upload_dir['basedir'] . '/wp-sls/.';
  $dirname = dirname($save_path);

  if (!is_dir($dirname)) {
      mkdir($dirname, 0755, true);
  }
}

/**
 * Create Search Feed
 */

function create_search_feed() {

  require_once( ABSPATH . 'wp-admin/includes/export.php' );
  
  ob_start();
  export_wp();
  $xml = ob_get_clean();

  $upload_dir = wp_get_upload_dir();
  $save_path = $upload_dir['basedir'] . '/wp-sls/search-feed.xml';
  
  file_put_contents($save_path, $xml);
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
  
  $shifter_js = plugins_url( 'main/main.js', __FILE__ );

  $search_params = array(
    'searchForm' => get_option('wp_sls_search_form'),
    'searchFormInput' => get_option('wp_sls_search_form_input')
  );
  
  wp_register_script('wp-sls-search-js', $shifter_js, array( 'jquery' ), null, true);
  wp_localize_script( 'wp-sls-search-js', 'searchParams', $search_params );
  wp_enqueue_script('wp-sls-search-js');

  wp_register_script('fusejs', 'https://cdnjs.cloudflare.com/ajax/libs/fuse.js/3.2.1/fuse.min.js', null, null, true);
  wp_enqueue_script('fusejs');

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