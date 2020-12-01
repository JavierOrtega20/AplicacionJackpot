@extends('layouts.app')
@section('titulo', 'Clientes')

<?php
	if(isset($_POST['fecha_desde'])){
		$fecha_desde = explode("/", $_POST['fecha_desde']);
		$fecha_desde =  $fecha_desde[0]."-".$fecha_desde[1]."-".$fecha_desde[2];
	}else{
		$fecha_desde = date('d-m-Y',strtotime(date('d-m-Y')));
	}

	if(isset($_POST['fecha_hasta'])){
		$fecha_hasta = explode("/",$_POST['fecha_hasta']);
		$fecha_hasta =  $fecha_hasta[0]."-".$fecha_hasta[1]."-".$fecha_hasta[2];
	}else{
		$fecha_hasta = date('d-m-Y',strtotime(date('d-m-Y')."+1 days"));
	}

	if(isset($_POST['estado'])){
		$estado = $_POST['estado'];
	}else{
		$estado = '1000';
	}


	if(isset($_POST['cliente'])){
		$cliente = $_POST['cliente'];
	}else{
		$cliente = 0;
	}

	if(isset($_POST['mon_nombre'])){
		$moneda = $_POST['mon_nombre'];
	}else{
		$moneda = 2;
	}

?>

@section('contenido')

