@extends('layouts.app')
@section('titulo', 'Carga de Limites y Disponibles')

@section('contenido')

<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-8">
		<h2><i class="fa fa-credit-card"></i>   Preliminar Carga Masiva de Limites y Disponibles</h2>
		<ol class="breadcrumb">
			<li>
				<a href="{{ url('home') }}">Panel</a>
			</li>
			<li>Carga Masiva de Limites y Disponibles
			</li>
			<li class="active">
				<strong>Preliminar Carga Masiva de Limites y Disponibles</strong>
			</li>
		</ol>
	</div>
	<div class="col-lg-4">
		<div class="title-action">

		</div>
	</div>

</div>


<div class="wrapper wrapper-content ecommerce">
	<div class="wrapper wrapper-content ecommerce">
	@include('flash::message')
	
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Carga Masiva de Limites y Disponibles</h5>
			</div>

			<div class="ibox-content">
				<div class="row">
					<div class="col-sm-6 b-r">
						<h3 class="m-t-none m-b">
							Archivos en carpeta
						</h3>

						<form role="form" method="POST" action="{{ url('transacciones/insertFile') }}"  class="form-horizontal" enctype="multipart/form-data" accept-charset="UTF-8" files="true">
							{{ csrf_field() }}
							<div class="row">
								<div class="col-sm-12">
										<label class="custom-file col-sm-12" for="fecha_desde">
											<div class="input-group date">
												@foreach($files as $file)
													@if($file[1] == 1)
														<label><h4><i class="fa fa-file-excel-o"></i> {{ $file[0] }}</h4><p style="color:red;">* Ya fue procesado un archivo con este nombre</p></label></br>
													@else	
														@if($file[1] == 0)
															<a><h4><i class="fa fa-file-excel-o"></i> {{ $file[0] }}</h4></a><input type="hidden" id="FileName" name="FileName" value="{{ $file[0] }}"></br>
														@else
															<label><h4><i class="fa fa-file"></i> {{ $file[0] }}</h4><p style="color:red;">* Este archivo no es permitido</p></label></br>
														@endif
													@endif
													
												@endforeach	
											</div>
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
							@if(count($files) == 1)
								@if($files[0][1] == 0)
									<div>
										<button type="submit" class="btn btn-block btn-primary" id="importPagos">
											Procesar Archivo
										</button>
									</div>									
								@endif								
							@else
								@if(count($files) > 1)
								<div>
									<button type="submit" class="btn btn-block btn-primary" id="importPagos" disabled="true" onclick="javascript:this.form.submit();this.disabled= true;">
										Procesar Archivo
									</button>
								</div>
								<p style="color:red;">* Importante: para procesar un arhivo de forma masiva solo debe existir uno en la carpeta</p>
								@else
									
								@endif								
							@endif
						</form>
					</div>
					<div class="col-sm-6">
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
				 						Archivos Procesados
				 					</span>
								</h2>
								<div class="hr-line-dashed"></div>

								<table  id="customers" class="footable table table-stripped toggle-arrow-tiny" data-page-size="10">
									<thead>
										<th>ARCHIVO</th>
										<th>REGISTROS</th>
										<th>PROCESADOS</th>
										<th>ERRORES</th>
										<th>DETALLE</th>
										<th>FECHA</th>
										<th>RESPONSABLE</th>
									</thead>
									<tbody>
										
										@foreach($ProcessedFiles as $element)
											<tr>
												<td>
													@if($element['processed'])
														<a href="{{ url('/') }}/processed-files/{{ $element['Filename']}}"><i class="fa fa-check" aria-hidden="true"> {{ $element['Filename']}}</i></a>
													@else
														@if($element['InProgress'])
															<a href="{{ url('/') }}/processed-files/{{ $element['Filename']}}"><i class="fa fa-spinner" aria-hidden="true"> {{ $element['Filename']}} (En progreso)</i></a>
														@else
															<a href="#"><i class="fa fa-clock-o" aria-hidden="true"> {{ $element['Filename']}} (En espera)</i></a>
														@endif
														
													@endif													
												</td>
												<td>{{ $element['TotalRows']}}</td>
												<td>{{ $element['TotalProcessed']}}</td>
												<td>{{ $element['TotalErrors']}}</td>
												<td>
													<ul>
													 {!! $element['ErrorDetail'] !!}
													  
													</ul>  												
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