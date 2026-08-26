@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .content {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    }
    .chart-window {
        position: relative;
        display: inline-block;
        width: 450px;
        background-color: #fff;
        border: 1px solid #ccc;
        box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        margin: 10px;
    }

    .chart-title-bar {
        background-color: #007bff;
        color: #fff;
        padding: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chart-title {
        margin: 0;
    }

    .chart-controls {
        display: flex;
    }

    .chart-control {
        background-color: #007bff;
        color: #fff;
        padding: 5px;
        margin-right: 5px;
        cursor: pointer;
    }
    

    .chart-content {
        padding: 10px;
    }

    .chart-graphics {
        min-height: 300px;
    }
    /* Agrega estas clases */
    .centered-chart .chart-title-bar{
        display: flex;
        justify-content: flex-end; /* Alinea los controles en la parte superior */
        background-color: transparent; /* Fondo transparente para los controles */
        position: absolute; /* Ajusta la posición de los controles */
        top: 0; /* Ajusta la posición de los controles */
        right: 0;
    }

    .maximized-chart {
        width: 100%;
        height: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: white; /* Agrega un fondo blanco para el gráfico maximizado */
        overflow: auto;
    }
</style>
@endsection

@section('title', 'Accion')

@section('content_header')

@stop

@section('content')
<div class="content">
    <!-- DONUT CHART -->
    <div class="card card-danger">
        <div class="card-header">
          <h3 class="card-title">Donut Chart</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <canvas id="myChart1" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card 
    <div class="chart-window" id="chart-window1">
        <div class="chart-title-bar">
            <h2 class="chart-title">Gráfico 1</h2>
            <div class="chart-controls">
                <div class="chart-control" onclick="toggleChart(1, 'minimize')">-</div>
                <div class="chart-control" onclick="toggleChart(1, 'maximize')">□</div>
                <div class="chart-control" onclick="reloadChart(1)">↻</div>
            </div>
        </div>
        <div class="chart-graphics">
            <div class="chart-content" id="chart-content1">
                <canvas id="myChart1" width="400" height="400"></canvas>
            </div>
        </div>
    </div>
    
    <div class="chart-window" id="chart-window2">
        <div class="chart-title-bar">
            <h2 class="chart-title">Gráfico 2</h2>
            <div class="chart-controls">
                <div class="chart-control" onclick="toggleChart(2, 'minimize')">-</div>
                <div class="chart-control" onclick="toggleChart(2, 'maximize')">□</div>
                <div class="chart-control" onclick="reloadChart(2)">↻</div>
            </div>
        </div>
        <div class="chart-graphics">
            <div class="chart-content" id="chart-content2">
                <canvas id="myChart2" width="400" height="400"></canvas>
            </div>
        </div>
    </div>
    
    <div class="chart-window" id="chart-window3">
        <div class="chart-title-bar">
            <h2 class="chart-title">Gráfico 3</h2>
            <div class="chart-controls">            
                <div class="chart-control" onclick="toggleChart(3, 'minimize')">-</div>
                <div class="chart-control" onclick="toggleChart(3, 'maximize')">□</div>
                <div class="chart-control" onclick="reloadChart(3)">↻</div>
            </div>
        </div>
        <div class="chart-graphics">
            <div class="chart-content" id="chart-content3">
                <canvas id="myChart3" width="400" height="400"></canvas>
            </div>
        </div>
    </div>-->
</div>
        
@stop

@section('css')
 
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    var ctx1 = document.getElementById('myChart1').getContext('2d');
    var myChart1 = new Chart(ctx1, {
        type: 'bar',/*type pie*/
        data: {
            labels: ['Agendas', 'Evidencias', 'Informes'],
            datasets: [{
                label: 'Cantidad de registros',
                data: [{{ $agendasCount }}, {{ $evidenciasCount }}, {{ $informesCount }}],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    var ctx2 = document.getElementById('myChart2').getContext('2d');
    var myChart2 = new Chart(ctx2, {
        type: 'pie',/*type pie*/
        data: {
            labels: ['Agendas', 'Evidencias', 'Informes'],
            datasets: [{
                label: 'Cantidad de registros',
                data: [{{ $agendasCount }}, {{ $evidenciasCount }}, {{ $informesCount }}],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Configuración del tercer gráfico
    var ctx3 = document.getElementById('myChart3').getContext('2d');
    var myChart3 = new Chart(ctx3, {
        type: 'doughnut',/*type pie*/
        data: {
            labels: ['Agendas', 'Evidencias', 'Informes'],
            datasets: [{
                label: 'Cantidad de registros',
                data: [{{ $agendasCount }}, {{ $evidenciasCount }}, {{ $informesCount }}],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
<script>
    const chartMinimized = {};
    function toggleChart(chartNumber, action) {
        const chartWindow = document.querySelector(`#chart-window${chartNumber}`);
        const chartGraphics = chartWindow.querySelector('.chart-graphics');
        
        if (action === 'minimize') {
            chartGraphics.style.display = chartGraphics.style.display === 'none' ? 'block' : 'none';
            chartWindow.classList.remove('maximized-chart');
        } else if (action === 'maximize') {
            chartGraphics.style.display = 'block';
            chartWindow.classList.add('maximized-chart'); 
            chartWindow.classList.remove('centered-chart');
        }
    }
    function reloadChart(chartNumber) {
        // Implementa aquí la lógica para recargar el gráfico
    } 

</script>   
@stop