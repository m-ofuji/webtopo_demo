const stockList = (() => {
  let list = {};
  let hasFetched = false;
  const getList = () => {
    return list;
  };
  const setList = function(l) {
    list = l;
  };
  const mergeList = (l) => {
    Object.keys(l).forEach(grade => {
      if (!list.hasOwnProperty(grade)) {
        list[grade] = l[grade];
      } else {
        list[grade] = list[grade].concat(l[grade]);
      }
    });
  };
  const add = (p) => {
    if (!list.hasOwnProperty(p.grade_name)) {
      list[p.grade_name] = [];
    }
    list[p.grade_name].push(p);
  };
  const remove = (p) => {
    list[p.grade_name] = list[p.grade_name].filter(n => n.id !== p.id);
    if (list[p.grade_name].length <= 0) {
      delete list[p.grade_name];
    }
  };
  const createStockList = () => {
    const body = '<div id="stock-list" order="">'
               +   '<div class="ui secondary menu grade-tab">'
               +   '</div>'
               + '</div>';
    let $body = $(body);
    let menu = '';
    let $t = '';
    const keys = Object.keys(list).sort();
    if (keys.length > 0) {
      keys.forEach((key) => {
        menu += '<a class="item stock-item" data-tab="' + key + '">' + key + '</a>';
        const tab = '<div class="ui tab stock-tab" data-tab="' + key + '">'
                  +   '<div class="ui relaxed divided list">'
                  +   '</div>'
                  + '</div>';
        let problems = '';
        list[key].forEach((p) => {
          const pr = '<div class="item monthly-stock-item">'
                   +    '<div class="content">'
                   +      '<div class="ui accordion stock-accordion">'
                   +        '<div class="title">'
                   +          '<div class="description">'
                   +            '<div class="header">課題名：' + p.name + '</div>'
                   +            '<div class="contetn">設定者：' + p.setter + '</div>'
                   +            '<div class="floated centered ui ' + p.grade_color + ' label problem-card-grade">'
                   +              p.grade_name
                   +            '</div>'
                   +            '<div class="floated centered ui basic teal label problem-card-grade">'
                   +              p.wall_name
                   +            '</div>'
                   +            '<div class="ui right floated basic primary button select-stock" id="' + p.id + '" name="' + p.name + '" setter="' + p.setter + '" grade_color="' + p.grade_color + '" grade_name="' + p.grade_name + '" wall_name="' + p.wall_name + '" img="' + p.img + '" imgs="' + p.imgs + '" comments="'+ p.comments + '">'
                   +              '<i class="circle plus icon"></i>'
                   +              '選択'
                   +            '</div>'
                   +          '</div>'
                   +        '</div>'
                   +        '<div class="content images">'
                   +        '</div>'
                   +      '</div>'
                   +    '</div>'
                   +  '</div>';
          const swipers = createImageSwiper(p.imgs);
          const $problem = $(pr);
          $problem.find('.content.images').append(swipers);
          problems += $problem.html();
        });
        $t = $(tab);
        $t.find('.ui.relaxed.divided.list').append($(problems));
        $body.find('.secondary.menu').after($t);
      });
    } else {
      menu = '<p>マンスリー課題のストックがありません。</p>';
    }
    $body.find('.secondary.menu').append(menu);
    return $body;
  };
  const refreshListModal = () => {
    const $list = createStockList();
    $('#stock-list').remove();
    $('#monthly-stock .popup-inner .ui.segment').append($list);
    $('#stock-list .menu .item').tab({context: 'parent'});
    const grade = $('.stock-item:first').attr('data-tab');
    $('.stock-item:first,.stock-tab[data-tab="' + grade + '"]').addClass('active');
    $('.stock-accordion').accordion();
    $('.select-stock').on('click', addProblem);
  };
  return {
    hasFetched: hasFetched,
    getList: getList,
    setList: setList,
    mergeList: mergeList,
    add: add,
    remove: remove,
    createStockList: createStockList,
    refreshListModal: refreshListModal
  };
})();

let sortable = null;

const initSortable = () => {
  sortable = new Draggable.Sortable(document.getElementById('problem-list'), {
    draggable: '.monthly-list-item',
    delay: 500
  });
};

// マンスリーストック
const openStockModal = function() {
  $('.ui.dimmer').addClass('active');
  const order = $(this).closest('.monthly-list-item').index();
  $('#monthly-stock-header').text('課題選択: No' + (order + 1));
  if (stockList.hasFetched === false) {
    ajaxGetJson(false)(ROOT_PATH + 'monthly/stocks', {}, res => {
      if (res) {
        stockList.hasFetched = true;
        stockList.mergeList(res.stocks);
        stockList.refreshListModal();
        $('#stock-list').attr('order', order);
        $('#monthly-stock').addClass('is-show');
        $('.ui.dimmer').removeClass('active');
      }
    }, res => {
      const list = stockList.createStockList();
      $('.ui.dimmer').removeClass('active');
      alert('課題の取得に失敗しました');
    });
  } else {
    stockList.refreshListModal();
    $('#stock-list').attr('order', order);
    $('#monthly-stock').addClass('is-show');
    $('.ui.dimmer').removeClass('active');
  }
}

const createProblemObject = ($dom) => {
  const obj = {
    id: $dom.attr('id'),
    name: $dom.attr('name'),
    setter: $dom.attr('setter'),
    grade_name: $dom.attr('grade_name'),
    grade_color: $dom.attr('grade_color'),
    wall_name: $dom.attr('wall_name'),
    img: $dom.attr('img'),
    imgs: $dom.attr('imgs').split(','),
    comments: $dom.attr('comments')
  };

  return obj;
}

