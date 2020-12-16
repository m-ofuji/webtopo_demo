const BASE_SEGMENT = ROOT_PATH + CONTROLLER;

const init = () => {
  if ($('#input-form').length) {
    initDetail();
  } else if ($('#main-grid').length) {
    initIndex();
  }
}

const removePreviewImage = function() {
  $(this).parent('.prev-field').remove();
};

const initProblemSwiper = () => {
  var mySwiper = new Swiper('.swiper-container', {});
};

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

// 画像アップロード
const onImageSelected = (e) => {
  //画像選択時
  const file = e.target.files[0];

  if (!hasImageExtension(file)) {
    alert('アップロードできるのは画像ファイルのみです。');
    return;
  }

  uploadFile(file, BASE_SEGMENT + '/upload', res => {
    if (res.res) {
      const prev = '<div class="prev-field">'
                 +   '<input name="images[]" type="hidden" value="' + res.img[0] + '"/>'
                 +   '<img class="ui left aligned big image image-preview" src="" />'
                 + '</div>';

      const remove = '<div class="close-btn remove-image">'
                   +   '<i class="large red times circle icon"></i>'
                   + '</div>';
      const $prev = $(prev);
      const $remove = $(remove);
      $remove.on('click', removePreviewImage)
      $prev.prepend($remove);
      $('.image-list').append($prev);

      var reader = new FileReader();
      reader.onload = function (e) {
        $(".image-preview:last").attr('src', e.target.result);
      }
      reader.readAsDataURL(file);
    } else {
      alert('画像のアップロードに失敗しました。アップロードできるファイルサイズの上限は1Mバイトまでです。');
    }
  }, res => {
    alert('画像のアップロードに失敗しました。アップロードできるファイルサイズの上限は1Mバイトまでです。');
  });
};

const hasImageExtension = (file) => {
  const validExtensions = new RegExp('([^\s]+(\\.(jpg|jpeg|png|gif|tiff|bmp))$)', 'i');
  return validExtensions.test(file.name);
}

const moveProblem = function() {
  const param = {
    id: $(this).attr('problem-id')
  };
  ajaxPostJson(false)(BASE_SEGMENT +'/move', param, res => {
    if (res.res) {
      alert('移動しました。');
      getProblemRecords(true);
    } else {
      alert('移動に失敗しました。');
    }
  }, res => {
    alert('移動に失敗しました。');
  });
};

$(document).on('click', '.move-problem', function () {
  const id = $(this).attr('problem-id');
  $('#move').attr('problem-id', id);
  $('#move-modal').modal('show');
});

const initDetail = () => {
  //画像選択時
  $('.problem-image-upload').on('change', function (e) {
    onImageSelected(e);
  });

  $('#validation').on('click', function() {
    const param = new FormData(document.getElementById('input-form'));
    ajaxPostJson(true)(BASE_SEGMENT +'/validation', param, res => {
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

  $('.remove-image').on('click', removePreviewImage);

  $('#delete-confirmation').on('click', function() {
    $('#delete-modal').modal('show');
  });

  $('#delete').on('click', function() {
    const form = new FormData();
    form.append('id', $('input[name="id"]').val());
    const url = BASE_SEGMENT +'/delete/' + $('input[name="id"]').val();
    formSubmit(url, 'POST', form);
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

  // 並び順ドロップダウン初期化
  const sort = $('#sort').val();
  const order = $('#order').val();
  $('.dropdown-sort').each((i, e) => {
    const el = $(e);
    if (el.attr('sort') == sort && el.attr('order') == order) {
      el.addClass('active selected');
    }
  });

  $('#move').on('click', moveProblem);

  // 課題取得
  getProblemRecords(true);
};

$(document).ready(function() {
  init();
});