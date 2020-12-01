@extends('layouts.app')
@section('titulo', 'Comercios')

@section('contenido')

<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-8">
		<h2><i class="fa fa-credit-card"></i>   Exportar comercios</h2>
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
				<h5>Reporte de  <small>comercio</small></h5>

			</div>
			<div class="ibox-content">
				<div class="row">
					<div class="col-sm-6 b-r"><h3 class="m-t-none m-b">Query de comercios</h3>
						<p>Ingrese los criterios para la descarga del reporte.</p>
						<form role="form" method="POST" action=" {{ url('comercios/export_comercio') }}"  class="form-horizontal">
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
							<div>
								<button type="submit" class="btn btn-block btn-primary" id="export" name="export" data-dismiss="modal">Descargar</button>

							</div>
						</form>
					</div>
					<div class="col-sm-6"><h4>Descarga de archivo de Excel</h4>
						<p class="text-center">
							<a href=""><i class="fa fa-file-excel-o big-icon"></i></a>
						</p>

					</div>
				</div>
			</div>
		</div>


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

            });

        </script>
    <!-- end page js -->
    @endsection