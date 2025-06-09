// charts.js
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
    var data = google.visualization.arrayToDataTable([
        ['Task', 'Hours per Day'],
        ['Fang', 32],
        ['Mpongwè', 15],
        ['Obamba', 14],
        ['Punu', 12],
        ['Autres', 27]
    ]);

    var options = {
        title: 'Répartition des Ethnies en Pourcentage',
          width: 500,
	  height: 200,
	  is3D: true,
        backgroundColor: '#ECECEC'  // Utilisez la même couleur que le body
    };

    var chart = new google.visualization.PieChart(document.getElementById('piechart'));
    chart.draw(data, options);
}

////////////////////////////////////////////
google.charts.load('current', {packages: ['corechart', 'line']});
google.charts.setOnLoadCallback(drawAxisTickColors);

function drawAxisTickColors() {
      var data = new google.visualization.DataTable();
      data.addColumn('number', 'X');
      data.addColumn('number', 'H');
      data.addColumn('number', 'F');

      data.addRows([
           [2000, 56, 60],   [2010, 60, 64],  [2015, 62, 68],   [2019, 64, 70],  [2021, 63, 68]
      ]);

      var options = {
        hAxis: {
          title: 'Année',
          textStyle: {
            color: '#01579b',
            fontSize: 20,
            fontName: 'Roboto',
            bold: true,
            italic: true
          },
          titleTextStyle: {
            color: '#01579b',
            fontSize: 16,
            fontName: 'Roboto',
            bold: false,
            italic: true
          },
		      ticks: [2000, 2010, 2015, 2019, 2021] // Ajout des années aux ticks
            
        },
        vAxis: {
          title: 'L\'espérance de vie à la naissance',
          textStyle: {
            color: '#1a237e',
            fontSize: 10,
            bold: true
          },
          titleTextStyle: {
            color: '#1a237e',
            fontSize: 14,
            bold: true
          }
        },
        colors: ['#a52714', '#097138'],
        width: 350,
        backgroundColor: '#ECECEC'  // Utilisez la même couleur que le body
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
      chart.draw(data, options);
    }
///////////////////////////////////////
//
google.charts.load('current', {'packages':['bar']});
      google.charts.setOnLoadCallback(drawStuff);

      function drawStuff() {
        var data = new google.visualization.arrayToDataTable([
          ['Move', 'Percentage'],
          ["0-14", 24.1],
          ["15-64", 72.3],
          ["65+", 3.66]
        ]);

        var options = {
          width: 350,
          legend: { position: 'none' },
          chart: {
            title: 'Répartition par âge de la population',
            subtitle: '' },
          axes: {
            x: {
              0: { side: 'top', label: 'Âge'} // Top x-axis.
            }
          },
          bar: { groupWidth: "60%" },
          backgroundColor: '#ECECEC'  // Utilisez la même couleur que le body
        };

        var chart = new google.charts.Bar(document.getElementById('top_x_div'));
        // Convert the Classic options to Material options.
        chart.draw(data, google.charts.Bar.convertOptions(options));
      };
