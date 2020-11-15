$(function(){
  // 準備
  var canvas = new fabric.Canvas('cnvs');
  // 四角
  canvas.add(
    new fabric.Rect({
        width: 100,
        height: 200,
        left: 100,
        top: 100,
        fill: 'red',
    }));
});