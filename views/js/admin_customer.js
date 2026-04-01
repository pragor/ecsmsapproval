$(document).ready(function () {
  var $badge = $('#ecsms-approval-badge');
  if (!$badge.length) { return; }

  var $target = $('.customer-partner-offers-status');
  if ($target.length) {
    $target.parent().append($badge.show());
  }
});
