const BASE_SEGMENT = ROOT_PATH + CONTROLLER;

$(document).on('click','.problem-image', function() {
  $('#image-popup .swiper-container').remove();
  const $container = $(this).closest('.swiper-container').clone().appendTo('#image-popup .popup-inner');
  const swiper = new Swiper('#image-popup .swiper-container', {});
  $('#image-popup').addClass('is-show');
});

const initCheckbox = () => {
  $('.input-checkbox.checked').checkbox('check');
  $('.search-checkbox.checked').checkbox('check');
}

const init = () => {
  initIndex();
  initCheckbox();
}

const initProblemSwiper = () => {
  var mySwiper = new Swiper('.swiper-container', {});
};

// const getProblemRecords = () => {
//   getRecords(BASE_SEGMENT + '/problems', '課題の取得に失敗しました。', () => {
//     initProblemSwiper();
//   });
// };

const getProblemRecords = (clear) => {
  if (clear === true) {
    $('#offset').val(0);
  }
  getRecords(BASE_SEGMENT + '/problems', '課題の取得に失敗しました。', (res) => {
    if (clear === true) {
      $('#main-grid').children().remove();
    }
    $('#main-grid').append(res);
    initProblemSwiper();
    const count = $(res).children('.ui.card').length;
    // 10件以上とってこれなかったら上限まで達したと判断してもっと見るボタン使用不可
    const enabledShowMore = count >= 10 ? 'blue' : 'disabled grey';
    $('#show-more').removeClass('blue disabled grey').addClass(enabledShowMore);
  });
};

const initIndex = () => {
  $('#open-search-form').on('click', function() {
    $('#search-form').addClass('is-show');
  });

  $('#search').on('click', function() {
    window.scrollTo(0, 0);
    getProblemRecords(true);
    hideModal();
  });

  $('#sort-order').dropdown({
    onChange: (value, text, $selectedItem) => {
      $('#sort').val($selectedItem.attr('sort'));
      $('#order').val($selectedItem.attr('order'));
      window.scrollTo(0, 0);
      getProblemRecords(true);
    }
  });

  $('#show-more:not(.disabled)').on('click', function() {
    const offset = $('#offset').val();
    $('#offset').val(Number(offset) + 10);
    getProblemRecords();
  });

  const sort = $('#sort').val();
  const order = $('#order').val();
  $('.dropdown-sort').each((i, e) => {
    const el = $(e);
    if (el.attr('sort') == sort && el.attr('order') == order) {
      el.addClass('active selected');
    }
  });

  // 初期表示時、課題取得
  getProblemRecords(true);
};

$(document).ready(function() {
  init();
});