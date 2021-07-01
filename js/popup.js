(function (Drupal, $, window) {

  Drupal.behaviors.custom_popup = {

    attach: function (context, settings) {

      console.log(settings)

      var $body = $(context).find('body').once('custom_popup');
      if ($body.length > 0) {
        if (!$.cookie('shown_' + settings.popup_id)) {
          $body.append(settings.popup)
          $.cookie('shown_' + settings.popup_id, 1)
        }
      }

      $(document).on('click', function (event) {
        if ($(event.target).hasClass('js-custom-popup--close')) {
          $('.custom-popup, .custom-popup--shadow').hide();
        }
      })
    }
  }

})(Drupal, jQuery, window);