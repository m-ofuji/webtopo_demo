$(document).ready(function() {
  // fullcalender初期化
  var calendarEl = document.getElementById('calendar');
  var calendar = new FullCalendar.Calendar(calendarEl, {
    plugins: [ 'dayGrid' ],
    header: ['prev,next'],
    locale: 'ja',
    events: [
      {
        title: '休館日',
        start: '2020-04-20'
      },
      {
        title: 'マンスリー張替',
        start: '2020-04-25',
        color: '#ff9f89'
      },
    ]
  });
  calendar.render();
});