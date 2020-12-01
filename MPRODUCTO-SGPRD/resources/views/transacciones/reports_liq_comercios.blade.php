@extends('layouts.app')
@section('titulo', 'Liquidación de Comercios')


@section('contenido')

<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-8">
		<h2><i class="fa fa-credit-card"></i> Exportar Liquidación de Comercios </h2>
		<ol class="breadcrumb">
			<li>
				<a href="{{ url('home') }}">Panel</a>
			</li>
			<li>Reportes de Liquidación de Comercios y Domiciliación de Clientes
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
<div id="FlashMessage">
@include('flash::message')
</div>

@if($comercio == null && $domiciliacion == null && $nopermission == '')
	 <div class="alert alert-danger" role="alert" style="width: 100%;">
  		¡No hubo resultados para la exportación, por favor intente más tarde!
	</div>
@endif
<div class="alert alert-danger" id="NoResult" style="display: none;" role="alert" style="width: 100%;">
  		¡No hubo resultados para la exportación, por favor intente más tarde!
	</div>
@if($nopermission == 1 && $comercio == '' && $domiciliacion == '')
	<div class="alert alert-danger" role="alert" style="width: 100%;">
  		¡Disculpe este perfil de usuario no tiene permiso para generar dicho reporte!
	</div> 
@endif
<div class="wrapper wrapper-content ecommerce">


	<div class="wrapper wrapper-content ecommerce">
	
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Reporte  de Liquidación de Comercios y Domiciliación de Clientes</h5>

			</div>
			<div class="ibox-content">
				<div class="row">
					<div class="col-sm-6 b-r"><!--<h3 class="m-t-none m-b">Reporte de Liquidación de Comercios y Domiciliación de Clientes</h3>-->
						<p>Ingrese los criterios para la descarga del reporte.</p>
						<form role="form" id="frmGenerate" method="POST" action=" {{ url('transacciones/export_liq_comercios') }}"  class="form-horizontal">
							{{ csrf_field() }}
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label" for="fecha_desde">Fecha desde</label>
										<div class="input-group date">
											<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
											{!! Form::text('fecha_desde', date('d/m/Y'), ['class'=>'input-sm form-control', '','id'=>'fecha_desde']) !!}
										</div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label" for="fecha_hasta">Fecha hasta</label>
										<div class="input-group date">
											<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
											{!! Form::text('fecha_hasta', date('d/m/Y'), ['class'=>'input-sm form-control', '','id'=>'fecha_hasta']) !!}
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label" for="dateranges">Monedas: </label><br>
										<div class="input-group date" >
											<select class="form-control m-b" name="moneda" id="monedas">
												<!--<option value="" disabled selected>Moneda</option>-->
											</select>
										</div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<!--<label class="control-label" >Cédula</label>-->
										<!--<div class="input-group date">
											{!! Form::text('dni','',['class'=>'form-control m-b','placeholder'=>'Cédula','id'=>'dni']) !!}
										</div>-->
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<!--<label class="control-label" >Carnet</label>-->
										<!--<div class="input-group date">
											{!! Form::text('carnet','',['class'=>'form-control m-b','placeholder'=>'Carnet','id'=>'carnet']) !!}
										</div>-->
									</div>
								</div>
							</div>
							<div>
								<button type="submit" class="btn btn-block btn-primary" id="export" name="export" data-dismiss="modal">Descargar</button>
								<div id="idLoad" style="text-align:center;display:none"><img src="{{ url('/') }}/img/loader.gif" alt="loading" /><br/>Un momento, por favor...</div>
							</div>
						</form>
					</div>
					<div class="col-sm-6"><h5>Descarga de archivo de Excel</h5>
						<p class="text-center">
							<a href=""><i class="fa fa-file-excel-o big-icon"></i></a>
						</p>

					</div>
				</div>
			</div>
		</div>

		@if(isset($ProcessedFiles))
			@if(count($ProcessedFiles) != 0)
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox">
							<div class="ibox-content">
								<h2>
									<span class="text-navy">
				 						Archivos Generados
				 					</span>
								</h2>
								<div class="hr-line-dashed"></div>

								<table  id="customers" class="footable table table-stripped toggle-arrow-tiny" data-page-size="10">
									<thead>
										<th>ARCHIVO</th>
										<th>FECHA/HORA</th>
										<th>RESPONSABLE</th>
									</thead>
									<tbody>
										
										@foreach($ProcessedFiles as $element)
											<tr>
												<td>
													<a href="{{ url('/') }}/liquidacion-domiciliacion/{{ $element['Filename']}}"><i class="fa fa-check" aria-hidden="true"> {{ $element['Filename']}}</i></a>												
												</td>
												<td>{{ $element['created_at']}}</td>
												<td>{{ $element['email']}}</td>
												
											</tr>
										@endforeach
									</tbody>
									<tfoot>
                                		<tr>
                                    		<td colspan="9">
                                        		<ul class="pagination pull-right"></ul>
                                    			</td>
                                		</tr>
                            		</tfoot>
								</table>
							</div>
						</div>
					</div>
				</div>
			@endif
		@endif		


	</div>

	@stop

	@section('scripts')
   

    <!-- Page-Level Scripts -->
        <script>
            $(document).ready(function() {

                $('#fecha_desde').datepicker({
                    todayBtn: "linked",
                    keyboardNavigation: false,
                    forceParse: false,
                    calendarWeeks: true,
                    format: 'dd/mm/yyyy',
                    autoclose: true
                   
                });

                $('#fecha_hasta').datepicker({
                    todayBtn: "linked",
                    keyboardNavigation: false,
                    forceParse: false,
                    calendarWeeks: true,
                    format: 'dd/mm/yyyy',
                    autoclose: true
                });

                $.get( "{{URL('/divisas')}}",function(data){
  				for(var i=0; i < data.length; i++){
  				$("#monedas").append('<option value="'+data[i].mon_id+'">'+data[i].mon_nombre+'</option>');
  				}
			});//Fin del desplegable divisa

            });

		</script>
		
		<script>
$("#frmGenerate").submit(function(event){	
	$('#NoResult').css('display', 'none');
	$('#FlashMessage').css('display', 'none');

	$(':input[type="submit"]').prop('disabled', true);

	$('#export').css('display', 'none');

	$("#idLoad").css("display", "block");

	event.preventDefault(); 

	var post_url = $(this).attr("action");

	var form_data = $(this).serialize();
	
	$.post( post_url, form_data, function( response ) {				

		if(response["Massage"] == "NoResult")
		{
			$("#NoResult").css("display", "block");

			$(':input[type="submit"]').prop('disabled', false);

			$("#idLoad").css("display", "none");

			$("#export").css("display", "block");
		}

		if(response["Massage"] == "Generated")
		{
			location.reload(true);
		}
	});
});				
		
		</script>
    <!-- end page js -->
    @endsection