<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-8">
		<h2><i class="fa fa-credit-card"></i>   Exportar Totalizado de Clientes</h2>
		<ol class="breadcrumb">
			<li>
				<a href="{{ url('home') }}">Panel</a>
			</li>
			<li>Reportes Totalizado de Clientes
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
				<h5>Reporte Totalizado de Clientes</h5>

			</div>
			<div class="ibox-content">
				<div class="row">
					<div class="col-sm-6 b-r"><!--<h3 class="m-t-none m-b">Reporte Totalizado de clientes</h3>-->
						<p>Ingrese los criterios para la descarga del reporte.</p>
						<form role="form" method="POST" action=" {{ url('users/search') }}"  class="form-horizontal">
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
								<!--<div class="col-sm-3">
									<div class="form-group">
										<label class="control-label" for="fecha_hasta">Estado</label><br>
										<div class="input-group date">
											<select class="input-sm form-control" name="estado" id="estado">
												@if($estado == "1000")
													<option value="1000" selected="true">Seleccione</option>
												@endif

												@if($estado == 0)
													<option value="0" selected="true">Aprobadas</option>
												@else
													<option value="0">Aprobadas</option>
												@endif

												@if($estado == 1)
													<option value="1" selected="true">Por Autorizar</option>
												@else
													<option value="1">Por Autorizar</option>
												@endif

												@if($estado == 2)
													<option value="2" selected="true">Canceladas</option>
												@else
													<option value="2">Canceladas</option>
												@endif

												@if($estado == 3)
													<option value="3" selected="true">Rechazadas</option>
												@else
													<option value="3">Rechazadas</option>
												@endif

												@if($estado == 4)
													<option value="4" selected="true">Reversadas</option>
												@else
													<option value="4">Reversadas</option>
												@endif

											</select>
										</div>
									</div>
								</div>-->

								<div class="col-sm-3">
									<div class="form-group">
										<label class="control-label" for="fecha_hasta">Cliente</label><br>
										<div class="input-group date">
											@if($cliente == 0)
												<input type="text" placeholder="Cedula" name="cliente" id="cliente" class="input-sm form-control" maxlength ="50" value="0" onkeypress="return justNumbers(event);" onclick="deleteValCliente(this)" onblur="desenfCliente(this)">
											@else
												<input type="text" placeholder="Cedula" name="cliente" id="cliente" class="input-sm form-control" maxlength ="50" value="{{$cliente}}" onkeypress="return justNumbers(event);" onclick="deleteValCliente(this)" onblur="desenfCliente(this)">
											@endif
										</div>
									</div>
								</div>
								

								<div class="col-sm-3">
									<div class="form-group">
										<label class="control-label" for="fecha_hasta">Monedas</label><br>
										<div class="input-group date">
											<select class="form-control m-b" name="mon_nombre" id="monedas">
														<!--<option value="" disabled selected>Moneda</option>-->
											</select>
										</div>
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
								<button type="submit" class="btn btn-block btn-primary" id="search" name="search" data-dismiss="modal">Buscar</button>

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

		@if(isset($userCount))
			@if($userCount != 0)
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox">
							<div class="ibox-content">
								<h2>
				 					{{count($clientes)}}
				 					<span class="text-navy">
				 						Clientes
				 					</span>
				 					<span class="text-navy" style="margin-left: 70%;">
				 						<a href="{{ url('users/export_clients',['fecha_desde'=>$fecha_desde,'fecha_hasta'=>$fecha_hasta,'estado'=>$estado,'cliente'=>$cliente, 'moneda'=> $moneda]) }}" class="btn btn-primary" id="descargar">
                							<i class="fa fa-book"></i>
                							Descargar
                						</a>
				 					</span>

				 				</h2>

				 				<div class="hr-line-dashed"></div>
				 				<table  id="customers" class="footable table table-stripped toggle-arrow-tiny" data-page-size="10">
				 					@if($rol == 3)
					 					<thead>
											<tr>
												<th style="font-size: 10px;text-align: right;">CÉDULA</th>
												<th style="font-size: 10px;text-align: right;">NOMBRE</th>
												<th style="font-size: 10px;text-align: right;">APELLIDO</th>
												<th style="font-size: 10px;text-align: right;">TELÉFONO</th>
												<th style="font-size: 10px;text-align: right;">CORREO ELECTRONICO</th>
												<th style="font-size: 10px;text-align: right;">CONSUMOS</th>
												<th style="font-size: 10px;text-align: right;">PROPINAS</th>
												<!--<th style="font-size: 10px;text-align: right;">DISPONIBLE</th>-->
												<th style="font-size: 10px;text-align: right;">LIMITE</th>
												<th style="font-size: 10px;text-align: right;">MONEDA</th>
												<th style="font-size: 10px;text-align: right;">ESTATUS</th>
											</tr>
										</thead>
                            			<tbody>

                            				@foreach($clientes as $i => $element)
												<tr>
													<td style="text-align: right;">{{$element->cedula}}</td>
													<td style="text-align: right;">{{$element->nombre}}</td>
													<td style="text-align: right;">{{$element->apellido}}</td>
													<td style="text-align: right;">{{$element->telefono}}</td>
													<td style="text-align: right;">{{$element->correo}}</td>
													<td style="text-align: right;">{{number_format($element->consumos,2,',','.')}}</td>
													<td style="text-align: right;">{{number_format($element->propinas,2,',','.')}}</td>
													<!--<td style="text-align: right;">{{number_format($element->disponible,2,',','.')}}</td>-->
													<td style="text-align: right;">{{number_format($element->limite,2,',','.')}}</td>
													<td style="text-align: right;">{{$element->mon_nombre}}</td>
													<td style="text-align: right;">
														@if($element->status == 0)
															Aprobada
														@elseif($element->status == 1)
															Por Aprobar
														@elseif($element->status == 2)
															Cancelada
														@elseif($element->status == 3)
															Rechazada
														@elseif($element->status == 4)
															Reversada
														@endif
													</td>
												</tr>
                            				@endforeach
                            			</tbody>
                            		@elseif($rol == 2 || $rol == 4 || $rol == 6 || $rol == 1)
                            			<thead>
											<tr>
												<th style="font-size: 10px;text-align: right;">CÉDULA</th>
												<th style="font-size: 10px;text-align: right;">TARJETA DE MEMBRESÍA</th>
												<th style="font-size: 10px;text-align: right;">NOMBRE</th>
												<th style="font-size: 10px;text-align: right;">APELLIDO</th>
												<th style="font-size: 10px;text-align: right;">TELÉFONO</th>
												<th style="font-size: 10px;text-align: right;">CORREO ELECTRONICO</th>
												<th style="font-size: 10px;text-align: right;">CONSUMOS</th>
												<th style="font-size: 10px;text-align: right;">PROPINAS</th>
												<!--<th style="font-size: 10px;text-align: right;">DISPONIBLE</th>-->
												<th style="font-size: 10px;text-align: right;">LIMITE</th>
												<th style="font-size: 10px;text-align: right;">MONEDA</th>

												<th style="font-size: 10px;text-align: right;">ESTATUS</th>
											</tr>
										</thead>

                            			<tbody>
                            				@foreach($clientes as $i => $element)
												<tr>
													<td style="text-align: right;">{{$element->cedula}}</td>
													@if ($rol == 2)
														<td style="text-align: right;">{{ $element->carnet }}</td>
													@else
														<td style="text-align: right;">{{ substr($element->carnet,-20,4) .' XXXX XXXX '. substr($element->carnet,-4) }}</td>
													@endif
													<td style="text-align: right;">{{$element->nombre}}</td>
													<td style="text-align: right;">{{$element->apellido}}</td>
													<td style="text-align: right;">{{$element->telefono}}</td>
													<td style="text-align: right;">{{$element->correo}}</td>
													<td style="text-align: right;">{{number_format($element->consumos,2,',','.')}}</td>
													<td style="text-align: right;">{{number_format($element->propinas,2,',','.')}}</td>
													<!--<td style="text-align: right;">{{number_format($element->disponible,2,',','.')}}</td>-->
													<td style="text-align: right;">{{number_format($element->limite,2,',','.')}}</td>
													<td style="text-align: right;">{{$element->mon_nombre}}</td>
													<td style="text-align: right;">
														@if($element->status == 0)
															Aprobada
														@elseif($element->status == 1)
															Por Aprobar
														@elseif($element->status == 2)
															Cancelada
														@elseif($element->status == 3)
															Rechazada
														@elseif($element->status == 4)
															Reversada
														@endif
													</td>
												</tr>
                            				@endforeach
                            			</tbody>
                            		@endif
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
			@else
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox">
							 <div class="ibox-content">
							 	<h2>
							 		{{$userCount}}<span class="text-navy"> Clientes encontrados</span>
							 	</h2>
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

                $('#fecha_desdeb').datepicker({
                    todayBtn: "linked",
                    keyboardNavigation: false,
                    forceParse: false,
                    calendarWeeks: true,
                    format: 'dd/mm/yyyy',
                    autoclose: true

                });

                $('#fecha_hastab').datepicker({
                    todayBtn: "linked",
                    keyboardNavigation: false,
                    forceParse: false,
                    calendarWeeks: true,
                    format: 'dd/mm/yyyy',
                    autoclose: true
                });
                $('.footable').footable();
            });

        function justNumbers(e){

        		var keynum = window.event ? window.event.keyCode : e.which;
        		if ((keynum == 8) || (keynum == 46) || (keynum == 44))
        			return true;

        		return /\d/.test(String.fromCharCode(keynum));
  		}


  			function deleteValCliente(val){

            	if( val.value == 0 ){
            		$('#cliente').val("");
            	}
            }

            function desenfCliente(val){
            	if(val.value == ''){
            		$('#cliente').val("0");
            	}
            }


            $(document).ready(function() {
			    $('#customers').DataTable( {
			        "scrollX": false,
			        "paginate":false,
			        "searching":false,
			        "info":     false
			    } );
			} );
			$.get( "{{URL('/divisas')}}",function(data){
		    for(var i=0; data.length; i++){
		    $("#monedas").append('<option value="'+data[i].mon_id+'">'+data[i].mon_nombre+'</option>');
		    }
		  });//Fin del desplegable divisa
        </script>
    <!-- end page js -->
    @endsection
