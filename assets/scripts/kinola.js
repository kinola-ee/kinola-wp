jQuery(function($) {
  $('.js-kinola-location-filter').select2({
    ajax: {
      url: window.Kinola.ajaxUrl,
      dataType: 'json',
      data: function(params) {
        return {
          'action': 'kinola_get_filter_options',
          'field': 'location',
          'date': $('.js-kinola-date-filter').val(),
          'time': $('.js-kinola-time-filter').val(),
          'film': $('.js-kinola-time-filter').data('film'),
        };
      }
    }
  });
  $('.js-kinola-date-filter').select2({
    ajax: {
      url: window.Kinola.ajaxUrl,
      dataType: 'json',
      data: function(params) {
        return {
          'action': 'kinola_get_filter_options',
          'field': 'date',
          'location': $('.js-kinola-location-filter').val(),
          'time': $('.js-kinola-time-filter').val(),
          'film': $('.js-kinola-time-filter').data('film'),
        };
      }
    }
  });
  $('.js-kinola-time-filter').select2({
    ajax: {
      url: window.Kinola.ajaxUrl,
      dataType: 'json',
      data: function(params) {
        return {
          'action': 'kinola_get_filter_options',
          'field': 'time',
          'location': $('.js-kinola-location-filter').val(),
          'date': $('.js-kinola-date-filter').val(),
          'film': $('.js-kinola-time-filter').data('film'),
        };
      }
    }
  });
});
