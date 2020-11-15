const CONTROLLER = 'monthly';

$(document).ready(function() {
  $('.month-select').on('click', function() {
      $('.month-select').removeClass('active');
      $(this).addClass('active');
      getProblemRecords(true);
  });
});