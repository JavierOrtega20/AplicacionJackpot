@extends('layouts.app')
@section('titulo', 'Carga de Pagos')

@section('contenido')

<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-8">
		<h2><i class="fa fa-credit-card"></i>   Preliminar Carga Masiva de Pagos</h2>
		<ol class="breadcrumb">
			<li>
				<a href="{{ url('home') }}">Panel</a>
			</li>
			<li>Carga Masiva de Pagos
			</li>
			<li class="active">
				<strong>Preliminar Carga Masiva de Pagos</strong>
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
				<h5>Carga Masiva de <small>Pagos</small></h5>
			</div>

			<div class="ibox-content">
				<div class="row">
					<div class="col-sm-6 b-r">
						<h3 class="m-t-none m-b">
							Carga Masivas de Pagos
						</h3>
						<p>Examine y seleccione el archivo.</p>
						<form role="form" method="POST" action="{{ url('transacciones/uploadPagos') }}"  class="form-horizontal" enctype="multipart/form-data" accept-charset="UTF-8" files="true">
							{{ csrf_field() }}
							<div class="row">
								<div class="col-sm-12">
										<label class="custom-file col-sm-12" for="fecha_desde">
											Seleccione Archivo Excel
											<div class="input-group date">
												<span class="input-group-addon">
													<i class="fa fa-upload"></i>
												</span>
											</div>
											<br>
											<input type="file" id="file" name="file" class="custom-file-input">
											<span class="custom-file-control"></span>
										</label>
								</div>
							</div>
							
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group"></div>
								</div>
								<div class="col-sm-6">
									<div class="form-group"></div>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-6">
									<div class="form-group"></div>
								</div>
								<div class="col-sm-6">
									<div class="form-group"></div>
								</div>
							</div>
							<div>
								<button type="submit" class="btn btn-block btn-primary" id="importPagos" disabled="true" onclick="javascript:this.form.submit();this.disabled= true;">
									Cargar Archivo
								</button>
							</div>
						</form>
					</div>
					<div class="col-sm-6"><h4>Subir de archivo Excel</h4>
						<p class="text-center">
							<a href=""><i class="fa fa-file-excel-o big-icon"></i></a>
						</p>

					</div>
				</div>
			</div>
		</div>
		@if(isset($duplicados))
			@if(count($duplicados) != 0)
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox">
							<div class="ibox-content">
								<h2>
									{{count($duplicados)}}
									<span class="text-navy">
				 						Cédulas duplicadas
				 					</span>
								</h2>
								<div class="hr-line-dashed"></div>

								<table  id="customers" class="footable table table-stripped toggle-arrow-tiny" data-page-size="10">
									<thead>
										<th>CEDULAS</th>
										<th>PAGOS</th>
									</thead>
									<tbody>
										
										@foreach($duplicados as $i => $element)
											<tr>
												<td>{{ $element->cedula}}</td>
												<td>
													{{ number_format($element->saldo, 2, ',', '.') }}
												</td>
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

		@if(isset($nonum))
			@if(count($nonum) != 0)
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox">
							<div class="ibox-content">
								<h2>
									{{count($nonum)}}
									<span class="text-navy">
				 						Errores en registros
				 					</span>
								</h2>
								<div class="hr-line-dashed"></div>

								<table  id="customers" class="footable table table-stripped toggle-arrow-tiny" data-page-size="10">
									<thead>
										<th>CEDULAS</th>
										<th>PAGOS</th>
									</thead>
									<tbody>
										
										@foreach($nonum as $i => $element)
											<tr>
												<td>{{ $element->cedula}}</td>
												<td>{{ $element->saldo}}</td>
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

		@if(isset($cedNoExist))
			@if(count($cedNoExist) != 0)
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox">
							<div class="ibox-content">
								<h2>
									{{count($cedNoExist)}}
									<span class="text-navy">
				 						Cédulas inexistente en Base de Datos
				 					</span>
								</h2>
								<div class="hr-line-dashed"></div>

								<table  id="customers" class="footable table table-stripped toggle-arrow-tiny" data-page-size="10">
									<thead>
										<th>CEDULAS</th>
										<th>PAGOS</th>
									</thead>
									<tbody>
										
										@foreach($cedNoExist as $i => $element)
											<tr>
												<td>{{ $element->cedula}}</td>
												<td>
													{{ number_format($element->saldo, 2, ',', '.') }}
												</td>
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

		@if(isset($arrayPagoMayorSaldo))
			@if(count($arrayPagoMayorSaldo) != 0)
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox">
							<div class="ibox-content">
								<h2>
									{{count($arrayPagoMayorSaldo)}}
									<span class="text-navy">
				 						Pagos que exceden el saldo
				 					</span>
								</h2>
								<div class="hr-line-dashed"></div>

								<table  id="customers" class="footable table table-stripped toggle-arrow-tiny" data-page-size="10">
									<thead>
										<th>CEDULAS</th>
										<th>PAGOS</th>
									</thead>
									<tbody>
										
										@foreach($arrayPagoMayorSaldo as $i => $element)
											<tr>
												<td>{{ $element->cedula}}</td>
												<td>
													{{ number_format($element->saldo, 2, ',', '.') }}
												</td>
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
</div>

@stop

@section('scripts')

<script>
	$(document).ready(function() {
		$("#file").change(function(){

			if($("#file").val() !='' ){
				$("#importPagos").prop( "disabled", false );
			}else{
				$("#importPagos").prop( "disabled", true );
			}

		});
		$('.footable').footable();
	});

	function checkKeyCode(evt)
	{

		var evt = (evt) ? evt : ((event) ? event : null);
		var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
		if(event.keyCode==116)
		{
			evt.keyCode=0;
			return false
		}
	}
	document.onkeydown=checkKeyCode;


</script>
			
</script>

@endsection