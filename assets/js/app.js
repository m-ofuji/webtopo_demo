const ROOT_PATH = '/index.php/';

const ajaxBase = type => dataType => sendFile => (url, param, success, fail) => {
  let setting = {
    url: url,
    data: param,
    type: type,
    cache: false,
    dataType: dataType
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
  ajaxBase('GET')(dataType)(false)(url, param, success, fail);
};

const ajaxGetJson = (url, param, success, fail) => {
  ajaxBase('GET')('json')(false)(url, param, success, fail);
};

const ajaxGetHtml = (url, param, success, fail) => {
  ajaxBase('GET')('html')(false)(url, param, success, fail);
};

const ajaxPostJson = sendfile => (url, param, success, fail) => {
  ajaxBase('POST')('json')(sendfile)(url, param, success, fail);
};

const getRecords = (url, msg, callBack) => {
  const param = {};
  param.sort = $('#sort').val();
  param.order = $('#order').val();
  param.offset = $('#offset').val();
  param.search_condition = createSearchCondition();
  ajaxGetHtml(url, param, res => {
    if (res) {
      if (res && typeof callBack == 'function') {
        callBack(res);
      }
    }
  }, res => {
    alert(msg);
  });
}

const createSearchCondition = () => {
  let cons = {};

  $('.search-condition:not(.checkbox), .search-checkbox.checked, .search-item.active').each((i,e) => {
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
};

$(document).on('click', '.close-modal', function() {
  hideModal();
});

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

$(document).ready(function() {
  $('.dropdown').dropdown();

  $('.ui.checkbox').checkbox();

  initSearchDates();

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

  $('#js-sidebar').click(function() {
    $('.ui.sidebar').sidebar('toggle');
  });

  //swiper
  var mySwiper = new Swiper('.swiper-container', {
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true
    },
    autoplay: {
      delay: 5000,
      disableOnInteraction: true
    },
    speed: 1000,
  });

  //humburger
  var forEach=function(t,o,r){if("[object Object]"===Object.prototype.toString.call(t))for(var c in t)Object.prototype.hasOwnProperty.call(t,c)&&o.call(r,t,c,t);else for(var e=0,l=t.length;l>e;e++)o.call(r,t[e],e,t)};
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

  //AOS初期化
  AOS.init();

  $('.animation-top').addClass('loaded');

  if ($('#clear').length > 0) {
    $('#clear').on('click', function() {
      $('input.search-condition[type="text"]').val('');
      $('.search-dropdown').each((i, e) => {
        $(e).dropdown('clear');
      });
      $('.search-checkbox-all').each((i,e) => {
        $(e).checkbox('check');
      });
    });
  }
});