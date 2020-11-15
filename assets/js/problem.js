const getParam = () => {
  let param = {};
  $('.ca.checked').each((i, e) => {
    const r = $(e).children('input');
    if (r.attr('data-value')) {
      param[r.attr('name')] = r.attr('data-value');
    }
  });

  const grades = [];
  $('.dropdown-grade').children('a').each((i, e) => {
    grades.push($(e).attr('data-value'));
  });
  if (grades.length > 0) {
    param['g'] = grades;
  }

  const setter = encodeURI($('#setter-form').val());
  if (setter) {
    param['s'] = setter;
  }

  return param;
};

const popupImage = function() {
  var popup = document.getElementById('js-popup');
  if(!popup) return;
  popup.classList.add('is-show');
  // prohibitScroll();
}

// スクロール禁止
var prohibitScroll = function() {
  // PCでのスクロール禁止
  document.addEventListener("mousewheel", scrolControl, { passive: false });
  // スマホでのタッチ操作でのスクロール禁止
  document.addEventListener("touchmove", scrolControl, { passive: false });
}
// スクロール禁止解除
var allowScroll = function() {
  // PCでのスクロール禁止解除
  document.removeEventListener("mousewheel", scrolControl, { passive: false });
  // スマホでのタッチ操作でのスクロール禁止解除
  document.removeEventListener('touchmove', scrolControl, { passive: false });
}

// スクロール関連メソッド
var scrolControl = function(event) {
  console.log(event.touches);
  if(event.touches && event.touches.length == 1){
    event.preventDefault();
  }
  // event.preventDefault();
}

$(document).ready(function() {
  // $('.btn-circle-flat.float-search a').click(function() {
  //   $('.ui.mini.modal').modal('show');
  // });

  $('.btn-circle-flat.float-search a').click(function() {
    $('.ui.modal.search-modal').modal('show');
  });

  $('.ui.radio.checkbox').checkbox();
  
  $('.ui.checkbox').checkbox();

  $('.dropdown').dropdown();

  $('#search-form').on('submit', event => {
      event.preventDefault();
      const param = getParam();
      console.log(param);
      ajaxGetHtml('/index.php/problem/problems', param, res => {
        $('.problem-grid').children().remove();
        $('.problem-grid').append(res);
      }, res => {
      });
  })

  $('#search-button').on('click', () => {
    $('#search-form').submit();
    $('.ui.modal').modal('hide');
  });

  $('.ui.radio.checkbox.checked.ca').click();

  $('#search-form').submit();

  // $(document).on('click','.problem-image', function() {
  //   const src = $(this).children('img').attr('src');
  //   $('#problem-modal-image').attr('src', src);
  //   popupImage();
  //   // $('.ui.modal.problem-modal').modal('show');
  // });

  // $(document).on('click', '.close-modal', function() {
  //   $('#js-popup').removeClass('is-show');
  //   // allowScroll();
  // });
});