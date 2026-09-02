@extends('adminlte::page')
@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<style>
  #contenidoExportarUsuario {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
  }
  #contenidoExportarInstitucion {
    width: 100%;
    max-width: 400px; 
    margin: 0 auto; 
  }
  #contenidoExportarAccion {
    width: 100%;
    max-width: 400px; 
    margin: 0 auto; 
  }
  #contenidoExportarDifusion {
    width: 100%;
    max-width: 400px; 
    margin: 0 auto; 
  }  
  #contenidoExportarEvidencia {
    width: 100%;
    max-width: 400px; 
    margin: 0 auto; 
  }
  #contenidoExportarInforme {
    width: 100%;
    max-width: 400px; 
    margin: 0 auto; 
  }  
  #contenidoExportarPlan {
    width: 100%;
    max-width: 400px; 
    margin: 0 auto; 
  }
  #contenidoExportarProduccion {
    width: 100%;
    max-width: 400px; 
    margin: 0 auto; 
  }  
  #contenidoExportarAgenda {
    width: 100%;
    max-width: 400px; 
    margin: 0 auto; 
  }

  .custom-checkbox input[type="checkbox"] {
    display: none; /* Oculta el checkbox original */
  }

  .custom-checkbox label::before {
      content: ""; /* Crea un elemento de antes del label */
      display: inline-block;
      width: 20px; /* Ancho del checkbox personalizado */
      height: 20px; /* Alto del checkbox personalizado */
      border: 1px solid #ccc; /* Bordes del checkbox personalizado */
      margin-right: 5px; /* Espacio entre el checkbox y el texto */
      border-radius: 3px; /* Bordes redondeados */
  }

  .custom-checkbox input[type="checkbox"]:checked + label::before {
      background-color: #007bff; /* Cambia el color de fondo cuando el checkbox está marcado */
  }
</style>
@endsection

@section('title', 'Accion')

@section('content_header')

@stop

