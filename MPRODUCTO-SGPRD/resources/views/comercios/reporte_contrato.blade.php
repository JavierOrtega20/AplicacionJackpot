
@extends('layouts.app')
@section('titulo', 'Comercios')

@section('contenido')

<div class="row wrapper border-bottom white-bg page-heading">
  <div class="col-lg-8">
    <h2><i class="fa fa-credit-card"></i>   Exportar contratos comercios</h2>
    <ol class="breadcrumb">
      <li>
        <a href="{{ url('home') }}">Panel</a>
      </li>
      <li>Reportes de Comercios
      </li>
      <li class="active">
        <strong>Exportar</strong>
      </li>
    </ol>
  </div>
  <div class="col-lg-4">
    <div class="title-action">

    </div>
  </div>

</div>
@include('flash::message')
<div class="wrapper wrapper-content ecommerce">


  <div class="wrapper wrapper-content ecommerce">
  
    <div class="ibox float-e-margins">
      <div class="ibox-title">
        <h5>Reporte de comercio</h5>

      </div>
      <div class="ibox-content">
        <div class="row">
          <h2>
           {{ $cantidad}} <span class="text-navy"> Comercios</span>
          </h2>
          <table id="contratos" class="table">
            <thead>
              <tr class="text-center-row">
                <th>Nombre del Comercio </th>
                <th>Rif</th>
				<th>Estado</th>
                <th>Contrato firmado</th>
                <th>Fecha y Hora de Aceptación</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($contratos as $contrato)
              <tr class="text-center-row">
                <td>{{ $contrato->fulldescripcion }}</td>
                <td>{{ $contrato->rif }}</td>
				<td> 
					@if($contrato->estado_afiliacion_comercio == 1)
					<p>Afiliado</p>
					@endif
					
					@if($contrato->estado_afiliacion_comercio == 2)
					<p>Pendiente por aceptación de contrato</p>
					@endif

					@if($contrato->estado_afiliacion_comercio == 3)
					<p>Pendiente por activación</p>
					@endif					
				</td>
                <td>@if($contrato->aceptacion_contrato == null)
					No
					@else
					Si
					@endif
					</td>
                <td>{{ $contrato->aceptacion_contrato }}</td> 
              </tr>
              @endforeach
            </tbody>
          </table><!--fin de la tabla--> 
        </div>
      </div>
    </div>
  </div>

  @stop

@section('scripts')

    <script type="text/javascript">
     $(document).ready(function(){

      
            $('#contratos').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                "order": [[0, "desc"]],
                buttons: [{
                    extend: 'pdf',
                    pageOrientation: 'landscape',
                    pageSize: {
                        width: 580,
                        height: 'auto'
                    },
                },
                {
                    extend: 'excel',
                    exportOptions: {
                       columns: ':visible',
                       format: {
                           body: function (data, row, column, node) {
                                data = $('<p>' + data + '</p>').text();
                                return $.isNumeric(data.replace(',', '.')) ? data.replace(',', '.') : data;
                           }
                       }
                    }
                },
                {     
                    extend: 'copy',   
                },
                ],
                language: {

                    buttons: {
                        copyTitle: 'Información Copiada',
                        copySuccess: {
                            _: '%d lineas copiadas',
                            1: '1 lineas copiadas'
                        },
                    },
                    "decimal": "",
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
                    "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
                    "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Entradas",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Ultimo",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                
            });//data table

    });
          </script>
@endsection









