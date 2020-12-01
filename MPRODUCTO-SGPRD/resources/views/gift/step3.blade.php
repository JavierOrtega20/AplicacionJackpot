@extends('layouts.app')
@section('titulo', 'GiftCard')        
@section('contenido')
		<div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-10">
                <h2><i class="fa fa-gift"></i>   Gift Card</h2>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.html">Panel</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a>Gift Card</a>
                    </li>
                    <li class="breadcrumb-item active">
                        <strong>Listado</strong>
                    </li>
                </ol>
            </div>
            <div class="col-lg-2">

            </div>
        </div>
		<div class="wrapper wrapper-content animated fadeInRight">
			<div class="row">
				<div class="col-md-9">
					<div class="ibox">
                        <div class="ibox-title">
                            <h5>2. Seleccione el Método de pago</h5>
                        </div>
						
                        <div class="ibox-content">


                            <div class="row">
								@if($existe_cliente == true)
									@if($puede_pagar_nac == true)
										<div class="col-md-6">
											<div class="payment-card">
												<i class="fa fa-credit-card payment-icon-big text-primary"></i>
												<h2>
													<form method="POST" action="{{ route('gift.step4') }}" method="POST" enctype="multipart/form-data" id="Form_Nacional">
													{{ csrf_field() }}

													<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
													<input id="monto" name="monto" type="hidden" value="{{ $monto }}">
													<input id="metodo_pago" name="metodo_pago" type="hidden" value="tarjeta_nacional">
													<input id="cedula" name="cedula" type="hidden" value="{{$cedula}}">
													<input id="nacionalidad" name="nacionalidad" type="hidden" value="{{$nacionalidad}}">
													<a href="#" id="Submit_Nacional"> Tarjeta Nacional</a>	
													</form>
												</h2>
											</div>
										</div>										
									@endif
								@endif
								@if($gift->mon_id == 1)
									<div class="col-md-6">
										<div class="payment-card">
											<i class="fa fa-cc-visa payment-icon-big text-success"></i>
											<i class="fa fa-cc-mastercard payment-icon-big text-warning"></i>
											<h2>
												<form method="POST" action="{{ route('gift.step4') }}" method="POST" enctype="multipart/form-data" id="Form_Internacional">
												{{ csrf_field() }}

												<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
												<input id="monto" name="monto" type="hidden" value="{{ $monto }}">
												<input id="metodo_pago" name="metodo_pago" type="hidden" value="tarjeta_internacional">
												<input id="cedula" name="cedula" type="hidden" value="{{$cedula}}">
												<input id="nacionalidad" name="nacionalidad" type="hidden" value="{{$nacionalidad}}">
												<a href="#" id="Submit_Internacional"> Tarjeta Internacional</a>	
												</form>																							
											</h2>
										</div>
									</div>
								@endif
                            </div>

                        </div>						
					</div>
					<hr>
				</div>
				<div class="col-md-3">

					<div class="ibox">
						<div class="ibox-title">
							<h5>{{ $gift->nombregift }}</h5>
						</div>
						<div class="ibox-content">
							<span>
								Total GiftCard
							</span>
							<h2 class="font-bold">
								{{ $gift->mon_simbolo }} {{ $monto }}
							</h2>

							<hr/>
							<span class="text-muted small">
								{{ $gift->descripcion }}
							</span>
						</div>
					</div>                
				</div>							
			</div>			
		</div>
@endsection
@section('scripts')
<script type="text/javascript">
	$(document).ready(function(){
	  $("#Submit_Nacional").click(function(){
		document.getElementById("Form_Nacional").submit();
	  });
	  
	  $("#Submit_Internacional").click(function(){
		document.getElementById("Form_Internacional").submit();
	  });	  
	});
	
	$(document).ready(function() {
			window.history.pushState(null, "", window.location.href);        
			window.onpopstate = function() {
				window.history.pushState(null, "", window.location.href);
			};
		});
</script>
@endsection	