@section('content')
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Bienvenido</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
<section class="content">
    <div class="container-fluid">
    <!-- Info boxes -->
    <div class="row">
        <!-- ./col -->

      {{-- Docentes, Directores y Profesores Coordinadores --}}  
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
              @foreach ($data as $item)
                <h3 id="totaldocentes">{{ $item->totaldocentes }}</h3>
              @endforeach
                <p>Docentes y Directores Registrados</p>
              </div>
              <div class="icon">
                <i class="fas fa-users"></i>
              </div>              
              <a href="#" class="small-box-footer" data-bs-toggle="modal" data-bs-target="#myModalUser" data-toggle="modal" data-target="#myModalUser">
                Detalles <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
        </div>
        <div class="modal fade" id="myModalUser" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 800px;">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cantidad de Docentes, Directores y Profesores Coordinadores</h5>
                <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <!-- Aquí puedes colocar tu gráfico -->
                <canvas id="pieChartUser" style="min-height: 400px; height: 400px; max-height: 400px; width: 100%;"></canvas>
                <div class="container my-5" id="contenidoExportarUsuario">
                  <form action="{{ route('export', 'usuario') }}" method="post" class="my-4 mx-auto text-center">
                      @csrf<!--
                        <div class="form-row">
                          <div class="form-group col-md-4">
                              <label for="checkbox_docente">Docente:</label>
                              <div class="form-check custom-checkbox">
                                  <input type="checkbox" class="form-check-input" id="checkbox_docente" name="checkbox_docente">
                                  <label class="form-check-label" for="checkbox_docente"></label>
                              </div>
                          </div>
                          <div class="form-group col-md-4">
                              <label for="checkbox_directo">Director:</label>
                              <div class="form-check custom-checkbox">
                                  <input type="checkbox" class="form-check-input" id="checkbox_directo" name="checkbox_directo">
                                  <label class="form-check-label" for="checkbox_directo"></label>
                              </div>
                          </div>
                          <div class="form-group col-md-4">
                              <label for="checkbox_pc">PC:</label>
                              <div class="form-check custom-checkbox">
                                  <input type="checkbox" class="form-check-input" id="checkbox_pc" name="checkbox_pc">
                                  <label class="form-check-label" for="checkbox_pc"></label>
                              </div>
                          </div>
                      </div>-->
                      <button type="submit" class="btn btn-success btn-block mt-3 rounded-pill animated bounce">Exportar a Excel</button>
                  </form>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      {{-- Docentes, Directores y Profesores Coordinadores --}}  

      {{-- Instituciones por ugel --}}  
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
              @foreach ($data as $item)
                <h3 id="totalinstituciones">{{ $item->totalinstituciones }}</h3>
              @endforeach
                <p>Instituciones Registrados</p>
              </div>
              <div class="icon">
                <i class="fas fa-school"></i>
              </div>                            
              <a href="#" class="small-box-footer" data-bs-toggle="modal" data-bs-target="#myModalInst" data-toggle="modal" data-target="#myModalInst">
                Detalles <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
        </div>
        
        <div class="modal fade" id="myModalInst" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 800px;">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cantidad de Instituciones por ugel</h5>
                <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <!-- Aquí puedes colocar tu gráfico -->
                <canvas id="pieChartInst" style="min-height: 400px; height: 400px; max-height: 400px; width: 100%;"></canvas>
                <div class="container my-5" id="contenidoExportarInstitucion">
                  <form action="{{ route('export', 'institucion') }}" method="post" class="my-4 mx-auto text-center">
                      @csrf<!--
                      <div class="form-row">
                          <div class="form-group col-md-6">
                              <label for="start_date">Fecha de inicio:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="start_date" name="start_date" required>
                          </div>
                          <div class="form-group col-md-6">
                              <label for="end_date">Fecha de fin:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="end_date" name="end_date" required>
                          </div>
                      </div>-->
                      <button type="submit" class="btn btn-success btn-block mt-3 rounded-pill animated bounce">Exportar a Excel</button>
                  </form>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      {{-- Instituciones por ugel --}} 

      {{-- Acciones registradas por ugel--}} 
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-secondary">
              <div class="inner">
              @foreach ($data as $item)
                <h3 id="totalacciones">{{ $item->totalacciones }}</h3>
              @endforeach
                <p>Registraron Acciones</p>
              </div>
              <div class="icon">
                <i class="fas fa-users"></i>
              </div>                                         
              <a href="#" class="small-box-footer" data-bs-toggle="modal" data-bs-target="#myModalAcciones" data-toggle="modal" data-target="#myModalAcciones">
                Detalles <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
        </div>    
      
        <div class="modal fade" id="myModalAcciones" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 800px;">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cantidad de Acciones por ugel</h5>
                <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <!-- Aquí puedes colocar tu gráfico -->
                <canvas id="pieChartAccion" style="min-height: 400px; height: 400px; max-height: 400px; width: 100%;"></canvas>
                <div class="container my-5" id="contenidoExportarAccion">
                  <form action="{{ route('export', 'accion') }}" method="post" class="my-4 mx-auto text-center">
                      @csrf
                      <div class="form-row">
                          <div class="form-group col-md-6">
                              <label for="start_date">Fecha de inicio:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="start_date" name="start_date" required>
                          </div>
                          <div class="form-group col-md-6">
                              <label for="end_date">Fecha de fin:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="end_date" name="end_date" required>
                          </div>
                      </div>
                      <button type="submit" class="btn btn-success btn-block mt-3 rounded-pill animated bounce">Exportar a Excel</button>
                  </form>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      {{-- Acciones registradas por ugel--}} 

      {{-- Difuciones registradas por ugel--}} 

        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
              @foreach ($data as $item)
                <h3 id="totaldifusiones">{{ $item->totaldifusiones }}</h3>
                @endforeach
                <p>Registraron Difusiones</p>
              </div>              
              <div class="icon">
              <i class="fas fa-users"></i>
              </div>                                                     
              <a href="#" class="small-box-footer" data-bs-toggle="modal" data-bs-target="#myModalDifusiones" data-toggle="modal" data-target="#myModalDifusiones">
                Detalles <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
        </div>

        <div class="modal fade" id="myModalDifusiones" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 800px;">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cantidad de Difusiones por ugel</h5>
                <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <!-- Aquí puedes colocar tu gráfico -->
                <canvas id="pieChartDifusion" style="min-height: 400px; height: 400px; max-height: 400px; width: 100%;"></canvas>
                <div class="container my-5" id="contenidoExportarDifusion">
                  <form action="{{ route('export', 'difusion') }}" method="post" class="my-4 mx-auto text-center">
                      @csrf
                      <div class="form-row">
                          <div class="form-group col-md-6">
                              <label for="start_date">Fecha de inicio:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="start_date" name="start_date" required>
                          </div>
                          <div class="form-group col-md-6">
                              <label for="end_date">Fecha de fin:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="end_date" name="end_date" required>
                          </div>
                      </div>
                      <button type="submit" class="btn btn-success btn-block mt-3 rounded-pill animated bounce">Exportar a Excel</button>
                  </form>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      {{-- Difuciones registradas por ugel--}} 


      {{-- Evidencias registradas por ugel--}} 
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
              @foreach ($data as $item)
                <h3 id="totalevidencias">{{ $item->totalevidencias }}</h3>
                @endforeach
                <p>Registraron Evidencias</p>
              </div>              
              <div class="icon">
              <i class="fas fa-users"></i>
              </div>                                               
              <a href="#" class="small-box-footer" data-bs-toggle="modal" data-bs-target="#myModalEvidencias" data-toggle="modal" data-target="#myModalEvidencias">
                Detalles <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
        </div>
        

        <div class="modal fade" id="myModalEvidencias" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 800px;">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cantidad de Evidencias por ugel</h5>
                <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <!-- Aquí puedes colocar tu gráfico -->
                <canvas id="pieChartEvidencia" style="min-height: 400px; height: 400px; max-height: 400px; width: 100%;"></canvas>
                <div class="container my-5" id="contenidoExportarEvidencia">
                  <form action="{{ route('export', 'evidencia') }}" method="post" class="my-4 mx-auto text-center">
                      @csrf
                      <div class="form-row">
                          <div class="form-group col-md-6">
                              <label for="start_date">Fecha de inicio:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="start_date" name="start_date" required>
                          </div>
                          <div class="form-group col-md-6">
                              <label for="end_date">Fecha de fin:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="end_date" name="end_date" required>
                          </div>
                      </div>
                      <button type="submit" class="btn btn-success btn-block mt-3 rounded-pill animated bounce">Exportar a Excel</button>
                  </form>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      {{-- Evidencias registradas por ugel--}} 


      {{-- Informes registradas por ugel--}} 
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
              @foreach ($data as $item)
                <h3 id="totalinformes">{{ $item->totalinformes }}</h3>
                @endforeach
                <p>Registraron Informes</p>
              </div>              
              <div class="icon">
              <i class="fas fa-users"></i>
              </div>
              <a href="#" class="small-box-footer" data-bs-toggle="modal" data-bs-target="#myModalInformes" data-toggle="modal" data-target="#myModalInformes">
                Detalles <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
        </div>


        <div class="modal fade" id="myModalInformes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 800px;">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cantidad de Informes por ugel</h5>
                <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <!-- Aquí puedes colocar tu gráfico -->
                <canvas id="pieChartInforme" style="min-height: 400px; height: 400px; max-height: 400px; width: 100%;"></canvas>
                <div class="container my-5" id="contenidoExportarInforme">
                  <form action="{{ route('export', 'informe') }}" method="post" class="my-4 mx-auto text-center">
                      @csrf<!--
                      <div class="form-row">
                          <div class="form-group col-md-6">
                              <label for="start_date">Fecha de inicio:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="start_date" name="start_date" required>
                          </div>
                          <div class="form-group col-md-6">
                              <label for="end_date">Fecha de fin:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="end_date" name="end_date" required>
                          </div>
                      </div>-->
                      <button type="submit" class="btn btn-success btn-block mt-3 rounded-pill animated bounce">Exportar a Excel</button>
                  </form>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      {{-- Informes registradas por ugel--}} 


      {{-- Planes registradas por ugel--}} 
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-light">
              <div class="inner">
              @foreach ($data as $item)
                <h3 id="totalplans">{{ $item->totalplans }}</h3>
                @endforeach
                <p>Registraron Planes</p>
              </div>              
              <div class="icon">
              <i class="fas fa-users"></i>
              </div>
              <a href="#" class="small-box-footer" data-bs-toggle="modal" data-bs-target="#myModalPlanes" data-toggle="modal" data-target="#myModalPlanes">
                Detalles <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
        </div>

        <div class="modal fade" id="myModalPlanes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 800px;">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cantidad de Planes por ugel</h5>
                <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <!-- Aquí puedes colocar tu gráfico -->
                <canvas id="pieChartPlan" style="min-height: 400px; height: 400px; max-height: 400px; width: 100%;"></canvas>
                <div class="container my-5" id="contenidoExportarPlan">
                  <form action="{{ route('export', 'plan') }}" method="post" class="my-4 mx-auto text-center">
                      @csrf<!--
                      <div class="form-row">
                          <div class="form-group col-md-6">
                              <label for="start_date">Fecha de inicio:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="start_date" name="start_date" required>
                          </div>
                          <div class="form-group col-md-6">
                              <label for="end_date">Fecha de fin:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="end_date" name="end_date" required>
                          </div>
                      </div>-->
                      <button type="submit" class="btn btn-success btn-block mt-3 rounded-pill animated bounce">Exportar a Excel</button>
                  </form>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      {{-- Planes registradas por ugel--}} 

      
      {{-- Producciones registradas por ugel--}} 
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
              @foreach ($data as $item)
                <h3 id="totalproducciones">{{ $item->totalproducciones }}</h3>
                @endforeach
                <p>Registraron Producciones</p>
              </div>              
              <div class="icon">
              <i class="fas fa-users"></i>
              </div>
              <a href="#" class="small-box-footer" data-bs-toggle="modal" data-bs-target="#myModalProducciones" data-toggle="modal" data-target="#myModalProducciones">
                Detalles <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
        </div>

        <div class="modal fade" id="myModalProducciones" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 800px;">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cantidad de Producciones de textos infantiles por ugel</h5>
                <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <!-- Aquí puedes colocar tu gráfico -->
                <canvas id="pieChartPro" style="min-height: 400px; height: 400px; max-height: 400px; width: 100%;"></canvas>
                <div class="container my-5" id="contenidoExportarProduccion">
                  <form action="{{ route('export', 'produccion') }}" method="post" class="my-4 mx-auto text-center">
                      @csrf<!--
                      <div class="form-row">
                          <div class="form-group col-md-6">
                              <label for="start_date">Fecha de inicio:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="start_date" name="start_date" required>
                          </div>
                          <div class="form-group col-md-6">
                              <label for="end_date">Fecha de fin:</label>
                              <input type="date" class="form-control shadow-sm rounded" id="end_date" name="end_date" required>
                          </div>
                      </div>-->
                      <button type="submit" class="btn btn-success btn-block mt-3 rounded-pill animated bounce">Exportar a Excel</button>
                  </form>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      {{-- Producciones registradas por ugel--}} 


      {{-- Agendas registradas por ugel--}} 
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-primary">
            <div class="inner">
            @foreach ($data as $item)
              <h3 id="totalagendas">{{ $item->totalagendas }}</h3>
              @endforeach
              <p>Registraron Agendas</p>
            </div>              
            <div class="icon">
            <i class="fas fa-users"></i>
            </div>
            <a href="#" class="small-box-footer" data-bs-toggle="modal" data-bs-target="#myModalAgendas" data-toggle="modal" data-target="#myModalAgendas">
              Detalles <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <div class="modal fade" id="myModalAgendas" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 800px;">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cantidad de Agendas de lectura por ugel</h5>
                <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <!-- Aquí puedes colocar tu gráfico -->
                <canvas id="pieChartAge" style="min-height: 400px; height: 400px; max-height: 400px; width: 100%;"></canvas>
                  <div class="container my-5" id="contenidoExportarAgenda">
                    <form action="{{ route('export', 'agenda') }}" method="post" class="my-4 mx-auto text-center">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="start_date">Fecha de inicio:</label>
                                <input type="date" class="form-control shadow-sm rounded" id="start_date" name="start_date" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="end_date">Fecha de fin:</label>
                                <input type="date" class="form-control shadow-sm rounded" id="end_date" name="end_date" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block mt-3 rounded-pill animated bounce">Exportar a Excel</button>
                    </form>
                </div>
              </div>              
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      {{-- Agendas registradas por ugel--}}
    </div>
        <!-- /.row -->
{{--
        <div class="row">
          <div class="col-md-12">
            <div class="card card-success">
              <div class="card-header">
                <h5 class="card-title">Vacunas Aplicadas a los Alumnos de la Región</h5>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <div class="row">
                  <div class="card-body">
                    <div class="chart">
                      <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                  </div>
              <!-- /.card-body -->
                  </div>
                  <!-- /.col -->
                </div>
                <!-- /.row -->
              </div>
              <!-- ./card-body -->
              
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
          <div class="col-md-8">
            <!-- MAP & BOX PANE -->
            <div class="card">

              <!-- /.card-header -->
             
                  
                <div class="card card-danger">
              <div class="card-header">
                <h3 class="card-title">Redes de Salud de la Región</h3>

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
                <canvas id="pieChart" style="min-height: 260px; height: 260px; max-height: 260px; max-width: 100%;"></canvas>
              </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
              <div class="col-md-13">
                <div class="card card-warning">
                  <div class="card-header">
                    <h5 class="card-title">Vacunas Aplicadas con Dosis a los Alumnos de la Región</h5>
    
                    <div class="card-tools">
                      <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                      </button>
                      <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body">
                    <div class="row">
                      <div class="card-body">
                        <div class="chart">
                          <canvas id="dosis" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                      </div>
                  <!-- /.card-body -->
                      </div>
                      <!-- /.col -->
                    </div>
                    <!-- /.row -->
                  </div>
                  <!-- ./card-body -->
                  
                </div>
                <!-- /.card -->
              </div>
              <!-- /.col -->


          <div class="col-md-4">
            <!-- Info Boxes Style 2 -->
            <div class="info-box mb-3 bg-primary">
              <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">SIS</span>
                <span class="info-box-number">{{ $totalSISCount }}</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
            <div class="info-box mb-3 bg-success">
              <span class="info-box-icon"><i class="fas fa-file-medical"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">EsSalud</span>
                <span class="info-box-number">{{ $totalEssaludCount }}</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
            <div class="info-box mb-3 bg-warning">
              <span class="info-box-icon"><i class="fas fa-file-medical-alt"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">EPS</span>
                <span class="info-box-number">{{ $totalEPSCount }}</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
            <div class="info-box mb-3 bg-info">
              <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Sanidad</span>
                <span class="info-box-number">{{ $totalSanidadCount }}</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
            <div class="info-box mb-3 bg-danger">
              <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Sin Seguro</span>
                <span class="info-box-number">{{ $totalSinseguroCount }}</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
--}}
              

            <!-- PRODUCT LIST -->

          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!--/. container-fluid -->
    </section>
    <!-- /.content -->
