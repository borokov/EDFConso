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


  <script type="text/javascript">

DELTA_HOUR = 3600;
DELTA_DAY = 24 * 3600;
DELTA_WEEK = 7 * DELTA_DAY;
DELTA_MONTH = 30 * DELTA_DAY;
DELTA_YEAR = 12 * DELTA_MONTH;

function createChart(csv)
{
    $('#container_puissance').highcharts({
        chart: {
            type: 'spline',
            zoomType: 'x',
            panning: true,
            events: {
              selection: function (event) {
                          asyncCreateChart(Math.floor(event.xAxis[0].min/1000), Math.floor(event.xAxis[0].max/1000));
                          }
            }
        },
        title: {
            text: 'Puissance moyenne'
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            type: 'datetime',
            title: {
                text: 'Date'
            }
        },
        yAxis: {
            title: {
                text: 'Puissance (W)'
            },
        },
        tooltip: {
            headerFormat: '<b>{series.name}</b><br>',
            pointFormat: '{point.x:%e. %b}: {point.y:.0f} W'
        },

        series: [{
            name: 'Consommation HC',
            // Define the data points. All series have a dummy year
            // of 1970/71 in order to be compared on the same x axis. Note
            // that in JavaScript, months start at 0 for January, 1 for February etc.
            data: csv.data
        }, {
            name: 'Consommation HP',
            data: [
            ]
        }]
    });
};

function asyncCreateChart(minDate, maxDate){
  deltaChart = maxDate - minDate;
  deltaRequest = 0;
  maxRequest = maxDate + 0.2 * deltaRequest;
  // estimate how many point there will be in chart and adjust delta of request

  // a few days
  if ( deltaChart < 50 * DELTA_HOUR )
  {
    deltaRequest = DELTA_HOUR
  }

  // a few weeks
  else if( deltaChart < 50 * DELTA_DAY )
  {
    deltaRequest = DELTA_DAY
  }

  // a few month
  else if ( deltaChart < 50 * DELTA_WEEK )
  {
    deltaRequest = DELTA_WEEK;
  }
  else
  {
    deltaRequest = DELTA_MONTH;
  }

  $.getJSON('getConso.php?delta=' + deltaRequest + '&minDate=' + Math.floor(minDate) + '&maxDate=' + Math.floor(maxRequest), createChart);
};

$(document).ready(function(){
  asyncCreateChart(0, Date.now() / 1000);

  $( "#radio" ).buttonset();
  
  $( "#day" ).click(function(){
    asyncCreateChart(Date.now()/1000 - DELTA_DAY, Date.now() / 1000);
  });

  $( "#week" ).click(function(){
    asyncCreateChart(Date.now()/1000 - DELTA_WEEK, Date.now() / 1000);
  });
  
  $( "#month" ).click(function(){
    asyncCreateChart(Date.now()/1000 - DELTA_MONTH, Date.now() / 1000);
  });

  $( "#year" ).click(function(){
    asyncCreateChart(Date.now()/1000 - DELTA_YEAR, Date.now() / 1000);
  });

  $( "#all" ).click(function(){
    asyncCreateChart(0, Date.now() / 1000);
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
      <input type="radio" id="day" name="radio"><label for="day">Today</label>
      <input type="radio" id="week" name="radio" checked="checked"><label for="week">This week</label>
      <input type="radio" id="month" name="radio"><label for="month">This month</label>
      <input type="radio" id="year" name="radio"><label for="year">This year</label>
      <input type="radio" id="all" name="radio"><label for="all">all</label>
    </div>
  </form>
  <div id="container_puissance" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

  </div>
</body>
</html>

