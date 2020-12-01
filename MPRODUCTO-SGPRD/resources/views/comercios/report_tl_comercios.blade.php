@extends('layouts.app')
@section('titulo', 'Totalizado por Comercios')


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

	if(isset($_POST['rif'])){
		$rif = $_POST['rif'];
	}else{
		$rif = 0;
	}

	if(isset($_POST['nombreComercio'])){
		$nombreComercio = $_POST['nombreComercio'];
	}else{
		$nombreComercio = "-";
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
		<h2><i class="fa fa-credit-card"></i>Exportar Totalizado por Comercios</h2>
		<ol class="breadcrumb">
			<li>
				<a href="{{ url('home') }}">Panel</a>
			</li>
			<li>Reportes Totalizado por Comercios
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
				<h5>Reporte Totalizado de Comercios</h5>

			</div>
			<div class="ibox-content">
				<div class="row">
					<div class="col-sm-6 b-r"><!--<h3 class="m-t-none m-b">Reporte de Totalizado por Comercios</h3>-->
						<p>Ingrese los criterios para la descarga del reporte.</p>
						<form role="form" method="POST" action=" {{ url('comercios/report_tl_comercios2') }}"  class="form-horizontal">
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
										<label class="control-label" for="fecha_hasta">Rif</label><br>
										<div class="input-group date">
											@if($rif == '0')
												<input type="text" placeholder="rif" name="rif" id="rif" class="input-sm form-control" maxlength ="50" value="0" onclick="deleteValRif(this)" onblur="desenfRif(this)">
											@else
												<input type="text" placeholder="rif" name="rif" id="rif" class="input-sm form-control" maxlength ="50" value="{{ $rif }}"  onclick="deleteValRif(this)" onblur="desenfRif(this)">
											@endif
										</div>
									</div>
								</div>

								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label" for="fecha_hasta">Nombre de Comercio</label>
										<div class="input-group date">
											@if($nombreComercio == '-')
												<input type="text" placeholder="Nombre Comercio" name="nombreComercio" id="nombreComercio" class="input-sm form-control" maxlength ="50" value="-" onclick="deleteValComer(this)" onblur="desenfComer(this)">
											@else
												<input type="text" placeholder="Nombre Comercio" name="nombreComercio" id="nombreComercio" class="input-sm form-control" maxlength ="50" value="{{$nombreComercio}}" onclick="deleteValComer(this)" onblur="desenfComer(this)">
											@endif
										</div>
									</div>
								</div>

								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">Monedas:</label>
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

		@if(isset($transComerCount))
			@if($transComerCount != 0)
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox">
							<div class="ibox-content">
								<h2>
				 					{{count($transComer)}}
				 					<span class="text-navy">
				 						Totalizados de comercios
				 					</span>
				 					<span class="text-navy" style="margin-left: 70%;">
				 						<a href="{{ url('comercios/export_report_tl_comercios',['fecha_desde'=>$fecha_desde,'fecha_hasta'=>$fecha_hasta,'estado'=>$estado,'rif'=>$rif,'nombreComercio'=>$nombreComercio, 'moneda'=> $moneda]) }}" class="btn btn-primary" id="reportButton">
                							<i class="fa fa-book"></i>
                							Descargar
                						</a>
				 					</span>

				 				</h2>

				 				<div class="hr-line-dashed" ></div>
				 				<table  id="customers" class="footable table table-stripped toggle-arrow-tiny" data-page-size="9">
					 					<thead>
											<tr>
												<th NOWRAP>RIF</th>
			                                    <th NOWRAP>N° CTA</th>
			                                    <th NOWRAP>NOMBRE COMERCIO</th>
			                                    <th NOWRAP>VENTA BRUTA</th>
			                                    <th NOWRAP>PROPINA</th>
			                                    <!--<th NOWRAP>N° CTA PROPINA</th>-->
			                                    <th NOWRAP>TASA AFILIACIÓN</th>
			                                    <th NOWRAP>COMISIÓN AFILIADO</th>
			                                    <th NOWRAP>ABONO AL COMERCIO</th>
			                                    <th NOWRAP>MONEDA</th>
			                                    <!--<th NOWRAP>ESTADO</th>-->
			                                    <th NOWRAP>PROCESADO</th>
			                                    <!--<th NOWRAP>CONSUMO CLIENTE</th>
			                                    <th NOWRAP>TOTAL CONSUMOS</th>-->
											</tr>
										</thead>
                            			<tbody>
                            				@foreach($transComer as $element)
			                                  <tr>
				                                    <td NOWRAP>{{ $element -> rif }}</td>
													@if($element->v == 1)
				                                    	<td NOWRAP>{{ $element -> num_cuenta }}</td>
													@else
														<td NOWRAP>{{ $element -> num_cuenta_secu }}</td>
													@endif
				                                    <td NOWRAP>{{ $element -> nombre_comercio }}</td>
				                                    @if($element->v == 1)
														<td NOWRAP style="width: 100px;">{{ number_format($element -> venta_bruta,2,',','.') }}</td>
														<td NOWRAP>--</td>
				                                    @else
														<td NOWRAP>--</td>
														<td NOWRAP>{{ number_format($element -> propina,2,',','.') }}</td>
														<!--<td>{{ $element -> num_cuenta_secu }}</td>-->
				                                    @endif
				                                    <td NOWRAP>{{ number_format($element -> tasa_afiliacion,2,',','.') }} %</td>
				                                    @if($element->v == 1)
														<td NOWRAP>{{ number_format($element -> comision_afiliado_vb,2,',','.') }}</td>
														<td NOWRAP>{{ number_format($element -> abono_al_comercio,2,',','.') }}</td>
				                                    @else
														<td NOWRAP>{{ number_format($element -> comision_afiliado_prop,2,',','.') }}</td>
														<td NOWRAP>--</td>
				                                    @endif
				                                     <td NOWRAP>{{ $element -> mon_nombre }}</td>



				                                    <!--<td NOWRAP>{{ $element -> estado }}</td>-->
				                                    <td NOWRAP>{{ $element -> descargado }}</td>

				                                    <!-- <td class="col-lg-3">
				                                      <div class="btn-group">
				                                          <button class="btn-white btn btn-sm" data-toggle="modal" data-target="#detalle" onclick="show_comercio('{{ $element -> rif}}')" >
				                                            Ver
				                                          </button>
				                                          @permission('comercio-edit')
				                                          <a class="btn-white btn btn-sm" data-target="#detalle_edit" href="{{ route('comercios.edit',$element -> rif) }}">
				                                            Editar
				                                         </a>
				                                         @endpermission

				                                      </div>
			                                    </td> -->
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
				@else
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox">
							 <div class="ibox-content">
							 	<h2>
							 		{{$transComerCount}}<span class="text-navy"> Registros encontrados</span>
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

            	$( "#reportButton" ).on( "click", function() {
            		$("#export").click();
				});



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



            });

            $(document).ready(function() {
			    $('#customers').DataTable( {
			        "scrollX": false,
			        "paginate":false,
			        "searching":false,
			        "info":     false
			    } );
			} );

            function deleteValRif(val){

            	if( val.value == 0 ){
            		$('#rif').val("");
            	}
            }

            function desenfRif(val){
            	if(val.value == ''){
            		$('#rif').val("0");
            	}
            }

            function deleteValComer(val){
            	if( val.value == '-' ){
            		$('#nombreComercio').val("");
            	}
            }

            function desenfComer(val){
            	if(val.value == ''){
            		$('#nombreComercio').val("-");
            	}
            }
						$.get( "{{URL('/divisas')}}",function(data){
							for(var i=0; data.length; i++){
							$("#monedas").append('<option value="'+data[i].mon_id+'">'+data[i].mon_nombre+'</option>');
							}
						});//Fin del desplegable divisa

        </script>
    <!-- end page js -->
    @endsection
