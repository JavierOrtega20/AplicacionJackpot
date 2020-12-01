@extends('layouts.app')
@section('titulo', 'Transacciones')

<?php

	
	if(isset($_POST['fecha_desde'])){
		$fecha_desde = explode("/", $_POST['fecha_desde']);
		$fecha_desde =  $fecha_desde[0]."-".$fecha_desde[1]."-".$fecha_desde[2];
	}else{
		$fecha_desde = date('d-m-Y 00:00:00',strtotime(date('d-m-Y 00:00:00')));
	}

	if(isset($_POST['fecha_hasta'])){
		$fecha_hasta = explode("/",$_POST['fecha_hasta']);
		$fecha_hasta =  $fecha_hasta[0]."-".$fecha_hasta[1]."-".$fecha_hasta[2];
	}else{
		$fecha_hasta = date('d-m-Y 23:59:59',strtotime(date('d-m-Y 23:59:59')));
	}

	if(isset($_POST['estado'])){
		$estado = $_POST['estado'];
	}else{
		$estado = '1000';
	}

	if(isset($_POST['comercio'])){
		$comercio = $_POST['comercio'];
	}else{
		$comercio = '0';
	}

	if(isset($_POST['monto'])){
		$monto = $_POST['monto'];
	}else{
		$monto = 0;
	}

	if(isset($_POST['cliente'])){
		$cliente = $_POST['cliente'];
	}else{
		$cliente = 0;
	}

	if(isset($_POST['moneda'])){
		$moneda = $_POST['moneda'];
	}else{
		$moneda = 2;
	}

?>

@section('contenido')

<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-8">
		<h2><i class="fa fa-credit-card"></i>   Preliminar Consolidado de transacciones</h2>
		<ol class="breadcrumb">
			<li>
				<a href="{{ url('home') }}">Panel</a>
			</li>
			<li>Reporte Preliminar Consolidado de Transacciones
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
						<h5>Reporte Preliminar Consolidado de Transacciones</h5>
				</div>

				<div class="ibox-content">
					<div class="row">
						<div class="col-sm-6 b-r">
							<p>Ingrese los criterios para la descarga del reporte.</p>
							<form method="post" action=" {{ url('transacciones/preview_transactions') }} ">
								{{ csrf_field() }}
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="fecha_desde">Fecha desde</label>
											<div class="input-group date" id="datepicker">
												<span class="input-group-addon">
													<i class="fa fa-calendar"></i>
												</span>
												{!! Form::text('fecha_desde', date('d/m/Y'), ['class'=>'input-sm form-control','id'=>'fecha_desde']) !!}
											</div>
										</div>
									</div>

									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label" for="dateranges">Fecha hasta: </label>
											<br>
											<div class="input-group date" id="datepicker">
												<span class="input-group-addon">
													<i class="fa fa-calendar"></i>
												</span>
												{!! Form::text('fecha_hasta', date('d/m/Y'), ['class'=>'input-sm form-control','id'=>'fecha_hasta']) !!}

											</div>
										</div>
									</div>

									@if($rol == 2 || $rol == 4 || $rol == 6 || $EsComercioMaster)
									<div class="col-sm-4">
										<div class="form-group">
											<label class="control-label" for="dateranges">Comercios: </label><br>
											<div class="input-group date" >
												<select class="input-sm form-control" name="comercio" id="comercio">
													<option value="0">Seleccione</option>
													@if(isset($comercios))
														@foreach($comercios as $c => $v)
														    @if($v->descripcion != 'jackpotImportPagos')

														    	@if($comercio == $v->id)
																	<option value="{{ $v->id }}" selected="true">{{ $v->descripcion }}</option>
																@else
															    	<option value="{{ $v->id }}">{{ $v->descripcion }}</option>
															    @endif
															@endif
														@endforeach
													@endif
												</select>
											</div>
										</div>
									</div>
									@endif

									<div class="col-sm-4">
										<div class="form-group">
											<label class="control-label" for="dateranges">Monto: </label><br>
											<div class="input-group date" >
											@if($monto == 0)
												<input type="text" placeholder="Monto" name="monto" id="monto" class="input-sm form-control" maxlength ="10" value="0" onkeypress="return justNumbers(event);" onblur="format(this)">
											@else
												<input type="text" placeholder="Monto" name="monto" id="monto" class="input-sm form-control" maxlength ="10" value="{{$monto}}" onkeypress="return justNumbers(event);" onblur="format(this)">
											@endif
											</div>
										</div>
									</div>

									@if($rol == 1 || $rol == 2 || $rol == 4 || $rol == 6 || $rol == 3)
									<div class="col-sm-4">
										<div class="form-group">
											<label class="control-label" for="dateranges">Cliente: </label><br>
											<div class="input-group date" >
												@if($cliente == 0)
													<input type="text" placeholder="Cedula" name="cliente" id="cliente" class="input-sm form-control" maxlength ="50" value="0" onkeypress="return justNumbers(event);">
												@else
													<input type="text" placeholder="Cedula" name="cliente" id="cliente" class="input-sm form-control" maxlength ="50" value="{{$cliente}}" onkeypress="return justNumbers(event);">
												@endif
											</div>
										</div>
									</div>

									<div class="col-sm-4">
										<div class="form-group">
											<label class="control-label" for="dateranges">Monedas: </label><br>
											<div class="input-group date" >
												<select class="form-control m-b" name="moneda" id="monedas">															
												</select>
											</div>
										</div>
									</div>



									@endif
									<div>
										<button type="submit" class="btn btn-block btn-primary" >Buscar</button>
									</div>
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


