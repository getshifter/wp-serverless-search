const urlParams = window.location.search;
const searchModalSelector = 'wp-sls-search-modal';
const searchForm = searchParams.searchForm;
const searchModalInput = 'input.wp-sls-search-field';

/**
 * 
 * Launch Search Modal
 */
function launchSearchModal() {
  document.addEventListener("DOMContentLoaded", function (event) {
    MicroModal.show('wp-sls-search-modal');
  });
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
  } else {
    launchSearchModal();
  }
}

urlQuery();

/**
 * 
 * @param {string} query - Add query to search modal
 */
function addQueryToSearchModal() {
  var el = document.querySelectorAll(searchModalInput);
  [].forEach.call(el, function (el) {
    el.value = searchQueryParams();
  });
}

addQueryToSearchModal();

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
      MicroModal.show('wp-sls-search-modal');
    });
  });
}

onSearchSubmit();

function onSearchInput() {
  var el = document.querySelectorAll(searchForm);
  [].forEach.call(el, function (e) {
    e.addEventListener("input", function (e) {
      // fire on search input
    });
  });
}

onSearchInput();

jQuery(window).ready(function ($) {
  'use strict';

  var searchForm = $(searchParams.searchForm);
  var urlParams = new URLSearchParams(window.location.search);

  // Launch modal based on URL search query
  if (urlParams.get('s') != null) {
    launchSearchModal();
    $('.wp-sls-search-field').val(urlParams.get('s'));
  }

  $(searchParams.searchFormInput).keyup(function () {
    $('.wp-sls-search-field').val($(this).val());
  });

});

jQuery(window).ready(function ($) {
  var feed = location.origin + '/wp-content/uploads/wp-sls/search-feed.xml';
  var search = null;
  $.ajax(feed, {
    accepts: {
      xml: "application/rss+xml"
    },
    dataType: "xml",
    success: function (data) {

      var searchArray = [];

      var data = data.getElementsByTagName("channel")[0];

      $('.wp-sls-search-modal [role=document]').append('<div id="wp-sls-search-results" class="wp-sls-search-modal__results"></div>');

      $(data).find("item").each(function () {
        var el = $(this);

        // Check for Title
        if (!el.find("title").text()) {
          return;
        }

        // console.log(el);

        searchArray.push({
          "title": el.find('title').text(),
          "description": el.find('description').text(),
          "link": postUrl(el.find('link').text())
        });

      });

      // console.log(searchArray);

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
        }]
      };

      var fuse = new Fuse(searchArray, options);

      var $searchInput = $(searchParams.searchFormInput);

      $searchInput.each(function () {

        $(this).on('input', function () {

          var search = fuse.search($(this).val());
          var $res = $('#wp-sls-search-results');
          $res.empty();

          if ($(this).val().length < 1) return;
          if (search[0] === undefined) {
            $res.append('<h5>No results</h5>');
          } else {
            $res.append('<h5>' + search.length + ' results:</h5>');
          }

          search.forEach(function (data) {

            var articleData = {
              title: data.title,
              excerpt: data.description,
              link: data.link
            };

            $res.append(article(articleData));
          });
        });
      });
    }
  });

});

function article(
  articleData = {
    title: '',
    excerpt: '',
    link: ''
  }
) {
  return `<article>
      <header class='entry-header'>
        <h2 class='entry-title'><a href='` + articleData.link + `' rel='bookmark'>` + articleData.title + `</a></h2>
      </header>
      <div class='entry-summary'>
        ` + articleData.excerpt + `
      </div>
    </article>`;
}
