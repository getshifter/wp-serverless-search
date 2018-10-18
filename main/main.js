jQuery(window).ready(function ($) {
  var feed = location.origin + '/feed/';
  var search = null;
  $.ajax(feed, {
    accepts: {
      xml: "application/rss+xml"
    },
    dataType: "xml",
    success: function (data) {

      var searchArray = [];

      var data = data.getElementsByTagName("channel")[0];

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
          "link": el.find('link').text()
        });

      });

      // console.log(searchArray);

      var $searchbar = $('.wp-sls-search-modal .search-field');
      var options = {
        shouldSort: true,
        threshold: 0.5,
        minMatchCharLength: 0,
        keys: [
          "title",
        ],
      };

      var fuse = new Fuse(searchArray, options);

      // $('#main').empty();
      $('.wp-sls-search-modal [role=document]').append('<div id="search-results" class="wp-sls-search-modal__results"></div>');

      $('.search-field').on('click', function () {
        MicroModal.show('wp-sls-search-modal');
      })

      $searchbar.on('input', function () {
        var search = fuse.search($searchbar.val());
        var $res = $('#search-results');
        $res.empty();
        if ($searchbar.val().length < 1) return;
        if (search[0] === undefined) {
          $res.append('<h4>No results</h4>');
        }

        $res.append('<h5>All results:</h5>');

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

        search.forEach(function (data) {

          var articleData = {
            title: data.title,
            excerpt: data.description,
            link: data.link
          };

          $res.append(article(articleData));
        });
      });
    }
  });

  MicroModal.init({
    onClose: modal => console.info(`${modal.id} is hidden`),
    onShow: modal => console.info(`${modal.id} is shown`)
  });

});