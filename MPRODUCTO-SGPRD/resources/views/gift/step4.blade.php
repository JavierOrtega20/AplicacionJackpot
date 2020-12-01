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
									{!! Form::open(array('route' => 'gift.step5','method'=>'POST','class'=>'form-horizontal', 'id'=>'formSiExisteCliente')) !!}
										<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
										<input id="tipo_tarjeta" name="tipo_tarjeta" type="hidden" value="{{ $tipo_tarjeta }}">
										<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
										<input id="monto" name="monto" type="hidden" value="{{ $monto }}">
										<input id="existe_cliente" name="existe_cliente" type="hidden" value="1">
											<div class="col-sm-10">
											 <div class="col-sm-2">
													  {{ Form::select('nacionalidad_existe', [
														 'V' => 'V',
														 'E' => 'E',
														 'P' => 'P',
													   ], $quien_compra->nacionalidad, ['class' => 'form-control  input-lg m-b', 'required' => 'required', 'id' => 'idNac', 'readonly' => 'readonly', 'style' => 'width: 140%; margin-left: -20%;']
													  ) }}
											  </div>
											  <div class="input-group m-b col-sm-10">
													{!! Form::text('cedula_existe', $quien_compra->dni, array('placeholder' => 'Cédula', 'id' => 'cedula','class' => 'form-control input-lg m-b', 'readonly' => 'readonly', 'minlength'=>3,'maxlength'=>10 ,'onkeyup'=>'this.value=Numero(this.value)')) !!}
												   <span class="form-text m-b-none">Cédula del cliente que envía la GiftCard.</span>
											  </div>
											</div>
										</div>
										<div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>
											<div class="col-sm-10">
											 {!! Form::text('first_name_existe', $quien_compra->first_name, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'readonly' => 'readonly', 'maxlength'=>20,'onkeyup'=>'this.value=Text(this.value)')) !!}
											</div>
										</div>
										<div class="form-group"><label class="col-sm-2 control-label">Apellido <span class="text-danger">*</span></label>
											<div class="col-sm-10">
											 {!! Form::text('last_name_existe', $quien_compra->last_name, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'readonly' => 'readonly', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)')) !!}
											</div>
										</div>
										<div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico<span class="text-danger">*</span></label>
											<div class="col-sm-10">
											{!! Form::text('email_existe', $quien_compra->email, array('id'=>'email', 'placeholder' => 'Correo Electrónico', 'onblur'=>'validarEmail()', 'readonly' => 'readonly', 'class' => 'form-control input-lg m-b', 'maxlength'=>50)) !!}
												<div id="msgEmail" class="text-danger" ></div>
											</div>
										</div>
										<div class="form-group"><label class="col-sm-2 control-label">Número Telefonico<span class="text-danger">*</span></label>
											<div class="col-sm-10">
												<div class="col-sm-2">
												  {{ Form::select('cod_tel_existe', [
													 '58412' => '0412',
													 '58414' => '0414',
													 '58424' => '0424',
													 '58416' => '0416',
													 '58426' => '0426',
												   ], $quien_compra->cod_tel, ['class' => 'form-control  input-lg m-b','required' => 'required', 'readonly' => 'readonly','id'=> 'cod_tel','style' => 'width: 140%; margin-left: -20%;']
												  ) }}
												</div>
												<div class="input-group m-b col-sm-10">
													{!! Form::text('num_tel_existe', $quien_compra->num_tel, array('placeholder' => 'Número Telefonico', 'readonly' => 'readonly','class' => 'form-control input-lg m-b', 'maxlength'=>7 )) !!}
												</div>																								 
											</div>
										</div>
										@if($tipo_tarjeta == 'tarjeta_nacional')
											<div class="form-group">
											<label class="col-sm-2 control-label">Método de Pago<span class="text-danger">*</span></label>
											
												<div class="col-sm-10">
													<select class="form-control  input-lg m-b" name="carnet" id="carnetSelect">
													  <option value="">Seleccione</option>
													  @foreach ($productos as $key => $producto)
														<option value="{{ $producto['carnet'] }}">{{ $producto['nombre_producto'] }} / {{ substr($producto['carnet'],0,4) }} XXXX XXXX {{ substr($producto['carnet'], 12) }}</option>
													  @endforeach										  
													</select>
												</div>
											</div>
											<div class="form-group"><div class="col-sm-2">&nbsp;</div>
												<div class="col-sm-10">
													<div class="btn-group">
														<a href="{{ url('gift/list') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
														<button id="Confirm_SiExisteClienteForm" class="btn btn-primary btn-sm" type="button"><i class="fa fa-arrow-circle-o-right"></i> Siguiente</button>
														<button id="Submit_SiExisteClienteForm" style="display: none" type="submit"></button>
													</div>
												</div>
											</div>
										@else
											<div class="form-group"><div class="col-sm-2">&nbsp;</div>
												<div class="col-sm-10">
													<div class="btn-group">
														<a href="{{ url('gift/list') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
														<button id="Submit_SiExisteClienteForm" class="btn btn-primary btn-sm" type="submit"><i class="fa fa-arrow-circle-o-right"></i> Siguiente</button>
													</div>
												</div>
											</div>											
										@endif										
									{!! Form::close() !!}
								@else
									{!! Form::open(array('route' => 'gift.step5','method'=>'POST','class'=>'form-horizontal', 'id'=>'formNoExisteCliente')) !!}
										<div class="alert alert-info" role="alert">Estimado comercio, el Número de Cédula del cliente que compra la Gift Card no se encuentra registrado. Ingrese toda la información solicitada y presione el botón <strong>Siguiente</strong>.</div>   
										<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
											<input id="tipo_tarjeta" name="tipo_tarjeta" type="hidden" value="{{ $tipo_tarjeta }}">
											<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
											<input id="monto" name="monto" type="hidden" value="{{ $monto }}">
											<input id="existe_cliente" name="existe_cliente" type="hidden" value="0">
											<div class="col-sm-10">
											 <div class="col-sm-2">
													  {{ Form::select('nacionalidad', [
														 'V' => 'V',
														 'E' => 'E',
														 'P' => 'P',
													   ], null, ['class' => 'form-control  input-lg m-b', 'required' => 'required','id' => 'idNac','readonly' => 'readonly', 'style' => 'width: 140%; margin-left: -20%;']
													  ) }}
											  </div>
											  <div class="input-group m-b col-sm-10">
													{!! Form::text('cedula', null, array('placeholder' => 'Cédula','readonly' => 'readonly','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10 ,'onkeyup'=>'this.value=Numero(this.value)')) !!}
												   <span class="form-text m-b-none">Cédula del cliente que envía la GiftCard.</span>
											  </div>
											</div>
										</div>
										<div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>
											<div class="col-sm-10">
											 {!! Form::text('first_name', null, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'maxlength'=>20,'onkeyup'=>'this.value=Text(this.value)')) !!}
											</div>
										</div>
										<div class="form-group"><label class="col-sm-2 control-label">Apellido <span class="text-danger">*</span></label>
											<div class="col-sm-10">
											 {!! Form::text('last_name', null, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)')) !!}
											</div>
										</div>
										<div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico<span class="text-danger">*</span></label>
											<div class="col-sm-10">
											{!! Form::text('email', null, array('id'=>'email', 'placeholder' => 'Correo Electrónico', 'class' => 'form-control input-lg m-b', 'maxlength'=>50)) !!}
												<div id="msgEmail" class="text-danger" ></div>
											</div>
										</div>
										<div class="form-group"><label class="col-sm-2 control-label">Número Telefonico<span class="text-danger">*</span></label>
											<div class="col-sm-10">
												<div class="col-sm-2">
												  {{ Form::select('cod_tel', [
													 '58412' => '0412',
													 '58414' => '0414',
													 '58424' => '0424',
													 '58416' => '0416',
													 '58426' => '0426',
												   ], null, ['class' => 'form-control  input-lg m-b','required' => 'required', 'placeholder'=>'Seleccione', 'id'=> 'cod_tel','style' => 'width: 140%; margin-left: -20%;']
												  ) }}
												</div>
												<div class="input-group m-b col-sm-10">
													{!! Form::text('num_tel', null, array('placeholder' => 'Número Telefonico','class' => 'form-control input-lg m-b', 'maxlength'=>7 )) !!}
												</div>																								 
											</div>
										</div>
										<div class="form-group"><div class="col-sm-2">&nbsp;</div>
											<div class="col-sm-10">
												<div class="btn-group">
													<a href="{{ url('gift/list') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
													<button id="Confirm_NoExisteClienteForm" class="btn btn-primary btn-sm" type="button"><i class="fa fa-arrow-circle-o-right"></i> Siguiente</button>
													<button id="Submit_NoExisteClienteForm" style="display: none" type="submit"></button>
												</div>
											</div>
										</div>																			
									{!! Form::close() !!}									
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
		@if($existe_cliente == true)
			$('#idNac').bind('mousedown', function (event) { 
				event.preventDefault();
				event.stopImmediatePropagation();
			});
			$('#cod_tel').bind('mousedown', function (event) { 
				event.preventDefault();
				event.stopImmediatePropagation();
			});
		@else
			$('#idNac').bind('mousedown', function (event) { 
				event.preventDefault();
				event.stopImmediatePropagation();
			});			
		@endif
	  $("#Submit_Nacional").click(function(){
		document.getElementById("Form_Nacional").submit();
	  });
	  
	  $("#Submit_Internacional").click(function(){
		document.getElementById("Form_Internacional").submit();
	  });	  
	});
	
  function Numero(string){//solo numeros
    var out = '';
    //Se añaden los numeros validas
    var filtro = '1234567890';//Caracteres validos

    for (var i=0; i<string.length; i++)
       if (filtro.indexOf(string.charAt(i)) != -1)
       out += string.charAt(i);
    return out;
  }
  function Text(string){//solo letras
    var out = '';
    //Se añaden las letras validas
    var filtro = 'abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ';//Caracteres validos

    for (var i=0; i<string.length; i++)
       if (filtro.indexOf(string.charAt(i)) != -1)
       out += string.charAt(i);
    return out;
  }
</script>

@if($existe_cliente == false)
<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>

{!! JsValidator::formRequest('App\Http\Requests\PagadorGiftCardCreateRequest') !!}

<script type="text/javascript">
	$(document).ready(function(){
	  $("#Confirm_NoExisteClienteForm").click(function(){
		  if($("#formNoExisteCliente").valid())
		  {
			var opcion = confirm("¿Confirma que toda la información suministrada esta completa y correcta? Al aceptar se guardaran estos datos y no los podra cambiar.");
			if (opcion == true) {
				$("#Submit_NoExisteClienteForm").click();
			}			  
		  }
	  });	  	  
	});
</script>

@else

	@if($tipo_tarjeta == 'tarjeta_nacional')
		<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>

		{!! JsValidator::formRequest('App\Http\Requests\PagadorGiftCardCreateRequest') !!}
		
		<script type="text/javascript">
			$(document).ready(function(){
			  $("#Confirm_SiExisteClienteForm").click(function(){
				  if($("#formSiExisteCliente").valid())
				  {
					  @foreach ($productos as $key => $producto)

						if('{{ $producto["carnet"] }}' == $("#carnetSelect").val())
						{
							if('{{ $producto["tipo_carnet"] }}' == 'Interno')
							{
							  var monto = '{{ $monto }}';
							  var disponible = '{{ $producto["disponible"] }}';
							  var propina = 0;
							  var disp = "";
							  var mont = "";
							  var preautorizar = false;			  

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
									  var monto = '{{ $monto }}';
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
									$("#Submit_SiExisteClienteForm").click();
								}
							}
							else{
								$("#Submit_SiExisteClienteForm").click();
							}	
						}
					  @endforeach					  		
				  }
			  });	  	  
			});
		</script>		
	@endif
	
@endif

@endsection	

