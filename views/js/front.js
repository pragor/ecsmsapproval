(function ($) {
  'use strict';

  $(document).ready(function () {

    $(document).on('submit', '#js-delivery', function () {
      var approval = $('#ecsms-approval-checkbox').is(':checked') ? 1 : 0;

      $.ajax({
        url: ecsmsAjaxUrl,
        method: 'POST',
        async: true,
        data: {
          approval: approval,
          token: ecsmsToken,
        },
        dataType: 'json',
      });
    });

  });
})(jQuery);