@stop



@section('js')
<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

<!-- AdminLTE -->
<script src="vendor/adminlte/dist/js/adminlte.js"></script>

<!-- OPTIONAL SCRIPTS -->
<script src="vendor/chart.js/Chart.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="vendor/adminlte/dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="vendor/jquery/jquery.min.js"></script>

<script>
/* Todo esto es para un cierto rango de fecha para exportar en excel

  // Obtén el elemento del input de fecha
  var inputFechaI = document.getElementById('start_date');
  var inputFechaF = document.getElementById('end_date');
  
  //Obtener la fecha actual menos 1 para seleccionar fecha inicial
  var fechaF = new Date();
  fechaF.setDate(fechaF.getDate() - 1);
  var fechaFActual = fechaF.toISOString().slice(0, 10);

  // Obtiene la fecha actual en el formato "yyyy-mm-dd"
  var fechaActual = new Date().toISOString().slice(0, 10);

  // Establece el atributo "min" en el input para bloquear fechas posteriores
  inputFechaI.setAttribute('max', fechaFActual);
  inputFechaI.setAttribute('min', '2023-03-01');

  inputFechaF.setAttribute('max', fechaActual);
  inputFechaF.setAttribute('min', '2023-03-01');  
/* Todo esto es para un cierto rango de fecha para exportar en excel*/
  
  var datepickerInputsI = document.querySelectorAll('#start_date');
  var datepickerInputsF = document.querySelectorAll('#end_date');

  // Itera sobre cada elemento y aplica las restricciones de fecha
  datepickerInputsI.forEach(function(input) {
    var fechaHoy = new Date();
    var fechaAnterior = new Date();
    fechaAnterior.setDate(fechaAnterior.getDate() - 1);
    var fechaAnteriorString = fechaAnterior.toISOString().slice(0, 10);
    var fechaHoyString = fechaHoy.toISOString().slice(0, 10);

    input.setAttribute('min', '2023-03-01');
    input.setAttribute('max', fechaAnteriorString);
  });

  // Itera sobre cada elemento y aplica las restricciones de fecha
  datepickerInputsF.forEach(function(input) {
    var fechaHoy = new Date();
    var fechaHoyString = fechaHoy.toISOString().slice(0, 10);

    input.setAttribute('min', '2023-03-01');
    input.setAttribute('max', fechaHoyString);
  });

