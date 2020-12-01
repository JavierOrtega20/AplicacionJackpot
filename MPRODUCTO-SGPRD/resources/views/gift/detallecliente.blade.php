@extends('layouts.app')
@section('titulo', 'GiftCard')        
@section('contenido')
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
                        <strong>Detalle Cliente</strong>
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
                            <h5>Detalle GiftCard</h5>
                        </div>						
                        <div class="ibox-content">
                <div class="row">
				<div class="col-md-3">
					<div class="forum-icon">
						<i class="fa fa-address-card-o"></i>
					</div>
					<a class="forum-item-title">Cédula:</a>
					<div class="forum-sub-title">{{ $datos_giftcard->nacionalidad }}-{{ $datos_giftcard->dni }}</div>
				</div>
				<div class="col-md-3">
					<div class="forum-icon">
						<i class="fa fa-user-o"></i>
					</div>
					<a class="forum-item-title">Nombre y Apellido:</a>
					<div class="forum-sub-title">{{ $datos_giftcard->first_name }} {{ $datos_giftcard->last_name }}</div>
				</div>
				<div class="col-md-3">
					<div class="forum-icon">
						<i class="fa fa-envelope-o"></i>
					</div>
					<a class="forum-item-title">Email:</a>
					<div class="forum-sub-title">{{ $datos_giftcard->email }}</div>
				</div>
				<div class="col-md-3">
					<div class="forum-icon">
						<i class="fa fa-phone"></i>
					</div>
					<a class="forum-item-title">Telefono:</a>
					<div class="forum-sub-title">{{ str_replace("58","0",$datos_giftcard->cod_tel.'-'.$datos_giftcard->num_tel) }}</div>
				</div>					
				</div>
				</br>				
				<div class="row">
				<div class="col-md-3">
					<div class="forum-icon">
						<i class="fa fa-calendar"></i>
					</div>
					<a class="forum-item-title">Compra GiftCard:</a>
					<div class="forum-sub-title">{{ $datos_giftcard->fecha }}</div>
				</div>				
				<div class="col-md-3">
					<div class="forum-icon">
						<i class="fa fa-clock-o"></i>
					</div>
					<a class="forum-item-title">Fecha Vigencia:</a>
					<div class="forum-sub-title">{{ $datos_giftcard->vencimiento }}</div>
				</div>					
				<div class="col-md-3">
					<div class="forum-icon">
						<i class="fa fa-credit-card"></i>
					</div>
					<a class="forum-item-title">Monto Total GiftCard:</a>
					<div class="forum-sub-title">{{ str_replace(".", ",",$datos_giftcard->monto) }} {{ $datos_giftcard->mon_simbolo }}</div>
				</div>
				<div class="col-md-3">
					<div class="forum-icon">
						<i class="fa fa-shield"></i>
					</div>
					<a class="forum-item-title">Saldo:</a>
					<div class="forum-sub-title">{{ str_replace(".", ",",$saldo->monto) }} {{ $datos_giftcard->mon_simbolo }}</div>
				</div>									
				</div>
				</br>
				<div class="row">								
				<div class="col-md-3">
					<div class="forum-icon">
						<i class="fa fa-calendar-o"></i>
					</div>
					<a class="forum-item-title">Última Compra:</a>
					@if($ultima_compra)
						<div class="forum-sub-title">{{ $ultima_compra->fecha }}</div>
					@else
						<div class="forum-sub-title">--</div>
					@endif					
				</div>
				<div class="col-md-9">
					<div class="forum-icon">
						<i class="fa fa-shopping-cart"></i>
					</div>
					<a class="forum-item-title">Comercio:</a>
					<div class="forum-sub-title">({{ $datos_giftcard->rif }}) {{ $datos_giftcard->comercio }}</div>
				</div>					
				</div>
				</br>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-content">
                          <h2>
						  {{ $num_resultados }} <span class="text-navy"> Registros</span>
                          </h2>
                          <div class="hr-line-dashed"></div>
                            <div class="table-responsive">
                            <table id="datatab" class="table">
                                <thead>
                                <tr>
                                    <th>Fecha Compra</th>
                                    <th NOWRAP>Cédula</th>
                                    <th NOWRAP>Monto GiftCard</th>
									<th NOWRAP>Monto Consumo</th>
									<th NOWRAP>Saldo</th>
                                </tr>
                                </thead>
                                <tbody>
									@foreach ($consumos_giftcard as $key => $consumos)
										<tr>
											<td>{{ $consumos->fecha }}</td>
											<td>{{ $consumos->nacionalidad }}-{{ $consumos->dni }}</td>
											<td>0,00 {{ $datos_giftcard->mon_simbolo }}</td>
											<td>{{ str_replace(".", ",",$consumos->monto_consumo) }} {{ $datos_giftcard->mon_simbolo }}</td>
											<td>{{ $consumos->saldo }} {{ $datos_giftcard->mon_simbolo }}</td>
										</tr>									
									@endforeach	
									@foreach ($compra_giftcard as $key => $giftcard)
										<tr>
											<td>{{ $giftcard->fecha }}</td>
											<td>{{ $giftcard->nacionalidad }}-{{ $giftcard->dni }}</td>
											<td>{{ $giftcard->monto }} {{ $datos_giftcard->mon_simbolo }}</td>
											<td>0,00 {{ $datos_giftcard->mon_simbolo }}</td>
											<td>{{ str_replace(".", ",",$giftcard->saldo) }} {{ $datos_giftcard->mon_simbolo }}</td>
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
				  "order": [ 0, "desc" ]

				});				

            });
</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>


@endsection	