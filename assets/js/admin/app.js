/* 共通処理 */

const ROOT_PATH = '/index.php/admin/';

const ajaxBase = type => dataType => sendFile => (url, param, success, fail) => {
  let setting = {
    url: url,
    data: param,
    type: type,
    cache: false,
    dataType: dataType,
  };
  if (sendFile) {
    setting.processData = false;
    setting.contentType = false;
  }
  $.ajax(setting).done(res => {
    if (typeof success === 'function') {
      success(res);
    }
  }).fail(res => {
    if (typeof fail === 'function') {
      fail(res);
    }
  });
};

const ajaxGet = dataType => (url, param, success, fail) => {
  ajaxBase('GET')(dataType)(url, param, success, fail);
};

const ajaxGetJson = sendfile =>  (url, param, success, fail) => {
  ajaxBase('GET')('json')(sendfile)(url, param, success, fail);
};

const ajaxGetHtml = (url, param, success, fail) => {
  ajaxBase('GET')('html')(false)(url, param, success, fail);
};

const ajaxPostJson = sendfile => (url, param, success, fail) => {
  ajaxBase('POST')('json')(sendfile)(url, param, success, fail);
};

const uploadFile = (file, url, success, fail) => {
  const param = new FormData();
  param.enctype = 'multipart/form-data';
  param.append('img', file);
  ajaxPostJson(true)(url, param, res => {
    success(res);
  }, res => {
    fail(res);
  });
}

const getRecords = (url, msg, callBack) => {
  const param = {};
  param.sort = $('#sort').val();
  param.order = $('#order').val();
  param.offset = $('#offset').val();
  param.search_condition = createSearchCondition();
  ajaxGetHtml(url, param, res => {
    if (res && typeof callBack == 'function') {
      callBack(res);
    }
  }, res => {
    alert(msg);
  });
};

const formSubmit = (url, method, fd) => {
  const form = document.createElement("form")
  form.action = url
  form.method = method
  form.addEventListener("formdata", eve => {
    for(const [name, value] of fd.entries()) {
      eve.formData.append(name, value)
    }
  })
  document.body.append(form)
  form.submit()
};

const initSearchDates = () => {
  $('.search-checkbox[name="one_week"]').each((i, e) => {
    if ($(e).attr('val')) return true;
    $(e).attr('val',(moment().add(-7, 'days').format('YYYY-MM-DD HH:mm:ss')));
  });

  $('.search-checkbox[name="one_month"]').each((i, e) => {
    if ($(e).attr('val')) return true;
    $(e).attr('val', (moment().add(-1, 'months').format('YYYY-MM-DD HH:mm:ss')));
  });
};

const createSearchCondition = () => {
  let cons = {};

  $('.search-condition:not(.checkbox), .search-checkbox.checked, .search-dropdown.active.selected').each((i,e) => {
    const element = $(e);
    const val = element.val() || element.attr('val');
    if (val === null || val === undefined || val === '') return true;
    cons[element.attr('name')] = {
      col:      element.attr('col'),
      operator: element.attr('operator'),
      val:      val
    };
  });

  cons = getMultipleDropDown(cons);
  return cons;
};

const getMultipleDropDown = (cons) => {
  $('.search-dropdown').each((i, e) => {
    let val = [];
    $(e).children('.ui.label.transition.visible').each((i, el) => {
      return val.push($(el).attr('data-value'));
    });
    if (val.length <= 0) return true;
    cons[$(e).attr('name')] = {
      col: $(e).attr('col'),
      operator: $(e).attr('operator'),
      val: val
    };
  });
  return cons;
};

const popupModal = () => {
  $('.js-popup').addClass('is-show');
};

const hideModal = () => {
  $('.js-popup').removeClass('is-show');
  // マンスリー管理の課題ストックのアコーディオンを閉じる
  $('.ui.tab').removeClass('active');
};

const createImageSwiper = (imgs) => {
  const wrapper = '<div class="swiper-container">'
                +   '<div class="swiper-wrapper">'
                +   '</div>'
                + '</div>';
  if (!imgs) return wrapper;
  const swipers = imgs.map(i => {
    var s = '<a class="swiper-slide problem-image" images="'+ i +'">'
          +   '<img class="problem-thumb" src="/public/assets/image/problem/'+ i +'">'
          + '</a>';
    return s;
  }).join();
  const $wrapper = $(wrapper);
  $wrapper.find('.swiper-wrapper').append(swipers);
  return '<div class="swiper-container">' + $wrapper.html() + '</div>';
};

$(document).on('click','.problem-image', function() {
  $('#image-popup .swiper-container,#image-popup .ui.image').remove();
  const $container = $(this).closest('.swiper-container').clone();
  $container.appendTo('#image-popup .popup-inner');
  $('#image-popup .problem-image').removeClass('problem-image');
  const swiper = new Swiper('#image-popup .swiper-container', {});
  $('#image-popup').addClass('is-show');
});

const showErrors = (msg, clearMsg) => {
  if (clearMsg) {
    $('#error-list').children().remove();
  }
  $('#error-list').append(msg);
  $('#error-message').removeClass('hidden');
}

const hideErrors = () => {
  $('#error-list').children().remove();
  $('#error-message').addClass('hidden');
}