</script>
<script>
$(document).ready(function () {
//Pie Chart Cantidad de Docentes Usuarios
  var ubicacionesU = [
    {
        label: 'Ambo',
        docentes: {{ $totaldocAmboCount }},
        directores: {{ $totaldirAmboCount }},
        pc: {{ $totalpcAmboCount }},
        usuarios: {{ $totaluserAmboCount }},
    },
    {
        label: 'Huánuco',
        docentes: {{ $totaldocHuanucoCount }},
        directores: {{ $totaldirHuanucoCount }},
        pc: {{ $totalpcHuanucoCount }},
        usuarios: {{ $totaluserHuanucoCount }},
    },
    {
        label: 'Dos de Mayo',
        docentes: {{ $totaldocDosdeMayoCount }},
        directores: {{ $totaldirDosdeMayoCount }},
        pc: {{ $totalpcDosdeMayoCount }},
        usuarios: {{ $totaluserDosdeMayoCount }},
    },
    {
        label: 'Huamalies',
        docentes: {{ $totaldocHuamaliesCount }},
        directores: {{ $totaldirHuamaliesCount }},
        pc: {{ $totalpcHuamaliesCount }},
        usuarios: {{ $totaluserHuamaliesCount }},
    },
    {
        label: 'Leoncio Prado',
        docentes: {{ $totaldocPradoCount }},
        directores: {{ $totaldirPradoCount }},
        pc: {{ $totalpcPradoCount }},
        usuarios: {{ $totaluserPradoCount }},
    },
    {
        label: 'Pachitea',
        docentes: {{ $totaldocPachiteaCount }},
        directores: {{ $totaldirPachiteaCount }},
        pc: {{ $totalpcPachiteaCount }},
        usuarios: {{ $totaluserPachiteaCount }},
    },
    {
        label: 'Puerto Inca',
        docentes: {{ $totaldocIncaCount }},
        directores: {{ $totaldirIncaCount }},
        pc: {{ $totalpcIncaCount }},
        usuarios: {{ $totaluserIncaCount }},
    },
    {
        label: 'Yarowilca',
        docentes: {{ $totaldocYarowilcaCount }},
        directores: {{ $totaldirYarowilcaCount }},
        pc: {{ $totalpcYarowilcaCount }},
        usuarios: {{ $totaluserYarowilcaCount }},
    },
    {
        label: 'Marañon',
        docentes: {{ $totaldocMarañonCount }},
        directores: {{ $totaldirMarañonCount }},
        pc: {{ $totalpcMarañonCount }},
        usuarios: {{ $totaluserMarañonCount }},
    },
    {
        label: 'Lauricocha',
        docentes: {{ $totaldocLauricochaCount }},
        directores: {{ $totaldirLauricochaCount }},
        pc: {{ $totalpcLauricochaCount }},
        usuarios: {{ $totaluserLauricochaCount }},
    },
    {
        label: 'Huacaybamba',
        docentes: {{ $totaldocHuacaybambaCount }},
        directores: {{ $totaldirHuacaybambaCount }},
        pc: {{ $totalpcHuacaybambaCount }},
        usuarios: {{ $totaluserHuacaybambaCount }},
    }
  ];

  var donutChartCanvasU = $('#pieChartUser').get(0).getContext('2d');

  var labelsU = ubicacionesU.map(function (ubicacion) {
      return `${ubicacion.label} : ${ubicacion.usuarios}`;
  });

  var dataU = ubicacionesU.map(function (ubicacion) {
      return ubicacion.usuarios;
  });

  var backgroundColors = ['#f56954', '#f39c12', '#99F326', '#dfc731', '#00c0ef', '#3c8dbc', '#8224D5', '#F136EC', '#41C723', '#bac7c1', '#fcbd9c'];

  var donutDataU = {
    labels: labelsU,
    datasets: [
        {
            data: dataU,
            backgroundColor: backgroundColors,
        },
    ],
  };

  var donutOptionsU = {
    maintainAspectRatio: false,
    responsive: true,
    tooltips: {
        callbacks: {
            label: function (tooltipItem, data) {
                var index = tooltipItem.index;
                return `${labelsU[index]} (Docentes: ${ubicacionesU[index].docentes}, Directores: ${ubicacionesU[index].directores}, Profesor Coordinador: ${ubicacionesU[index].pc})`;
            },
        },
    },
  };

  // Crea el pie chart
  new Chart(donutChartCanvasU, {
      type: 'doughnut',
      data: donutDataU,
      options: donutOptionsU,
  });

//Pie Chart Cantidad de Instituciones
  var ubicacionesI = [
      {
          label: 'Ambo',
          instituciones: {{ $totalinstitucionAmboCount }},
      },
      {
          label: 'Huánuco',
          instituciones: {{ $totalinstitucionHuanucoCount }},
      },
      {
          label: 'Dos de Mayo',
          instituciones: {{ $totalinstitucionDosdeMayoCount }},
      },
      {
          label: 'Huamalies',
          instituciones: {{ $totalinstitucionHuamaliesCount }},
      },
      {
          label: 'Leoncio Prado',
          instituciones: {{ $totalinstitucionPradoCount }},
      },
      {
          label: 'Pachitea',
          instituciones: {{ $totalinstitucionPachiteaCount }},
      },
      {
          label: 'Puerto Inca',
          instituciones: {{ $totalinstitucionIncaCount }},
      },
      {
          label: 'Yarowilca',
          instituciones: {{ $totalinstitucionYarowilcaCount }},
      },
      {
          label: 'Marañon',
          instituciones: {{ $totalinstitucionMarañonCount }},
      },
      {
          label: 'Lauricocha',
          instituciones: {{ $totalinstitucionLauricochaCount }},
      },
      {
          label: 'Huacaybamba',
          instituciones: {{ $totalinstitucionHuacaybambaCount }},
      }
    ];

  var donutChartCanvasI = $('#pieChartInst').get(0).getContext('2d');

  var labelsI = ubicacionesI.map(function (ubicacion) {
      return `${ubicacion.label} : ${ubicacion.instituciones}`;
  });

  var dataI = ubicacionesI.map(function (ubicacion) {
      return ubicacion.instituciones;
  });

  var backgroundColors = ['#f56954', '#f39c12', '#99F326', '#dfc731', '#00c0ef', '#3c8dbc', '#8224D5', '#F136EC', '#41C723', '#bac7c1', '#fcbd9c'];

  var donutDataI = {
      labels: labelsI,
      datasets: [
          {
              data: dataI,
              backgroundColor: backgroundColors,
          },
      ],
  };

  var donutOptionsI = {
      maintainAspectRatio: false,
      responsive: true,
      tooltips: {
          callbacks: {
              label: function (tooltipItem, data) {
                  var index = tooltipItem.index;
                  return `${labelsI[index]}`;
              },
          },
      },
  };

  // Crea el pie chart
  new Chart(donutChartCanvasI, {
      type: 'doughnut',
      data: donutDataI,
      options: donutOptionsI,
  });

//Pie Chart Cantidad de Acciones
  var ubicacionesAcc = [
    {
        label: 'Ambo',
        docentesXaccion: {{ $totaldocXaccionAmboCount }},
        acciones: {{ $totalaccionAmboCount }},
    },
    {
        label: 'Huánuco',
        docentesXaccion: {{ $totaldocXaccionHuanucoCount }},
        acciones: {{ $totalaccionHuanucoCount }},
    },
    {
        label: 'Dos de Mayo',
        docentesXaccion: {{ $totaldocXaccionDosdeMayoCount }},
        acciones: {{ $totalaccionDosdeMayoCount }},
    },
    {
        label: 'Huamalies',
        docentesXaccion: {{ $totaldocXaccionHuamaliesCount }},
        acciones: {{ $totalaccionHuamaliesCount }},
    },
    {
        label: 'Leoncio Prado',
        docentesXaccion: {{ $totaldocXaccionPradoCount }},
        acciones: {{ $totalaccionPradoCount }},
    },
    {
        label: 'Pachitea',
        docentesXaccion: {{ $totaldocXaccionPachiteaCount }},
        acciones: {{ $totalaccionPachiteaCount }},
    },
    {
        label: 'Puerto Inca',
        docentesXaccion: {{ $totaldocXaccionIncaCount }},
        acciones: {{ $totalaccionIncaCount }},
    },
    {
        label: 'Yarowilca',
        docentesXaccion: {{ $totaldocXaccionYarowilcaCount }},
        acciones: {{ $totalaccionYarowilcaCount }},
    },
    {
        label: 'Marañon',
        docentesXaccion: {{ $totaldocXaccionMarañonCount }},
        acciones: {{ $totalaccionMarañonCount }},
    },
    {
        label: 'Lauricocha',
        docentesXaccion: {{ $totaldocXaccionLauricochaCount }},
        acciones: {{ $totalaccionLauricochaCount }},
    },
    {
        label: 'Huacaybamba',
        docentesXaccion: {{ $totaldocXaccionHuacaybambaCount }},
        acciones: {{ $totalaccionHuacaybambaCount }},
    }
  ];

  var donutChartCanvasAcc = $('#pieChartAccion').get(0).getContext('2d');

  var labelsAcc = ubicacionesAcc.map(function (ubicacion) {
      return `${ubicacion.label} : ${ubicacion.docentesXaccion}`;
  });

  var dataAcc = ubicacionesAcc.map(function (ubicacion) {
      return ubicacion.docentesXaccion;
  });

  var backgroundColors = ['#f56954', '#f39c12', '#99F326', '#dfc731', '#00c0ef', '#3c8dbc', '#8224D5', '#F136EC', '#41C723', '#bac7c1', '#fcbd9c'];

  var donutDataAcc = {
      labels: labelsAcc,
      datasets: [
          {
              data: dataAcc,
              backgroundColor: backgroundColors,
          },
      ],
  };

  var donutOptionsI = {
      maintainAspectRatio: false,
      responsive: true,
      tooltips: {
          callbacks: {
              label: function (tooltipItem, data) {
                  var index = tooltipItem.index;
                  return `${labelsAcc[index]} (# Acciones: ${ubicacionesAcc[index].acciones})`;
              },
          },
      },
  };

  // Crea el pie chart
  new Chart(donutChartCanvasAcc, {
      type: 'doughnut',
      data: donutDataAcc,
      options: donutOptionsI,
  });


//Pie Chart Cantidad de Difuciones
  var ubicacionesDif = [
    {
        label: 'Ambo',
        docentesXdifucion: {{ $totaldocXdifucionAmboCount }},
        difuciones: {{ $totaldifucionAmboCount }},
    },
    {
        label: 'Huánuco',
        docentesXdifucion: {{ $totaldocXdifucionHuanucoCount }},
        difuciones: {{ $totaldifucionHuanucoCount }},
    },
    {
        label: 'Dos de Mayo',
        docentesXdifucion: {{ $totaldocXdifucionDosdeMayoCount }},
        difuciones: {{ $totaldifucionDosdeMayoCount }},
    },
    {
        label: 'Huamalies',
        docentesXdifucion: {{ $totaldocXdifucionHuamaliesCount }},
        difuciones: {{ $totaldifucionHuamaliesCount }},
    },
    {
        label: 'Leoncio Prado',
        docentesXdifucion: {{ $totaldocXdifucionPradoCount }},
        difuciones: {{ $totaldifucionPradoCount }},
    },
    {
        label: 'Pachitea',
        docentesXdifucion: {{ $totaldocXdifucionPachiteaCount }},
        difuciones: {{ $totaldifucionPachiteaCount }},
    },
    {
        label: 'Puerto Inca',
        docentesXdifucion: {{ $totaldocXdifucionIncaCount }},
        difuciones: {{ $totaldifucionIncaCount }},
    },
    {
        label: 'Yarowilca',
        docentesXdifucion: {{ $totaldocXdifucionYarowilcaCount }},
        difuciones: {{ $totaldifucionYarowilcaCount }},
    },
    {
        label: 'Marañon',
        docentesXdifucion: {{ $totaldocXdifucionMarañonCount }},
        difuciones: {{ $totaldifucionMarañonCount }},
    },
    {
        label: 'Lauricocha',
        docentesXdifucion: {{ $totaldocXdifucionLauricochaCount }},
        difuciones: {{ $totaldifucionLauricochaCount }},
    },
    {
        label: 'Huacaybamba',
        docentesXdifucion: {{ $totaldocXdifucionHuacaybambaCount }},
        difuciones: {{ $totaldifucionHuacaybambaCount }},
    }
  ];

  var donutChartCanvasDif = $('#pieChartDifusion').get(0).getContext('2d');

  var labelsDif = ubicacionesDif.map(function (ubicacion) {
      return `${ubicacion.label} : ${ubicacion.docentesXdifucion}`;
  });

  var dataDif = ubicacionesDif.map(function (ubicacion) {
      return ubicacion.docentesXdifucion;
  });

  var backgroundColors = ['#f56954', '#f39c12', '#99F326', '#dfc731', '#00c0ef', '#3c8dbc', '#8224D5', '#F136EC', '#41C723', '#bac7c1', '#fcbd9c'];

  var donutDataDif = {
      labels: labelsDif,
      datasets: [
          {
              data: dataDif,
              backgroundColor: backgroundColors,
          },
      ],
  };

  var donutOptionsI = {
      maintainAspectRatio: false,
      responsive: true,
      tooltips: {
          callbacks: {
              label: function (tooltipItem, data) {
                  var index = tooltipItem.index;
                  return `${labelsDif[index]} (# Difusiones: ${ubicacionesDif[index].difuciones})`;
              },
          },
      },
  };

  // Crea el pie chart
  new Chart(donutChartCanvasDif, {
      type: 'doughnut',
      data: donutDataDif,
      options: donutOptionsI,
  });
//Pie Chart Cantidad de Evidencias
  var ubicacionesEvi = [
      {
          label: 'Ambo',
          docentesXevidencia: {{ $totaldocXevidenciaAmboCount }},
          evidencias: {{ $totalevidenciaAmboCount }},
      },
      {
          label: 'Huánuco',
          docentesXevidencia: {{ $totaldocXevidenciaHuanucoCount }},
          evidencias: {{ $totalevidenciaHuanucoCount }},
      },
      {
          label: 'Dos de Mayo',
          docentesXevidencia: {{ $totaldocXevidenciaDosdeMayoCount }},
          evidencias: {{ $totalevidenciaDosdeMayoCount }},
      },
      {
          label: 'Huamalies',
          docentesXevidencia: {{ $totaldocXevidenciaHuamaliesCount }},
          evidencias: {{ $totalevidenciaHuamaliesCount }},
      },
      {
          label: 'Leoncio Prado',
          docentesXevidencia: {{ $totaldocXevidenciaPradoCount }},
          evidencias: {{ $totalevidenciaPradoCount }},
      },
      {
          label: 'Pachitea',
          docentesXevidencia: {{ $totaldocXevidenciaPachiteaCount }},
          evidencias: {{ $totalevidenciaPachiteaCount }},
      },
      {
          label: 'Puerto Inca',
          docentesXevidencia: {{ $totaldocXevidenciaIncaCount }},
          evidencias: {{ $totalevidenciaIncaCount }},
      },
      {
          label: 'Yarowilca',
          docentesXevidencia: {{ $totaldocXevidenciaYarowilcaCount }},
          evidencias: {{ $totalevidenciaYarowilcaCount }},
      },
      {
          label: 'Marañon',
          docentesXevidencia: {{ $totaldocXevidenciaMarañonCount }},
          evidencias: {{ $totalevidenciaMarañonCount }},
      },
      {
          label: 'Lauricocha',
          docentesXevidencia: {{ $totaldocXevidenciaLauricochaCount }},
          evidencias: {{ $totalevidenciaLauricochaCount }},
      },
      {
          label: 'Huacaybamba',
          docentesXevidencia: {{ $totaldocXevidenciaHuacaybambaCount }},
          evidencias: {{ $totalevidenciaHuacaybambaCount }},
      }
    ];

    var donutChartCanvasEvi = $('#pieChartEvidencia').get(0).getContext('2d');

    var labelsEvi = ubicacionesEvi.map(function (ubicacion) {
        return `${ubicacion.label} : ${ubicacion.docentesXevidencia}`;
    });

    var dataEvi = ubicacionesEvi.map(function (ubicacion) {
        return ubicacion.docentesXevidencia;
    });

    var backgroundColors = ['#f56954', '#f39c12', '#99F326', '#dfc731', '#00c0ef', '#3c8dbc', '#8224D5', '#F136EC', '#41C723', '#bac7c1', '#fcbd9c'];

    var donutDataEvi = {
        labels: labelsEvi,
        datasets: [
            {
                data: dataEvi,
                backgroundColor: backgroundColors,
            },
        ],
    };

    var donutOptionsI = {
        maintainAspectRatio: false,
        responsive: true,
        tooltips: {
            callbacks: {
                label: function (tooltipItem, data) {
                    var index = tooltipItem.index;
                    return `${labelsEvi[index]} (# Evidencias: ${ubicacionesEvi[index].evidencias})`;
                },
            },
        },
    };

    // Crea el pie chart
    new Chart(donutChartCanvasEvi, {
        type: 'doughnut',
        data: donutDataEvi,
        options: donutOptionsI,
    });
//Pie Chart Cantidad de Informes
  var ubicacionesInf = [
      {
          label: 'Ambo',
          docentesXinforme: {{ $totaldocXinformeAmboCount }},
          informes: {{ $totalinformeAmboCount }},
      },
      {
          label: 'Huánuco',
          docentesXinforme: {{ $totaldocXinformeHuanucoCount }},
          informes: {{ $totalinformeHuanucoCount }},
      },
      {
          label: 'Dos de Mayo',
          docentesXinforme: {{ $totaldocXinformeDosdeMayoCount }},
          informes: {{ $totalinformeDosdeMayoCount }},
      },
      {
          label: 'Huamalies',
          docentesXinforme: {{ $totaldocXinformeHuamaliesCount }},
          informes: {{ $totalinformeHuamaliesCount }},
      },
      {
          label: 'Leoncio Prado',
          docentesXinforme: {{ $totaldocXinformePradoCount }},
          informes: {{ $totalinformePradoCount }},
      },
      {
          label: 'Pachitea',
          docentesXinforme: {{ $totaldocXinformePachiteaCount }},
          informes: {{ $totalinformePachiteaCount }},
      },
      {
          label: 'Puerto Inca',
          docentesXinforme: {{ $totaldocXinformeIncaCount }},
          informes: {{ $totalinformeIncaCount }},
      },
      {
          label: 'Yarowilca',
          docentesXinforme: {{ $totaldocXinformeYarowilcaCount }},
          informes: {{ $totalinformeYarowilcaCount }},
      },
      {
          label: 'Marañon',
          docentesXinforme: {{ $totaldocXinformeMarañonCount }},
          informes: {{ $totalinformeMarañonCount }},
      },
      {
          label: 'Lauricocha',
          docentesXinforme: {{ $totaldocXinformeLauricochaCount }},
          informes: {{ $totalinformeLauricochaCount }},
      },
      {
          label: 'Huacaybamba',
          docentesXinforme: {{ $totaldocXinformeHuacaybambaCount }},
          informes: {{ $totalinformeHuacaybambaCount }},
      }
    ];

    var donutChartCanvasInf = $('#pieChartInforme').get(0).getContext('2d');

    var labelsInf = ubicacionesInf.map(function (ubicacion) {
        return `${ubicacion.label} : ${ubicacion.docentesXinforme}`;
    });

    var dataInf = ubicacionesInf.map(function (ubicacion) {
        return ubicacion.docentesXinforme;
    });

    var backgroundColors = ['#f56954', '#f39c12', '#99F326', '#dfc731', '#00c0ef', '#3c8dbc', '#8224D5', '#F136EC', '#41C723', '#bac7c1', '#fcbd9c'];

    var donutDataInf = {
        labels: labelsInf,
        datasets: [
            {
                data: dataInf,
                backgroundColor: backgroundColors,
            },
        ],
    };

    var donutOptionsI = {
        maintainAspectRatio: false,
        responsive: true,
        tooltips: {
            callbacks: {
                label: function (tooltipItem, data) {
                    var index = tooltipItem.index;
                    return `${labelsInf[index]} (# Informes: ${ubicacionesInf[index].informes})`;
                },
            },
        },
    };

    // Crea el pie chart
    new Chart(donutChartCanvasInf, {
        type: 'doughnut',
        data: donutDataInf,
        options: donutOptionsI,
    });
//Pie Chart Cantidad de Plan
  var ubicacionesPla = [
      {
          label: 'Ambo',
          docentesXplan: {{ $totaldocXplanAmboCount }},
          planes: {{ $totalplanAmboCount }},
      },
      {
          label: 'Huánuco',
          docentesXplan: {{ $totaldocXplanHuanucoCount }},
          planes: {{ $totalplanHuanucoCount }},
      },
      {
          label: 'Dos de Mayo',
          docentesXplan: {{ $totaldocXplanDosdeMayoCount }},
          planes: {{ $totalplanDosdeMayoCount }},
      },
      {
          label: 'Huamalies',
          docentesXplan: {{ $totaldocXplanHuamaliesCount }},
          planes: {{ $totalplanHuamaliesCount }},
      },
      {
          label: 'Leoncio Prado',
          docentesXplan: {{ $totaldocXplanPradoCount }},
          planes: {{ $totalplanPradoCount }},
      },
      {
          label: 'Pachitea',
          docentesXplan: {{ $totaldocXplanPachiteaCount }},
          planes: {{ $totalplanPachiteaCount }},
      },
      {
          label: 'Puerto Inca',
          docentesXplan: {{ $totaldocXplanIncaCount }},
          planes: {{ $totalplanIncaCount }},
      },
      {
          label: 'Yarowilca',
          docentesXplan: {{ $totaldocXplanYarowilcaCount }},
          planes: {{ $totalplanYarowilcaCount }},
      },
      {
          label: 'Marañon',
          docentesXplan: {{ $totaldocXplanMarañonCount }},
          planes: {{ $totalplanMarañonCount }},
      },
      {
          label: 'Lauricocha',
          docentesXplan: {{ $totaldocXplanLauricochaCount }},
          planes: {{ $totalplanLauricochaCount }},
      },
      {
          label: 'Huacaybamba',
          docentesXplan: {{ $totaldocXplanHuacaybambaCount }},
          planes: {{ $totalplanHuacaybambaCount }},
      }
    ];

    var donutChartCanvasPla = $('#pieChartPlan').get(0).getContext('2d');

    var labelsPla = ubicacionesPla.map(function (ubicacion) {
        return `${ubicacion.label} : ${ubicacion.docentesXplan}`;
    });

    var dataPla = ubicacionesPla.map(function (ubicacion) {
        return ubicacion.docentesXplan;
    });

    var backgroundColors = ['#f56954', '#f39c12', '#99F326', '#dfc731', '#00c0ef', '#3c8dbc', '#8224D5', '#F136EC', '#41C723', '#bac7c1', '#fcbd9c'];

    var donutDataPla = {
        labels: labelsPla,
        datasets: [
            {
                data: dataPla,
                backgroundColor: backgroundColors,
            },
        ],
    };

    var donutOptionsI = {
        maintainAspectRatio: false,
        responsive: true,
        tooltips: {
            callbacks: {
                label: function (tooltipItem, data) {
                    var index = tooltipItem.index;
                    return `${labelsPla[index]} (# Planes: ${ubicacionesPla[index].planes})`;
                },
            },
        },
    };

    // Crea el pie chart
    new Chart(donutChartCanvasPla, {
        type: 'doughnut',
        data: donutDataPla,
        options: donutOptionsI,
    });

//Pie Chart Cantidad de Produccion
  var ubicacionesPro = [
      {
          label: 'Ambo',
          docentesXpro: {{ $totaldocXproduccionAmboCount }},
          producciones: {{ $totalproduccionAmboCount }},
      },
      {
          label: 'Huánuco',
          docentesXpro: {{ $totaldocXproduccionHuanucoCount }},
          producciones: {{ $totalproduccionHuanucoCount }},
      },
      {
          label: 'Dos de Mayo',
          docentesXpro: {{ $totaldocXproduccionDosdeMayoCount }},
          producciones: {{ $totalproduccionDosdeMayoCount }},
      },
      {
          label: 'Huamalies',
          docentesXpro: {{ $totaldocXproduccionHuamaliesCount }},
          producciones: {{ $totalproduccionHuamaliesCount }},
      },
      {
          label: 'Leoncio Prado',
          docentesXpro: {{ $totaldocXproduccionPradoCount }},
          producciones: {{ $totalproduccionPradoCount }},
      },
      {
          label: 'Pachitea',
          docentesXpro: {{ $totaldocXproduccionPachiteaCount }},
          producciones: {{ $totalproduccionPachiteaCount }},
      },
      {
          label: 'Puerto Inca',
          docentesXpro: {{ $totaldocXproduccionIncaCount }},
          producciones: {{ $totalproduccionIncaCount }},
      },
      {
          label: 'Yarowilca',
          docentesXpro: {{ $totaldocXproduccionYarowilcaCount }},
          producciones: {{ $totalproduccionYarowilcaCount }},
      },
      {
          label: 'Marañon',
          docentesXpro: {{ $totaldocXproduccionMarañonCount }},
          producciones: {{ $totalproduccionMarañonCount }},
      },
      {
          label: 'Lauricocha',
          docentesXpro: {{ $totaldocXproduccionLauricochaCount }},
          producciones: {{ $totalproduccionLauricochaCount }},
      },
      {
          label: 'Huacaybamba',
          docentesXpro: {{ $totaldocXproduccionHuacaybambaCount }},
          producciones: {{ $totalproduccionHuacaybambaCount }},
      }
    ];

    var donutChartCanvasPro = $('#pieChartPro').get(0).getContext('2d');

    var labelsPro = ubicacionesPro.map(function (ubicacion) {
        return `${ubicacion.label} : ${ubicacion.docentesXpro}`;
    });

    var dataPro = ubicacionesPro.map(function (ubicacion) {
        return ubicacion.docentesXpro;
    });

    var backgroundColors = ['#f56954', '#f39c12', '#99F326', '#dfc731', '#00c0ef', '#3c8dbc', '#8224D5', '#F136EC', '#41C723', '#bac7c1', '#fcbd9c'];

    var donutDataPro = {
        labels: labelsPro,
        datasets: [
            {
                data: dataPro,
                backgroundColor: backgroundColors,
            },
        ],
    };

    var donutOptionsI = {
        maintainAspectRatio: false,
        responsive: true,
        tooltips: {
            callbacks: {
                label: function (tooltipItem, data) {
                    var index = tooltipItem.index;
                    return `${labelsPro[index]} (# Producciones: ${ubicacionesPro[index].producciones})`;
                },
            },
        },
    };

    // Crea el pie chart
    new Chart(donutChartCanvasPro, {
        type: 'doughnut',
        data: donutDataPro,
        options: donutOptionsI,
    });

//Pie Chart Cantidad de Agendas
  var ubicacionesAge = [
      {
          label: 'Ambo',
          docentesXagenda: {{ $totaldocXagendaAmboCount }},
          agendas: {{ $totalagendaAmboCount }},
      },
      {
          label: 'Huánuco',
          docentesXagenda: {{ $totaldocXagendaHuanucoCount }},
          agendas: {{ $totalagendaHuanucoCount }},
      },
      {
          label: 'Dos de Mayo',
          docentesXagenda: {{ $totaldocXagendaDosdeMayoCount }},
          agendas: {{ $totalagendaDosdeMayoCount }},
      },
      {
          label: 'Huamalies',
          docentesXagenda: {{ $totaldocXagendaHuamaliesCount }},
          agendas: {{ $totalagendaHuamaliesCount }},
      },
      {
          label: 'Leoncio Prado',
          docentesXagenda: {{ $totaldocXagendaPradoCount }},
          agendas: {{ $totalagendaPradoCount }},
      },
      {
          label: 'Pachitea',
          docentesXagenda: {{ $totaldocXagendaPachiteaCount }},
          agendas: {{ $totalagendaPachiteaCount }},
      },
      {
          label: 'Puerto Inca',
          docentesXagenda: {{ $totaldocXagendaIncaCount }},
          agendas: {{ $totalagendaIncaCount }},
      },
      {
          label: 'Yarowilca',
          docentesXagenda: {{ $totaldocXagendaYarowilcaCount }},
          agendas: {{ $totalagendaYarowilcaCount }},
      },
      {
          label: 'Marañon',
          docentesXagenda: {{ $totaldocXagendaMarañonCount }},
          agendas: {{ $totalagendaMarañonCount }},
      },
      {
          label: 'Lauricocha',
          docentesXagenda: {{ $totaldocXagendaLauricochaCount }},
          agendas: {{ $totalagendaLauricochaCount }},
      },
      {
          label: 'Huacaybamba',
          docentesXagenda: {{ $totaldocXagendaHuacaybambaCount }},
          agendas: {{ $totalagendaHuacaybambaCount }},
      }
    ];

    var donutChartCanvasAge = $('#pieChartAge').get(0).getContext('2d');

    var labelsAge = ubicacionesAge.map(function (ubicacion) {
        return `${ubicacion.label} : ${ubicacion.docentesXagenda}`;
    });

    var dataAge = ubicacionesAge.map(function (ubicacion) {
        return ubicacion.docentesXagenda;
    });

    var backgroundColors = ['#f56954', '#f39c12', '#99F326', '#dfc731', '#00c0ef', '#3c8dbc', '#8224D5', '#F136EC', '#41C723', '#bac7c1', '#fcbd9c'];

    var donutDataAge = {
        labels: labelsAge,
        datasets: [
            {
                data: dataAge,
                backgroundColor: backgroundColors,
            },
        ],
    };

    var donutOptionsI = {
        maintainAspectRatio: false,
        responsive: true,
        tooltips: {
            callbacks: {
                label: function (tooltipItem, data) {
                    var index = tooltipItem.index;
                    return `${labelsAge[index]} (# Agendas: ${ubicacionesAge[index].agendas})`;
                },
            },
        },
    };

    // Crea el pie chart
    new Chart(donutChartCanvasAge, {
        type: 'doughnut',
        data: donutDataAge,
        options: donutOptionsI,
    });



});
</script>
@stop