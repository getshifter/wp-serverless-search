/**
 * WP Serverless Search
 * A static search plugin for WordPress.
 */


var wpServerlessSearch = (function () {
  const searchFeed = location.origin + '/wp-content/uploads/wp-sls/search-feed.xml';
  const urlParams = window.location.search;
  const searchModalSelector = 'wp-sls-search-modal';
  const searchModalInput = '.wp-sls-search-field';
  const searchForm = searchParams.searchForm;
  const searchFormInput = searchParams.searchFormInput;

  /**
   * 
   * Sync search input with search modal
   */
  function syncSearchFields() {
    jQuery(searchFormInput).keyup(function () {
      jQuery(searchModalInput).val(jQuery(this).val());
    });
  }

  /**
   * 
   * Launch Search Modal
   */
  function launchSearchModal() {
    MicroModal.show('wp-sls-search-modal');
  }

  /**
   * 
   * @param {string} url - WordPress Post URL Pathname
   */
  function postUrl(url) {
    var parser = document.createElement('a');
    parser.href = url;
    return parser.pathname;
  }

  /**
   * 
   * Test for search query based on URL
   */
  function urlQuery() {
    if (!searchQueryParams()) {
      return;
    }

    launchSearchModal();
  }

  /**
   * 
   * @param {string} query - Add query to search modal
   */
  function addQueryToSearchModal() {
    if (!searchQueryParams()) {
      return;
    }

    var el = document.querySelectorAll(searchModalInput);
    [].forEach.call(el, function (el) {
      el.value = searchQueryParams();
    });
  }

  /**
   * 
   * @param {string} url - Parse search query paramaters from URL
   */
  function searchQueryParams(url = urlParams) {
    url = url.split('+').join(' ');

    var params = {},
      tokens,
      re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(url)) {
      params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
    }

    return params.s;
  }

  /**
   * 
   * Search submit
   */

  function onSearchSubmit() {
    var el = document.querySelectorAll(searchForm);
    [].forEach.call(el, function (e) {
      e.addEventListener("submit", function (e) {
        e.preventDefault();
        launchSearchModal();
      });
    });
  }

  function onSearchInput() {
    var el = document.querySelectorAll(searchForm);
    [].forEach.call(el, function (e) {
      e.addEventListener("input", function (e) {
        // fire on search input
      });
    });
  }

  function searchPosts() {
    var search = null;
    jQuery.ajax(searchFeed, {
      accepts: {
        xml: "application/rss+xml"
      },
      dataType: "xml",
      success: function (data) {

        var searchArray = [];

        var data = data.getElementsByTagName("channel")[0];

        jQuery('.wp-sls-search-modal [role=document]').append('<div id="wp-sls-search-results" class="wp-sls-search-modal__results"></div>');

        jQuery(data).find("item").each(function () {
          var el = jQuery(this);

          // Check for Title
          if (!el.find("title").text()) {
            return;
          }

          searchArray.push({
            "title": el.find('title').text(),
            "description": el.find('excerpt\\:encoded').text(),
            "content": el.find('content\\:encoded').text(),
            "link": postUrl(el.find('link').text())
          });

        });

        var options = {
          shouldSort: true,
          threshold: 0.6,
          location: 0,
          distance: 100,
          maxPatternLength: 32,
          minMatchCharLength: 1,
          keys: [{
            name: 'title',
            weight: 0.75
          }, {
            name: 'description',
            weight: 0.5
          }, {
            name: 'content',
            weight: 0.5
          }]
        };

        var fuse = new Fuse(searchArray, options);

        var $searchInput = jQuery(searchParams.searchFormInput);

        $searchInput.each(function () {

          jQuery(this).on('input', function () {

            var search = fuse.search(jQuery(this).val());
            var $res = jQuery('#wp-sls-search-results');
            $res.empty();

            if (jQuery(this).val().length < 1) return;
            if (search[0] === undefined) {
              $res.append('<h5>No results</h5>');
            } else {
              $res.append('<h5>' + search.length + ' results:</h5>');
            }

            search.forEach(function (data) {

              var postContentData = {
                title: data.title,
                excerpt: data.description ? data.description : data.content,
                link: data.link
              };

              $res.append(postContent(postContentData));
            });
          });
        });
      }
    });
  }

  function postContent(
    postContentData = {
      title: '',
      excerpt: '',
      link: ''
    }
  ) {
    return `<article>
      <header class='entry-header'>
        <h2 class='entry-title'><a href='` + postContentData.link + `' rel='bookmark'>` + postContentData.title + `</a></h2>
      </header>
    </article>`;
  }

  // onSearchInput();
  searchPosts();
  onSearchSubmit();
  addQueryToSearchModal();
  urlQuery();
  syncSearchFields();

})();