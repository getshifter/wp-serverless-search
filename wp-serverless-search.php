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
add_action('wp_enqueue_scripts', 'wp_sls_search_assets' );
add_action('admin_enqueue_scripts', 'wp_sls_search_assets' );

function wp_sls_search_assets() {
  
  $shifter_js = plugins_url( 'main/main.js', __FILE__ );
  
  wp_register_script('wp-sls-search-js', $shifter_js, array( 'jquery' ), null, true);
  wp_enqueue_script('wp-sls-search-js');

  wp_register_script( 'fusejs', 'https://cdnjs.cloudflare.com/ajax/libs/fuse.js/3.2.1/fuse.min.js', null, null, true );
  wp_enqueue_script('fusejs');

  wp_register_script( 'micromodal', 'https://cdn.jsdelivr.net/npm/micromodal/dist/micromodal.min.js', null, null, true );
  wp_enqueue_script('micromodal');

  wp_register_style("wp-sls-search-css", plugins_url( '/main/main.css', __FILE__ ));
  wp_enqueue_style("wp-sls-search-css");

}

add_action('wp_footer', 'wp_sls_search_modal');

function wp_sls_search_modal() { ?>
  <div class="wp-sls-search-modal" id="wp-sls-search-modal" aria-hidden="true">
    <div class="wp-sls-search-modal__overlay" tabindex="-1" data-micromodal-overlay>
      <div class="wp-sls-search-modal__container" role="dialog" aria-labelledby="modal__title" aria-describedby="modal__content">
        <form role="search" method="get" class="search-form" action="https://127.0.0.1:8443/">
          <label for="search-form-5bc8e72db481b">
            <span class="screen-reader-text">Search for:</span>
          </label>
          <input type="search" autocomplete="off" class="search-field" placeholder="Search …" value="" name="s">
          <button type="submit" class="search-submit"><svg class="icon icon-search" aria-hidden="true" role="img">
              <use href="#icon-search" xlink:href="#icon-search"></use>
            </svg><span class="screen-reader-text">Search</span></button>
        </form>
        <div role="document"></div>
      </div>
    </div>
  </div>
<?php }