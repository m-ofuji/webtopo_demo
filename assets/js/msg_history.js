// History API が使えるブラウザかどうかをチェック
if( window.history && window.history.pushState ){
    //. ブラウザ履歴に１つ追加
    history.pushState( "nohb", null, "" );
    
    $(window).on( "popstate", function(event){
      //. このページで「戻る」を実行
      if( !event.originalEvent.state ){
        //. もう一度履歴を操作して終了
        history.pushState( "nohb", null, "" );
        return;
      }
    });
  }