@if(isset($transaccionesCount))

	@if($transaccionesCount != 0)

	 <div class="row">
		<div class="col-lg-12">
			<div class="ibox">
				 <div class="ibox-content">
				 	<h2>
				 		{{count($transacciones)}}
				 		<span class="text-navy"> Transacciones</span>
						<span class="text-navy" style="margin-left: 70%;">
				 			<a href="{{ url('transacciones/export_transactions',['fecha_desde'=>$fecha_desde,'fecha_hasta'=>$fecha_hasta,'estado'=>$estado,'comercio'=>$comercio,'monto'=>$monto,'cliente'=>$cliente, 'moneda'=>$moneda]) }}" class="btn btn-primary" id="descargar">
                			<i class="fa fa-book"></i>
                				Descargar
                			</a>
				 		</span>
				 	</h2>
				 	<div class="hr-line-dashed"></div>
					<div class="table-responsive">					
						<table  id="datatab" class="table">
				 		@if($rol == 2 || $rol == 4 || $rol == 6 || $rol == 1 || $rol == 3)
							<thead>
								<tr>
										<th>Ref</th>
										<th>Tipo</th>
										<th>Fecha</th>
										<th>Cédula</th>
										@if ($rol != 3)
											<th NOWRAP>TARJETA DE MEMBRESIA</th>										
										@endif
										<th NOWRAP>Moneda</th>
										<th>Cliente</th>
										<th>RIF</th>
										<th>Razón Social</th>
										<th>Monto</th>
										<th>Propina</th>
										<th>Abono al comercio</th>
										<th>Tasa</th>
										<th>Comision Afiliado</th>
										<th>Total</th>
									<!--<th NOWRAP>N°. CTA. PPAL</th>
									<th NOWRAP>N°. CTA. PROPINA</th>-->
										<th>Estado</th>
										@if ($rol != 3)
											<th NOWRAP>Canal</th>										
										@endif																				
										<th>Procesado</th>
								</tr>
							</thead>
							<tbody>
								<?php $num = count($transacciones); ?>
								@foreach($transacciones as $i => $element)
									<tr>
											<td>{{$element->REFERENCIA}}</td>
											<td>{{$element->origen}}</td>											
											<td>{{$element->FECHA}}</td>
											<td>{{$element->NACIONALIDAD}}-{{$element->CEDULA}}</td>
										@if ($rol != 3)
											@if ($rol == 2)
											<td  NOWRAP>{{ $element->NUM_TARJETA_MEMBRESIA }}</td>
											@else
											<td  NOWRAP>{{ substr($element->NUM_TARJETA_MEMBRESIA,-20,4) .' XXXX XXXX '. substr($element->NUM_TARJETA_MEMBRESIA,-4) }}</td>
											@endif
										@endif
											<td NOWRAP>
											{{$element->mon_nombre}}
											</td>										
											<td>{{$element->NOMBRE}} {{$element->APELLIDO}}</td>
										<td  NOWRAP>{{$element->RIF}}</td>
											<td>{{$element->nombre_comercio}}</td>
										<td  NOWRAP>{{number_format($element->CONSUMO_CLIENTE, 2, ',', '.')}}</td>
										<td  NOWRAP>{{number_format($element->PROPINA, 2, ',', '.')}}</td>
										<td  NOWRAP>{{number_format($element->abono_al_comercio, 2, ',', '.')}}
										</td>
										<td  NOWRAP>{{number_format($element->tasa_afiliacion, 2, ',', '.')}} %</td>
										<td  NOWRAP>{{number_format($element->comision_afiliado, 2, ',', '.')}}</td>
										<td  NOWRAP>{{number_format($element->TOTAL_CONSUMO_CLIENTE, 2, ',', '.')}}
										</td>
										<!--<td  NOWRAP>
											{{ $element->num_cta_princ }}
										</td>
										<td  NOWRAP>
											{{ $element->num_cta_secu }}
										</td>-->
										@if($element->ESTADO == 0)
											@if($element->REVERSO != null)
												<td  NOWRAP>Cancelada por Reverso</td>
											@else
												<td  NOWRAP>Aprobada</td>
											@endif
										@elseif($element->ESTADO == 1)
											<td  NOWRAP>Por Autorizar</td>
										@elseif($element->ESTADO == 2)
											<td  NOWRAP>Cancelada</td>
										@elseif($element->ESTADO == 3)
											<td  NOWRAP>Rechazada</td>
										@elseif($element->ESTADO == 4)
											<td  NOWRAP>Reverso</td>
										@elseif($element->ESTADO == 6)
											<td  NOWRAP>Aprobada</td>
										@endif
										@if ($rol != 3)
											<td  NOWRAP>{{$element->canal}}</td>										
										@endif											
										@if($element->PROCESADO)
											<td  NOWRAP>{{$element->PROCESADO}}</td>
										@else
											<td  NOWRAP>--</td>
										@endif
									</tr>
									<?php $num--; ?>
								@endforeach
							</tbody>
						@endif
				 		<!--elseif($rol==3)
							<thead>
								<tr>
									<th NOWRAP>ID</th>
									<th NOWRAP>REFERENCIA</th>
									<th NOWRAP>FECHA HORA (DD/MM/AA)</th>
									<th NOWRAP>CÉDULA</th>
									<th NOWRAP>TARJETA DE MEMBRESIA</th>
									<th NOWRAP>NOMBRE</th>
									<th NOWRAP>APELLIDO</th>
									<th NOWRAP>CONSUMO DE CLIENTE</th>
									<th NOWRAP>PROPINA</th>
									<th NOWRAP>N°. CTA. PPAL</th>
									<th NOWRAP>N°. CTA. PROPINA</th>
									<th NOWRAP>ESTADO</th>
								</tr>
							</thead>
							<tbody>
								<?php //$num = count($transacciones); ?>
								@foreach($transacciones as $i => $element)
									<tr>
										<td NOWRAP>
											{{ $num }}
										</td>
										<td NOWRAP>{{$element->REFERENCIA}}</td>
										<td NOWRAP>{{$element->FECHA}}</td>
										<td NOWRAP>{{$element->NACIONALIDAD}} - {{$element->CEDULA}}</td>
										<td NOWRAP>{{$element->NUM_TARJETA_MEMBRESIA}}</td>
										<td NOWRAP>{{$element->NOMBRE}}</td>
										<td NOWRAP>{{$element->APELLIDO}}</td>
										<td NOWRAP>{{number_format($element->CONSUMO_CLIENTE, 2, ',', '.')}}</td>
										<td NOWRAP>{{number_format($element->PROPINA, 2, ',', '.')}}</td>
										<td NOWRAP>
											{{ $element->num_cta_princ }}
										</td>
										<td NOWRAP>
											{{ $element->num_cta_secu }}
										</td>
										@if($element->ESTADO == 0)
											<td NOWRAP>Aprobada</td>
										@elseif($element->ESTADO == 1)
											<td NOWRAP>Por Autorizar</td>
										@elseif($element->ESTADO == 2)
											<td NOWRAP>Cancelada</td>
										@elseif($element->ESTADO == 3)
											<td NOWRAP>Rechazada</td>
										@elseif($element->ESTADO == 4)
											<td NOWRAP>Reverso</td>
										@endif

									</tr>
									<?php //$num--; ?>
								@endforeach
							</tbody>

				 	endif-->
				 		<tfoot>
                                <tr>
                                    <td colspan="17">
                                        <ul class="pagination pull-right"></ul>
                                    </td>
                                </tr>
                            </tfoot>
				 	</table>
				 </div>
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
				 		{{$transaccionesCount}}<span class="text-navy"> Transacciones encontradas</span>
				 	</h2>
				 </div>
			</div>
		</div>
	</div>
	@endif
