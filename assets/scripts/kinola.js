jQuery(function($) {
  $.get(window.Kinola.ajaxUrl, {
    'action': 'kinola_get_filter_options',
    'field': 'time',
    'venue': 'tu-kirik-jakobi-1',
    'date': 'October 31, 2023',
  }, function (data) {
    console.log(data);
  });

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
        };
      }
    }
  });
});
