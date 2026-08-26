

    var docentesAmbo = {{ $totaldocAmboCount }};
    var directoresAmbo = {{ $totaldirAmboCount }};
    var pcAmbo = {{ $totalpcAmboCount }};
    
    var docentesHco = {{ $totaldocHuanucoCount }};
    var directoresHco = {{ $totaldirHuanucoCount }};
    var pcHco = {{ $totalpcHuanucoCount }};


    var donutChartCanvas = $('#pieChart').get(0).getContext('2d')
    var donutData        = {
      labels: [
          'Ambo :'+{{ $totaluserAmboCount}},
          'Huánuco:'+{{ $totaluserHuanucoCount}},
          'Dos de Mayo:'+{{ $totaluserDosdeMayoCount}},
          'Huamalies:'+{{ $totaluserHuamaliesCount}},
          'Leoncio Prado:'+{{ $totaluserPradoCount}},
          'Pachitea:'+{{ $totaluserPachiteaCount}},
          'Puerto Inca:'+{{ $totaluserIncaCount}},
          'Yarowilca:'+{{ $totaluserYarowilcaCount}},
          'Marañon:'+{{ $totaluserMarañonCount}},
          'Lauricocha:'+{{ $totaluserLauricochaCount}},
          'Huacaybamba:'+{{ $totaluserHuacaybambaCount}},
      ],
      datasets: [
        {
          data: [{{ $totaluserAmboCount}}, {{ $totaluserHuanucoCount }}, {{ $totaluserDosdeMayoCount }}, {{ $totaluserHuamaliesCount }},
          {{ $totaluserPradoCount }}, {{ $totaluserPachiteaCount }}, {{ $totaluserIncaCount }}, {{ $totaluserYarowilcaCount }}, 
          {{ $totaluserMarañonCount }}, {{ $totaluserLauricochaCount }}, {{ $totaluserHuacaybambaCount }}],
          backgroundColor : ['#f56954', '#f39c12', '#99F326', '#41C723', '#00c0ef', '#3c8dbc', '#8224D5', '#F136EC', '#f56954', '#f39c12', '#99F326'],
        }
      ]
    }
    var donutOptions = {
        maintainAspectRatio: false,
        responsive: true,
        tooltips: {
            callbacks: {
                label: function (tooltipItem, data) {
                    var index = tooltipItem.index;
                    var labels = data.labels[index].split(':');
                    if (index === 0) {
                        var amboText = 'Ambo (Docentes: ' + docentesAmbo + ', Directores: ' + directoresAmbo + ', Profesor Coordinador: ' + pcAmbo + ')';
                        var huanucoText = 'Huánuco (Docentes: ' + docentesHco + ', Directores: ' + directoresHco + ', Profesor Coordinador: ' + pcHco + ')';
                        return index === 0 ? amboText : huanucoText;
                    } else {
                        return data.labels[index];
                    }
                }
            }
        }
    };

    // Crea el pie chart
    new Chart(donutChartCanvas, {
        type: 'doughnut',
        data: donutData,
        options: donutOptions
    });
  });

    
