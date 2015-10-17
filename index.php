<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
  <?php
    include("connectSql.php");
    //On se connecte
    connectMaBase();
  ?>
<head>
  <title>Consommation EDF</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <meta name="robots" content="noindex"/>
  <link href="jquery-ui/jquery-ui.min.css" rel="stylesheet">
  <link href="jquery-ui/jquery-ui.theme.min.css" rel="stylesheet">
  <script type="text/javascript" src="jquery-ui/external/jquery/jquery.js"></script>
  <script type="text/javascript" src="jquery-ui/jquery-ui.min.js"></script>
  <script src="js/highcharts.js"></script>
  <script src="js/modules/exporting.js"></script>
  <script src="js/modules/data.js"></script>


  <script type="text/javascript">

DELTA_HOUR = 1000 * 3600;
DELTA_DAY = 24 * DELTA_HOUR;
DELTA_WEEK = 7 * DELTA_DAY;
DELTA_MONTH = 30 * DELTA_DAY;
DELTA_YEAR = 12 * DELTA_MONTH;

MODE_DAY = 1;
MODE_WEEK = 2;
MODE_MONTH = 3;
MODE_YEAR = 4;
MODE_ALL = 5;

DAYS = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
MONTHS = ["Janvier", "Fevrier", "Mars", "Avril", "May", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Decembre"];

m_selectedMode = MODE_DAY
m_selectedLastDate = Date.now();

function setMode(mode)
{
  m_selectedMode = mode;
  m_selectedLastDate = Date.now();

  updateChart();
}

function prev()
{
  switch(m_selectedMode)
  {
    case MODE_DAY:
      m_selectedLastDate -= DELTA_DAY;
      break;
    case MODE_WEEK:
      m_selectedLastDate -= DELTA_WEEK;
      break
    case MODE_MONTH:
      m_selectedLastDate -= DELTA_MONTH;
      break;
    case MODE_YEAR:
      m_selectedLastDate -= DELTA_YEAR;
      break;
    case MODE_ALL:
      m_selectedLastDate = Date.now();
      break;
  }
  updateChart();
}

function next()
{
  switch(m_selectedMode)
  {
    case MODE_DAY:
      m_selectedLastDate += DELTA_DAY;
      break;
    case MODE_WEEK:
      m_selectedLastDate += DELTA_WEEK;
      break
    case MODE_MONTH:
        m_selectedLastDate += DELTA_MONTH;
        break;
    case MODE_YEAR:
        m_selectedLastDate += DELTA_YEAR;
        break;
    case MODE_ALL:
        m_selectedLastDate = Date.now();
        break;
  }
  updateChart();
}

function updateChart()
{
  switch(m_selectedMode)
  {
    case MODE_DAY:
      asyncCreateChart(m_selectedLastDate, m_selectedLastDate);
      break;
    case MODE_WEEK:
      asyncCreateChart(m_selectedLastDate - DELTA_WEEK, m_selectedLastDate);
      break
    case MODE_MONTH:
      asyncCreateChart(m_selectedLastDate - DELTA_MONTH, m_selectedLastDate);
      break;
    case MODE_YEAR:
      asyncCreateChart(m_selectedLastDate - DELTA_YEAR, m_selectedLastDate);
      break;
    case MODE_ALL:
      asyncCreateChart(0, m_selectedLastDate);
      break;
  }
}

function dateToTitle(date)
{
  title = "";
  dateObject = new Date(date);
  switch(m_selectedMode)
  {
    case MODE_DAY:
      title = DAYS[dateObject.getDay()];
      break;
    case MODE_WEEK:
      break
    case MODE_MONTH:
      title = MONTHS[dateObject.getMonth()];
      break;
    case MODE_YEAR:
      break;
    case MODE_ALL:
      break;
  }
  return title;
}

function createChart(csv)
{
    $('#container_puissance').highcharts({
        chart: {
            type: 'spline'
        },
        title: {
            text: dateToTitle(m_selectedLastDate)
        },
        yAxis: {
            min: 0,
            max: 1500,
            startOnTick: false,
            endOnTick: false
        },
        data: {
           rows: csv
        }
    });
};

function asyncCreateChart(minDate, maxDate)
{
  deltaChart = maxDate - minDate;
  deltaRequest = 0;
  maxRequest = maxDate + 0.2 * deltaRequest;

  minDateObject = new Date(minDate);
  maxDateObject = new Date(maxDate);
  // estimate how many point there will be in chart and adjust delta of request
  // a few days
  if ( deltaChart < 50 * DELTA_HOUR )
  {
    deltaRequest = 0.8*DELTA_HOUR
    minDateObject.setUTCHours(0);
    minDateObject.setUTCMinutes(0);
    minDateObject.setUTCSeconds(0);

    maxDateObject.setUTCHours(23);
    maxDateObject.setUTCMinutes(59);
    maxDateObject.setUTCSeconds(59);
  }

  // a few weeks
  else if( deltaChart < 50 * DELTA_DAY )
  {
    deltaRequest = DELTA_DAY
    minDateObject.setDate(0);
    minDateObject.setUTCHours(0);
    minDateObject.setUTCMinutes(0);
    minDateObject.setUTCSeconds(0);

    maxDateObject.setUTCHours(23);
    maxDateObject.setUTCMinutes(59);
    maxDateObject.setUTCSeconds(59);
  }

  // a few month
  else if ( deltaChart < 50 * DELTA_WEEK )
  {
    deltaRequest = DELTA_WEEK;
    minDateObject.setUTCHours(0);
    minDateObject.setUTCMinutes(0);
    minDateObject.setUTCSeconds(0);

  }
  else
  {
    deltaRequest = DELTA_MONTH;
    minDateObject.setUTCHours(0);
    minDateObject.setUTCMinutes(0);
    minDateObject.setUTCSeconds(0);

  }

  minRequest = minDateObject.getTime();
  maxRequest = maxDateObject.getTime();

  $.getJSON('getConso.php?delta=' + Math.floor(deltaRequest/1000) + '&minDate=' + Math.floor(minRequest/1000) + '&maxDate=' + Math.floor(maxRequest/1000), createChart);
};

$(document).ready(function()
{
  updateChart();

  $( "#radio" ).buttonset();

  $( "#prev" ).button().click(function( event ) {
    event.preventDefault();
    prev();
  });

  $( "#next" ).button().click(function( event ) {
    event.preventDefault();
    next();
  });

  $( "#day" ).click(function(){
    setMode(MODE_DAY);
  });

  $( "#week" ).click(function(){
    setMode(MODE_WEEK);
  });

  $( "#month" ).click(function(){
    setMode(MODE_MONTH);
  });

  $( "#year" ).click(function(){
    setMode(MODE_YEAR);
  });

  $( "#all" ).click(function(){
    setMode(MODE_ALL);
  });

});

		</script>
</head>

<body>
    <div id="header">
    <div>
      <h1>Consommation EDF</h1>
    </div>
  </div>
  <form>
    <div id="radio">
      <button id="prev"><<</button>
      <input type="radio" id="day" name="radio" checked="checked"><label for="day">Today</label>
      <input type="radio" id="week" name="radio"><label for="week">This week</label>
      <input type="radio" id="month" name="radio"><label for="month">This month</label>
      <input type="radio" id="year" name="radio"><label for="year">This year</label>
      <input type="radio" id="all" name="radio"><label for="all">all</label>
      <button id="next">>></button>
    </div>
  </form>
  <div id="container_puissance" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

  </div>
</body>
</html>

