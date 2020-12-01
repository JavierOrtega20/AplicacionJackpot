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
                        <strong>Comprar</strong>
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
                            <h5>3. Introduzca monto y forma de pago</h5>
                        </div>
						
                        <div class="ibox-content">


                            <div class="row">
								<div class="alert alert-info" role="alert">Ingrese toda la información solicitada y presione el botón <strong>Siguiente</strong>.</div>   
								<div class="col-md-5">
                                    <div class="product-images">
                                        <div>
											<img src="{!!asset('img/GiftCard/'.$gift->imagen)!!}" class="img_gift_card">
                                        </div>
                                    </div>
									<div class="m-b-xl ">
										<h2 class="font-bold ">
											{{ $gift->nombregift }}
										</h2>
										<h4>Descripción del Producto:</h4>
										<div class="small text-muted">
											{{ $gift->descripcion }} 																					  
										</div>
									</div>									
                                </div>
                                <div class="col-md-7">
								<form method="POST" action="{{ route('gift.pagar') }}" method="POST" enctype="multipart/form-data" id="formMetodoPago">
									{{ csrf_field() }}	
									<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
									<input id="fk_id_comprador" name="fk_id_comprador" type="hidden" value="{{ $fk_id_comprador }}">
									<input id="fk_id_receptor" name="fk_id_receptor" type="hidden" value="{{ $fk_id_receptor }}">
									<input id="fk_carnet_id_receptor" name="fk_carnet_id_receptor" type="hidden" value="{{ $fk_carnet_id_receptor }}">
									<div class="form-group"><label class="col-sm-2 control-label">Monto {{ $gift->mon_simbolo }}<span class="text-danger">*</span></label>
										<div class="col-sm-10">
										 {!! Form::text('monto', null, array('id' => 'monto', 'placeholder' => 'Monto','class' => 'form-control input-lg m-b', 'onkeypress' => 'return justNumbers(event)', 'onblur' => 'format(this)')) !!}
										 <div id="msgMonto" class="text-danger" ></div>
										</div>
									</div>	
									<div class="form-group">
									<label class="col-sm-2 control-label">Método de Pago<span class="text-danger">*</span></label>
									
										<div class="col-sm-10">
											<select class="form-control  input-lg m-b" name="carnet" id="carnetSelect">
											  <option value="">Seleccione</option>
											  @foreach ($productos as $key => $producto)
												@if($producto['carnet'] == 'Stripe')
													<option value="{{ $producto['carnet'] }}">{{ $producto['carnet'] }}</option>
												@else
													<option value="{{ $producto['carnet'] }}">{{ substr($producto['carnet'],0,4) }} XXXX XXXX {{ substr($producto['carnet'], 12) }}</option>
												@endif												
											  @endforeach										  
											</select>
										</div>
									</div>	
									<div class="form-group"><div class="col-sm-2">&nbsp;</div>
										<div class="col-sm-10">
											<div class="btn-group">
												<a href="{{ url('gift/list') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
												<button id="Confirm_MetodoPago" class="btn btn-primary btn-sm" type="button"><i class="fa fa-arrow-circle-o-right"></i> Pagar</button>
												<button id="Submit_Confirm_MetodoPagoForm" style="display: none" type="submit"></button>
											</div>
										</div>
									</div>									
								</form>	
									
																	
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
	
	$(document).ready(function(){
	  $("#Confirm_MetodoPago").click(function(){
		  if($("#formMetodoPago").valid())
		  {
			  $("#msgMonto").html("");
			  
			  @foreach ($productos as $key => $producto)

				if('{{ $producto["carnet"] }}' == $("#carnetSelect").val())
				{
					if('{{ $producto["tipo_carnet"] }}' == 'Interno')
					{
					  var monto = $("#monto").val();
					  var disponible = '{{ $producto["disponible"] }}';
					  var monto_minimo = '{{ $gift->monto_minimo }}'.replace(".", ",");
					  var propina = 0;
					  var disp = "";
					  var mont = "";
					  var mont_min = "";
					  var preautorizar = false;	

					  if(monto_minimo){
						  monto_minimo = monto_minimo.split(".",10);
						  for(var i = 0;i < monto_minimo.length;i++){
							  mont_min = mont_min.concat(monto_minimo[i])
						  }
					  }else{
						mont_min = '0';
					  }					  

					  if(monto){
						  monto = monto.split(".",10);
						  for(var i = 0;i<monto.length;i++){
							  mont = mont.concat(monto[i])
						  }
					  }else{
						mont = '0';
					  }

					  if(disponible){
						disponible = disponible.split(".",10);
						for(var i = 0;i<disponible.length;i++){
							disp = disp.concat(disponible[i])
						}
					  }else{
						disp = '0';
					  }

					  mont = mont.replace(",",".");
					  monto = parseFloat(mont);
					  disp = disp.replace(",",".");
					  disponible = parseFloat(disp);
					  mont_min = mont_min.replace(",",".");
					  monto_minimo = parseFloat(mont_min);
					  
					  if(monto_minimo > monto){
						  $("#msgMonto").html("El monto mínimo permitido es de " + monto_minimo + '{{ $gift->mon_simbolo }}');
						  return;
					  }
					  
					  //VALIDAR SALDO
						if(monto > disponible){
						console.log( $("#monto").val() );
						swal({
						  title: "Notificación",
						  text: "Agradecemos informar al cliente que debe comunicarse al Centro de Atención President vía  Whatsapp al 0412 Banplus (2267587), llamada al 0212-9092003, correo electrónico Presidentclub@banplus.com.",
						  allowOutsideClick: false,
						  allowEscapeKey: false,
						  type: "warning"
						}).then(function(result){
						  if(result.value){
							  var dni = $("#cedula").val();
							  var monto = $("#monto").val();
							  var producto = $("#carnetSelect").val();
							  
								var url = window.location;


								var pat = /step4/;

								if(pat.test(url) == true){
									url=String(url);

									url=url.replace("/gift/step4",'');
								}					  

								$.ajax({
								  url: url + "/MontoExcedido",
								  data: {
									ci: dni,
									monto: monto,
									producto: producto,
								  },
								  method: "GET",
								  succes: onSuccess
								});//end ajax
								function onSuccess(res){
								  if(res === 'ok'){
									window.location.href = "{{URL('/transacciones')}}";
								  }else{
									window.location.href = "{{URL('/home')}}";
								  }
								}//end function onSuccess
							}//end if result.value
						  })//end .then
						}
						else{
							$("#divLoading").show();
							$("#Submit_Confirm_MetodoPagoForm").click();
						}
					}
					else{
						var monto = $("#monto").val();
						var monto_minimo = '{{ $gift->monto_minimo }}'.replace(".", ",");
						var mont = "";
						var mont_min = "";
						
						if(monto_minimo){
						  monto_minimo = monto_minimo.split(".",10);
						  for(var i = 0;i < monto_minimo.length;i++){
							  mont_min = mont_min.concat(monto_minimo[i])
						  }
						}else{
							mont_min = '0';
						}					  

						if(monto){
						  monto = monto.split(".",10);
						  for(var i = 0;i<monto.length;i++){
							  mont = mont.concat(monto[i])
						  }
						}else{
							mont = '0';
						}

						mont = mont.replace(",",".");
						monto = parseFloat(mont);
						mont_min = mont_min.replace(",",".");
						monto_minimo = parseFloat(mont_min);

						if(monto_minimo > monto){
							$("#msgMonto").html("El monto mínimo permitido es de " + monto_minimo + '{{ $gift->mon_simbolo }}');
						return;
						}
						
						$("#divLoading").show();
						$("#Submit_Confirm_MetodoPagoForm").click();
					}	
				}
			  @endforeach					  		
		  }
	  });	  	  
	});
</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>

{!! JsValidator::formRequest('App\Http\Requests\PagadorGiftCardCreateRequest') !!}
@endsection	