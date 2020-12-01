@extends('layouts.app')
@section('titulo', 'GiftCard')        
@section('contenido')
@php
	use App\Models\User;
	
	$user= User::find(Auth::user()->id);
	$roles= $user->roles;
	$rol = null;
	foreach ($roles as $value) {
		$rol = $value->id;
	}
@endphp	
<?php
	if(isset($_POST['cedula'])){
		$cedula = $_POST['cedula'];
	}else{
		$cedula = 0;
	}
	
	if(isset($_POST['comercio_emisor'])){
		$comercio_emisor = $_POST['comercio_emisor'];
	}else{
		$comercio_emisor = '';
	}	
		
?>	
		
		<style>
			.img_gift_card {
			  width: 400px;
			  height: 250px;
			  border-radius: 10px;
			}		
		</style>
		<div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-10">
                <h2>GiftCard</h2>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.html">Panel</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a>GiftCard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        <strong>Ventas</strong>
                    </li>
                </ol>
            </div>
            <div class="col-lg-2">

            </div>
        </div>
		<div class="wrapper wrapper-content animated fadeInRight">
			<div class="row">
				<div class="col-md-12">
					<div class="ibox">
                        <div class="ibox-title">
                            <h5>Ventas de GiftCard</h5>
                        </div>						
                        <div class="ibox-content">
                <div class="row">


                 <form method="post" action=" {{ url('gift/ventas') }} ">
                        {{ csrf_field() }}
                        <input type="hidden" name="filter" value="true">
                            <div class="panel-body">
                                <div class="form-inline" >
                                    <div class="form-group" >
                                        <label class="control-label" for="fecha_desde">Fecha desde: </label><br>
                                        <div class="input-group date" id="datepicker">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('fecha_desde', date('d/m/Y'), ['class'=>'input-sm form-control','id'=>'fecha_desde','readonly']) !!}
                                    </div>

                                </div>
                                <div class="form-group" id="data_5">
                                        <label class="control-label" for="dateranges">Fecha hasta: </label><br>
                                        <div class="input-group date" id="datepicker">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('fecha_hasta', date('d/m/Y'), ['class'=>'input-sm form-control','id'=>'fecha_hasta','readonly']) !!}

                                        </div>

                                </div>
								@if($rol != 3)
                                  <div class="form-group">
                                    <label class="control-label" for="dateranges">Comercio Emisor:</label><br>
                                    <div class="input-group date">
                                      <select class="input-sm form-control" name="comercio_emisor" id="comercio_emisor">
                                            <option value="">Seleccione</option>
											@foreach($comercios as $element)
												@if($comercio_emisor == $element->rif)
													<option selected value="{{ $element->rif }}">({{ $element->rif }}) {{ $element->comercio }}</option>
												@else
													<option value="{{ $element->rif }}">({{ $element->rif }}) {{ $element->comercio }}</option>
												@endif
											@endforeach											
                                      </select>
                                    </div>
                                  </div>									
								@endif
                                <div class="form-group" id="data_5">
                                    <label class="control-label" for="dateranges">C&eacute;dula:</label><br>
                                    <div class="input-group date">
										@if($cedula == 0)
											<input type="text" name="cedula" id="cedula" onkeypress="return justNumbers(event);" class="input-sm form-control" maxlength="8">
										@else
											<input type="text" name="cedula" id="cedula" onkeypress="return justNumbers(event);" value="{{ $cedula }}" class="input-sm form-control" maxlength="8">
										@endif                                      
                                    </div>

                                </div>

                                <button type="submit" class="btn btn-primary" style="margin-top: 18px;">Buscar</button>

                                </div>
                            </div>
                    </form>
				</div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-content">
                          <h2>
                             {{ $num_resultados }} <span class="text-navy"> Ventas</span>
                          </h2>
                          <div class="hr-line-dashed"></div>
                            <div class="table-responsive">
                            <table id="datatab" class="table">
                                <thead>
                                <tr>
                                    <th>Fecha</th>
									<th NOWRAP>Comercio</th>								
                                    <th NOWRAP>Cédula</th>
									<th NOWRAP>Tipo Pago</th>
                                    <th NOWRAP>Monto GiftCard</th>									
									<th NOWRAP>Comisión Comercio</th>
									<th NOWRAP>Comisión Cliente</th>
                                </tr>
                                </thead>
                                <tbody>
									@foreach ($ventas_giftcard as $key => $gift)
										<tr>
											<td>{{ $gift->fecha }}</td>
											<td>{{ $gift->rif }} {{ $gift->comercio }}</td>
											<td>{{ $gift->nacionalidad }}-{{ $gift->dni }}</td>
											<td>@if ($gift->tipo_producto_compra == "")
													{{'Presidents Pay'}}
												@else
													{{ $gift->tipo_producto_compra}}
												@endif
											</td>
											<td>{{ str_replace(".", ",",$gift->monto) }} {{ $gift->mon_simbolo }}</td>
											<td>
												@if($gift->pago_comision == 1)
													{{ str_replace(".", ",",$gift->comision_monto) }} {{ $gift->mon_simbolo }}
												@else
													0,00 {{ $gift->mon_simbolo }}
												@endif
											</td>
											<td>
												@if($gift->pago_comision == 2)
													{{ str_replace(".", ",",$gift->comision_monto) }} {{ $gift->mon_simbolo }}
												@else
													0,00 {{ $gift->mon_simbolo }}
												@endif											
											</td>
										</tr>									
									@endforeach								                                   
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>				

                        </div>						
					</div>
					<hr>
				</div>						
			</div>			
		</div>		
@endsection	
@section('scripts')

<script type="text/javascript">
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
                    ignoreReadonly: true,
                    todayBtn: "linked",
                    keyboardNavigation: false,
                    forceParse: false,
                    calendarWeeks: true,
                    format: 'dd/mm/yyyy',
                    autoclose: true
                });
				
				$('#datatab').DataTable({
				  responsive: true,
				  "language": idioma,
				  "order": [ 0, "desc" ],
					dom: 'Bfrtip',
					buttons: [
						{ 
							extend: 'excel',
							className: 'btn btn-primary',
							text: 'Descargar',
							title: 'Reporte de Ventas'						
						}
					]				  

				});				

            });
</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>


@endsection	