const countLength = ($inputText) => {
  const maxLength = $inputText.attr('maxlength');
  const remain = maxLength - $inputText.val().length;
  $inputText.closest('.field').children('.count').text(remain >= 0 ? remain : 0);
};

const initCheckbox = () => {
  $('.input-checkbox.checked').checkbox('check');
  $('.search-checkbox.checked').checkbox('check');
}

const initDropdown = () => {
  $('.ui.dropdown').dropdown('set selected', $('.item.search-dropdown.active.selected').attr('data-value'));
}

//コメント登録
const registerComment = () => {
  const $input = $('#comment-input');
  const comment = $input.val();
  if (!comment) {
    alert('コメントを入力してください。');
    return;
  }
  const param = {
    problem_id: $input.attr('problem_id'),
    comment: comment
  }
  ajaxPostJson(false)(ROOT_PATH + 'comment/register', param, successRes => {
    if (successRes.res) {
      $input.val('');
      $('.comments-wrapper').children().remove();
      $('.comments-wrapper').append(successRes.comments);
    } else {
      alert('コメントの送信に失敗しました');
    }
  }, failedRes => {
    alert('コメントの送信に失敗しました');
  });
};

//コメント表示
const showComment = function() {
  $('.comments-wrapper').children().remove();
  const order = $(this).closest('.monthly-list-item').index() + 1;
  const id = $(this).attr('id');
  const name = $(this).attr('name');
  let header = order ? 'No.' + order + '　' : '';
  header += '課題名：' + name;
  $('.comment-header').text(header);
  $('#comment-input').attr('problem_id', id);
  ajaxGetJson(false)(ROOT_PATH + 'comment/comments/' + id, {}, res => {
    if (res.res) {
      $('.comments-wrapper').append(res.comments);
    } else {
      alert('コメントの取得に失敗しました');
    }
    $('#comment-popup').addClass('is-show');
  }, res => {
    alert('コメントの取得に失敗しました');
  });
  return false;
};

// 初期化
$(document).ready(function() {
  initSearchDates();

  // semantic ui
  $('.masthead').visibility({
    once: false,
    onBottomPassed: function() {
      $('.fixed.menu').transition('fade in');
    },
    onBottomPassedReverse: function() {
      $('.fixed.menu').transition('fade out');
    }
  });

  $('.sidebar').sidebar({
    onHide: function() {
      $('.hamburger').removeClass('is-active');
    }
  });

  $('.dropdown,.ui.dropdown').dropdown();

  $('.ui.checkbox').checkbox();

  $('.ui.radio.checkbox').checkbox();

  initCheckbox();
  initDropdown();

  $('.close-modal').on('click', function() {
    $('.mini.modal').modal('hide');
    $(this).closest('.js-popup').removeClass('is-show');
    $('#image-popup .popup-inner').children().not('.close-btn.close-modal').remove();
    // マンスリー管理の課題ストックを閉じる際、課題画像アコーディオンを閉じる
    // 閉じないと、画像が開きっぱなしでスクロールが聞かなくなるため。
    if ($(this).closest('.popup').attr('id') == 'monthly-stock') {
      $('.stock-accordion .content.images ').removeClass('active');
    }
  });

  $('#js-sidebar').click(function() {
    $('.ui.sidebar').sidebar('toggle');
  });

  if ($('input[type="text"],input[type="password"]').length) {
    $('input[type="text"],input[type="password"]').on('change keypress keyup keyenter', function(e) {
      countLength($(this));
    });
    $('input[type="text"],input[type="password"]').each((i, e) => {
      countLength($(e));
    });
  }

  //humburger
  var forEach=function(t,o,r) {if("[object Object]"===Object.prototype.toString.call(t))for(var c in t)Object.prototype.hasOwnProperty.call(t,c)&&o.call(r,t,c,t);else for(var e=0,l=t.length;l>e;e++)o.call(r,t[e],e,t)};
  var hamburgers = document.querySelectorAll(".hamburger");
  if (hamburgers.length > 0) {
    forEach(hamburgers, function(hamburger) {
      hamburger.addEventListener("click", function() {
      this.classList.toggle("is-active");
      }, false);
    });
  }

  var fadeUps = document.querySelectorAll(".fade-up");
  if (fadeUps.length > 0) {
    forEach(fadeUps, function(fadeUp) {
      fadeUp.setAttribute('data-aos', "fade-up");
      fadeUp.setAttribute('data-aos-once', true);
    });
  }

  if ($('#clear').length > 0) {
    $('#clear').on('click', function() {
      $('input.search-condition[type="text"]').val('');
      $('.search-dropdown, .selection.dropdown').each((i, e) => {
        $(e).dropdown('clear');
      });
      $('.search-checkbox-all').each((i,e) => {
        $(e).checkbox('check');
      });
    });
  }

  // 登録・更新内容送信
  if ($('#register').length > 0) {
    $('#register').on('click', function() {
      $('#input-form').submit();
    });
  }

  $('.animation-top').addClass('loaded');
});

// コメント表示、追加
$('body').on('click', '.show-comment', showComment);
$('body').on('click', '.add-comment', registerComment);
