jQuery(window).ready(function ($) {

  function appendPageTitle() {
    $("body.search .search-field").keyup(function () {
      $("body.search .page-title span").empty().append($(this).val());;
    });
  }

  appendPageTitle();

  function getSearchQuery() {
    var urlParams = new URLSearchParams(window.location.search);
    var searchQuery = urlParams.getAll('s');
    return searchQuery;
  }

  var search = null;

  var data = location.origin + '/wp-content/uploads/wp-sls-api/db.json';
  var xhr = new XMLHttpRequest();
  xhr.open('GET', data, true);

  xhr.onload = function () {
    if (xhr.status === 200) {

      var array = JSON.parse(this.responseText);
      var searchData = array.posts;

      var $searchbar = $('.search-field');
      var options = {
        shouldSort: true,
        threshold: 0.5,
        minMatchCharLength: 0,
        keys: [
          "title.rendered",
        ],
      };

      var fuse = new Fuse(searchData, options);

      // $('#main').empty();
      $('#main').append('<div id="search-results"></div>');

      $searchbar.on('input', function () {
        var search = fuse.search($searchbar.val());
        var $res = $('#search-results');
        $res.empty();
        if ($searchbar.val().length < 1) return;
        if (search[0] === undefined) {
          $res.append('<h4>No results</h4>');
        }

        $res.append('<h5>All results:</h5>');

        var articleData = {
          title: '',
          excerpt: '',
          link: ''
        };

        function article(articleData) {
          return `<article>
              <header class='entry-header'>
                <h2 class='entry-title'><a href='` + articleData.link + `' rel='bookmark'>` + articleData.title + `</a></h2>
              </header>
              <div class='entry-summary'>
                ` + articleData.excerpt + `
              </div>
            </article>`;
        }

        search.forEach(function (el) {
          var articleData = {
            title: el.title.rendered,
            excerpt: el.excerpt.rendered,
            link: el.link.link
          };
          $res.append(article(articleData));
        });
      });
    } else {
      console.log('Request failed.  Returned status of ' + xhr.status);
    }
  };

  xhr.send();
})