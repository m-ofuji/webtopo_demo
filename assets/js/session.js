const CONTROLLER = 'session';

const evaluate = function() {
    const $evaluation = $(this);
    const param = {
      val : $evaluation.attr('val'),
      id : $evaluation.attr('pid')
    };
  
    ajaxPostJson(false)(ROOT_PATH + CONTROLLER + '/evaluation', param, function(res) {
      if (res) {
        const val = Number($evaluation.find('.value').text());
        console.log(val);
        $evaluation.find('.value').text(val + 1);
      } else {
        alert('送信に失敗しました。');
      }
    },
    () => {
      alert('送信に失敗しました。');
    });
  };
  
  $('body').on('click', '.good,.bad', evaluate);