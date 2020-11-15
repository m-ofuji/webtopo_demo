const initIndex = () => {

  $('#validation').on('click', function() {
    const param = new FormData(document.getElementById('input-form'));
    ajaxPostJson(true)(ROOT_PATH + 'pass/validation', param, res => {
      if (res.result) {
        $('#register-modal').modal('show');
        hideErrors();
      } else {
        let msg = '';
        res.error.forEach(function(e) {
          msg += '<li>' + e + '</li>'
        });
        showErrors(msg, true);
        window.scrollTo(0,0);
      }
    }, res => {
      alert('送信に失敗しました');
    });
  });
}

const init = () => {
  if ($('#input-form').length) {
    initIndex();
  }
}

$(function() {
  init();
});