// ストック一覧からリストへ
const addProblem = function() {
  const p = createProblemObject($(this));
  stockList.remove(p);
  const item = '<div class="item monthly-list-item" pid="' + p.id + '">'
              +  '<div class="content">'
              +    '<div class="ui accordion">'
              +    '<div class="title">'
              +      '<div class="description">'
              +        '<div class="header">課題名：' + p.name + '</div>'
              +        '<div class="content">設定者：' + p.setter + '</div>'
              +        '<div class="floated centered ui ' + p.grade_color + ' label problem-card-grade">'
              +          p.grade_name
              +        '</div>'
              +        '<div class="floated centered ui basic teal label problem-card-grade">'
              +          p.wall_name
              +        '</div>'
              +        '<a class="ui show-comment" id="' + p.id + '" name="' + p.name + '">'
              +          '<i class="large comment outline grey icon"></i>'
              +        '</a>'
              +        '<a class="remove-problem" id="' + p.id + '" name="' + p.name + '" setter="' + p.setter + '" grade_color="' + p.grade_color + '" grade_name="' + p.grade_name + '" wall_name="' + p.wall_name + '" img="' + p.img + '" imgs="' + p.imgs.join(',') + '" comments="' + p.comments + '">'
              +          '<i class="times circle outline right floated large grey icon"></i>'
              +        '</a>'
              +      '</div>'
              +    '</div>'
              +    '<div class="content images">'
              +    '</div>'
              +  '</div>'
              +'</div>';
  const order = $('#stock-list').attr('order');
  $(this).closest('.monthly-stock-item').remove();
  sortable.destroy();
  const $problem = $(item);
  const swiper = createImageSwiper(p.imgs);
  $problem.find('.content.images').append(swiper);
  // documentにイベントを追加すると、バブリングが発生するので、ここでイベントを追加する
  $problem.find('.remove-problem').on('click', removeProblem);
  $problem.find('.show-comment').on('click', showComment);
  if (p.comments > 0) {
    $problem.find('.comment.outline.grey').removeClass('comment outline grey icon').addClass('comments blue icon');
  }
  $('.monthly-list-item').eq(order).after($problem);
  $('.monthly-list-item').eq(order).remove();
  hideModal();

  initSortable();

  $('.ui.accordion').accordion();
};

// 削除された課題をストックリストに追加
const removeProblem = function() {
  const p = createProblemObject($(this));
  stockList.add(p);
  const item = '<div class="item monthly-list-item">'
              +  '<div class="content">'
              +    '<div class="ui primary basic button monthly-select">課題を選択する</div>'
              +  '</div>'
              +'</div>';
  const order = $(this).closest('.monthly-list-item').index();
  $(this).closest('.monthly-stock-item').remove();
  sortable.destroy();
  const i = $(item);
  i.find('.monthly-select').on('click', openStockModal);
  $('.monthly-list-item').eq(order).after(i);
  $('.monthly-list-item').eq(order).remove();
  initSortable();

  hideModal();
};

const initDetail = () => {
  initSortable();

  $('.ui.accordion').accordion();

  // フォームデータ作成時
  if ($('#input-form').length) {
    $('#input-form').submit(function(event) {
      const year = $('.year.active.selected').attr('data-value');
      const month = ('0' + $('.month.active.selected').attr('data-value')).slice(-2);
      if (year && month) {
        $(this).append($('<input>', {
          type: 'hidden',
          name: 'year_month',
          value: year + month
        }));
      }
      $('.monthly-list-item').each((i, el) => {
        const pid = $(el).attr('pid');
        if (pid > 0) {
          $(this).append($('<input>', {
            type: 'hidden',
            name: 'problems[]',
            value: JSON.stringify({id : pid, order : i + 1})
          }));
        }
      });
      
      // $('.monthly-list-item').each((i, el) => {
      //   const pid = $(el).attr('pid');
      //   if (pid > 0) {
      //     e.formData.append('problems[]', JSON.stringify({id : pid, order : i + 1}));
      //   }
      // });
    });

    // document.getElementById('input-form').addEventListener("formdata", function(e) {
    //   // const year = $('.year.active.selected').attr('data-value');
    //   // const month = ('0' + $('.month.active.selected').attr('data-value')).slice(-2);
    //   // if (year && month) {
    //   //   e.formData.append('year_month', year + month);
    //   // }

    //   $('.monthly-list-item').each((i, el) => {
    //     const pid = $(el).attr('pid');
    //     if (pid > 0) {
    //       e.formData.append('problems[]', JSON.stringify({id : pid, order : i + 1}));
    //     }
    //   });
    // });
  }

  $('#validation').on('click', function() {
    const param = new FormData(document.getElementById('input-form'));
    const year = $('.year.active.selected').attr('data-value');
    const month = ('0' + $('.month.active.selected').attr('data-value')).slice(-2);
    if (year && month) {
      param.append('year_month', year + month);
    }

    $('.monthly-list-item').each((i, el) => {
      const pid = $(el).attr('pid');
      if (pid > 0) {
        param.append('problems[]', JSON.stringify({id : pid, order : i + 1}));
      }
    });
    ajaxPostJson(true)(ROOT_PATH + 'monthly/validation', param, res => {
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

  // マンスリー課題選択
  $('.monthly-select').on('click', openStockModal);

  //リストから課題削除
  $('.remove-problem').on('click', removeProblem);
}

const initIndex = () => {
  getRecords(ROOT_PATH + 'monthly/records', 'マンスリーの取得に失敗しました。', (res) => {
    $('#main-grid').children().remove();
    $('#main-grid').append(res);
  });
}

const init = () => {
  if ($('#input-form').length) {
    initDetail();
  } else if ($('#main-grid').length) {
    initIndex();
  }
}

$(function(){
  init();
});