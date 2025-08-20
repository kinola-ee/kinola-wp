jQuery(function ($) {

  var getFilmId = function () {
    if ($('.js-kinola-filters-form').data('film')) {
      return $('.js-kinola-filters-form').data('film');
    }

    if ($('.js-kinola-film-filter').length) {
      return $('.js-kinola-film-filter').val();
    }

    return null;
  }

  if ($('.js-kinola-film-filter').length) {
    $('.js-kinola-film-filter').select2({
      minimumResultsForSearch: Infinity,
      ajax: {
        url: window.Kinola.ajaxUrl,
        dataType: 'json',
        data: function (params) {
          return {
            'action': 'kinola_get_filter_options',
            'field': 'film',
            'venue': $('.js-kinola-venue-filter').val(),
            'date': $('.js-kinola-date-filter').val(),
            'time': $('.js-kinola-film-filter').data('film'),
          };
        }
      }
    });
  }
  $('.js-kinola-venue-filter').select2({
    minimumResultsForSearch: Infinity,
    ajax: {
      url: window.Kinola.ajaxUrl,
      dataType: 'json',
      data: function (params) {
        return {
          'action': 'kinola_get_filter_options',
          'field': 'venue',
          'film': getFilmId(),
          'date': $('.js-kinola-date-filter').val(),
          'time': $('.js-kinola-time-filter').length ? $('.js-kinola-time-filter').val() : 'all',
        };
      }
    }
  });
  $('.js-kinola-date-filter').select2({
    minimumResultsForSearch: Infinity,
    ajax: {
      url: window.Kinola.ajaxUrl,
      dataType: 'json',
      data: function (params) {
        return {
          'action': 'kinola_get_filter_options',
          'field': 'date',
          'film': getFilmId(),
          'venue': $('.js-kinola-venue-filter').val(),
          'time': $('.js-kinola-time-filter').length ? $('.js-kinola-time-filter').val() : 'all',
        };
      }
    }
  });
  if ($('.js-kinola-time-filter').length) {
    $('.js-kinola-time-filter').select2({
      minimumResultsForSearch: Infinity,
      ajax: {
        url: window.Kinola.ajaxUrl,
        dataType: 'json',
        data: function (params) {
          return {
            'action': 'kinola_get_filter_options',
            'field': 'time',
            'film': getFilmId(),
            'venue': $('.js-kinola-venue-filter').val(),
            'date': $('.js-kinola-date-filter').val(),
          };
        }
      }
    });
  }
});