@endif
</div>
</div>
@stop

@section('scripts')


<!-- Page-Level Scripts -->
<script>

	function Text(string){//solo letras
		    var out = '';
		    //Se añaden las letras validas
		    var filtro = 'abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ ';//Caracteres validos

		    for (var i=0; i<string.length; i++)
		       if (filtro.indexOf(string.charAt(i)) != -1)
		       out += string.charAt(i);
		    return out;
  	}

	function justNumbers(e){

        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) || (keynum == 46) || (keynum == 44))
        return true;

        return /\d/.test(String.fromCharCode(keynum));
  }

  function format(input){

            var num = input.value.replace(/\./g,'');
            if(!isNaN(num)){
                  num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
                  num = num.split('').reverse().join('').replace(/^[\.]/,'');
                  input.value = num;
            }else{
                  //$("#msg-formato").html('Solo se permiten valores númericos');
                  //input.value = input.value.replace(/[^\d\.]*/g,'');
                  num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
                  num = num.split('').reverse().join('').replace(/^[\.]/,'');
                  input.value = num;
            }

  }
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

		 $('.footable').footable();
	});

        $(document).ready(function(){
    $('#datatab').DataTable({
      responsive: true,
      "language": idioma,
      	    } );
	} );


	$(document).ready(function() {
	    $('#customers').DataTable( {
	        "scrollX": true,
	        "paginate":false,
	        "searching":false,
	        "info":     false
	    } );
	} );

	$.get( "{{URL('/divisas')}}",function(data){
		if(data.length > 1)
		{
			//$("#monedas").append('<option value="" disabled selected>Moneda</option>');
			for(var i=0; data.length; i++){
				if (window.moneda && window.moneda == data[i].mon_id ) {
					$("#monedas").append('<option selected="selected" value="'+data[i].mon_id+'">'+data[i].mon_nombre+'</option>');
					
				} else {
					$("#monedas").append('<option value="'+data[i].mon_id+'">'+data[i].mon_nombre+'</option>');
					
				}
			}			
		}
		else{
				if (window.moneda && window.moneda == data[0].mon_id ) {
					$("#monedas").append('<option selected="selected" value="'+data[0].mon_id+'">'+data[0].mon_nombre+'</option>');
					
				} else {
					$("#monedas").append('<option value="'+data[0].mon_id+'">'+data[0].mon_nombre+'</option>');
					
				}			
		}
	});//Fin del desplegable divisa

</script>
<!-- end page js -->
@endsection
