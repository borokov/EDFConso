<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
  <?php
    include("connectSql.php");
    //On se connecte
    $base = connectMaBase();
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
MODE_ALL_YEARS = 6;

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
    case MODE_ALL_YEARS:
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
    case MODE_ALL_YEARS:
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
			// hour0 is 0:00:00
			hour0 = new Date(m_selectedLastDate);
      hour0.setHours(0);
      hour0.setMinutes(0);
      hour0.setSeconds(0);
		  // hour24 is 23:59:59
			hour24 = new Date(m_selectedLastDate);
      hour24.setHours(23);
      hour24.setMinutes(59);
      hour24.setSeconds(59);

      asyncCreateChart(hour0, hour24);
      break;
    case MODE_WEEK:
			monday = new Date(m_selectedLastDate);
			monday.setDate(monday.getDate() - monday.getDay() + (monday.getDay() == 0 ? -6:1));
			monday.setHours(0);
      monday.setMinutes(0);
      monday.setSeconds(0);
			
			sunday = new Date(monday);
			sunday.setDate(monday.getDate() + 6);
			sunday.setHours(23);
      sunday.setMinutes(59);
      sunday.setSeconds(59);

      asyncCreateChart(monday, sunday);
      break
    case MODE_MONTH:
			// get 1st day and last days of current selected month
			firstOfMonth = new Date(m_selectedLastDate);
			firstOfMonth.setDate(1);
		  firstOfMonth.setHours(0);
      firstOfMonth.setMinutes(0);
      firstOfMonth.setSeconds(0);
			
			lastOfMonth = new Date(firstOfMonth);
			lastOfMonth.setMonth(firstOfMonth.getMonth() + 1);
		  lastOfMonth.setHours(23);
      lastOfMonth.setMinutes(59);
      lastOfMonth.setSeconds(59);
			
      asyncCreateChart(firstOfMonth, lastOfMonth);
      break;
    case MODE_YEAR:
			firstDay = new Date(m_selectedLastDate);
			firstDay.setMonth(0);
			firstDay.setDate(1);
		  firstDay.setHours(0);
      firstDay.setMinutes(0);
      firstDay.setSeconds(0);
			
			lastDay = new Date(m_selectedLastDate);
			lastDay.setMonth(11);
			lastDay.setDate(31);
		  lastDay.setHours(23);
      lastDay.setMinutes(59);
      lastDay.setSeconds(59);
			
      asyncCreateChart(firstDay, lastDay);
      break;
    case MODE_ALL:
    case MODE_ALL_YEARS:
      asyncCreateChart(new Date(0), new Date(Date.now()));
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
    case MODE_ALL_YEARS:
      break;
  }
  return title;
}
		
function getLegend()
{
  switch(m_selectedMode)
  {
    case MODE_DAY:
      return "conso [kw]";
    case MODE_WEEK:
      return "conso [kwh/j]"
    case MODE_MONTH:
      return "conso [kwh/j]"
    case MODE_YEAR:
      return "conso [kwh/mois]"
    case MODE_ALL:
      return "conso [kwh/mois]"
    case MODE_ALL_YEARS:
      return "conso [kwh/ans]"
  }
  return title;	
}

function createChart(csv)
{
    $('#container_puissance').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: dateToTitle(m_selectedLastDate)
        },
        yAxis: {
            //min: 0,
            //max: 20,
						title: {
					  	text: getLegend()
						},
            startOnTick: false,
            endOnTick: false
        },
			  plotOptions: {
        	column: {
						//stacking: 'normal',
            pointPadding: 0,
            borderWidth: 0,
						groupPadding: 0.1
        	}
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
  
  switch(m_selectedMode)
  {
    case MODE_DAY:
      // have to set little bit less than 1 hour else getConso.php bug because measures
      // are done ~= evry hours
      deltaRequest = 0.8*DELTA_HOUR;
      break;
    case MODE_WEEK:
      // have to set little bit less than 1 hour else getConso.php bug because measures
      // are done ~= evry hours
      deltaRequest = DELTA_DAY;
      break;
    case MODE_MONTH:
      deltaRequest = DELTA_DAY;
      break;
    case MODE_YEAR:
      deltaRequest = DELTA_MONTH;
      break;
    case MODE_ALL:
      deltaRequest = DELTA_MONTH;
      break;
    case MODE_ALL_YEARS:
      deltaRequest = DELTA_YEAR;
      break;
      
  }

  minRequest = minDate.getTime();
  maxRequest = maxDate.getTime();
	
  $.getJSON('getConso.php?delta=' + Math.floor(deltaRequest/1000) + '&minDate=' + Math.floor(minRequest/1000) + '&maxDate=' + Math.floor(maxRequest/1000), createChart);
};

$(document).ready(function()
{
  updateChart();


  Highcharts.setOptions({
    global: {
      timezoneOffset: -2 * 60
    }
  });

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
  
  $( "#all_years" ).click(function(){
    setMode(MODE_ALL_YEARS);
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
	    <div id="releve">
      <ul>
        <li>
					Dernier relevé:
					<?php
                // valeur fin de releve
                $sqlDate = "SELECT date FROM conso ORDER BY conso.date DESC ";
                $reqDate = mysqli_query($base, $sqlDate) or die("Erreur SQL !<br />".$sqlDate."<br />".mysqli_error($base));  
                $date = mysqli_fetch_array($reqDate);
                echo($date['date'] );
					?>
				</li>
				<li>
					Heures pleines
					<?php
                // valeur fin de releve
                $sqlHP = "SELECT hp FROM conso ORDER BY conso.date DESC ";
                $reqHP = mysqli_query($base, $sqlHP) or die("Erreur SQL !<br />".$sqlHP."<br />".mysqli_error($base));  
                $HP = mysqli_fetch_array($reqHP);
                echo(round($HP['hp']/1000));
					?>
          kwh
				</li>
				<li>
					Heures creuses
					<?php
                // valeur fin de releve
                $sqlHC = "SELECT hc FROM conso ORDER BY conso.date DESC ";
                $reqHC = mysqli_query($base, $sqlHC) or die("Erreur SQL !<br />".$sqlHC."<br />".mysqli_error($base));  
                $HC = mysqli_fetch_array($reqHC);
                echo(round($HC['hc']/1000));
					?>
          kwh
				</li>
			</ul>
    </div>
  <form>
    <div id="radio">
      <button id="prev"><<</button>
      <input type="radio" id="day" name="radio" checked="checked"><label for="day">Today</label>
      <input type="radio" id="week" name="radio"><label for="week">This week</label>
      <input type="radio" id="month" name="radio"><label for="month">This month</label>
      <input type="radio" id="year" name="radio"><label for="year">This year</label>
      <input type="radio" id="all" name="radio"><label for="all">all</label>
      <input type="radio" id="all_years" name="radio"><label for="all_years">all years</label>
      <button id="next">>></button>
    </div>
  </form>
  <div id="container_puissance" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

  </div>
</body>
</html>

