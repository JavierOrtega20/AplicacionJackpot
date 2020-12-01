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
                            <h5>3. A quien va dirigida la GiftCard</h5>
                        </div>
						
                        <div class="ibox-content">
                            <div class="row">							
								@if($existe_beneficiario == true)
									{!! Form::open(array('route' => 'gift.step7','method'=>'POST','class'=>'form-horizontal', 'id'=>'formSiExisteBeneficiario')) !!}
										<input id="tipo_tarjeta" name="tipo_tarjeta" type="hidden" value="{{ $tipo_tarjeta }}">
										<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
										<input id="fk_id_comercio" name="fk_id_comercio" type="hidden" value="{{ $gift->fk_id_comer }}">								
										<input id="monto" name="monto" type="hidden" value="{{ $monto }}">
										<input id="cedula_pagador" name="cedula_pagador" type="hidden" value="{{ $cedula_pagador }}">										
										
										<input id="carnet" name="carnet" type="hidden" value="{{ $producto_pagador }}">
										<input id="existe_beneficiario" name="existe_beneficiario" type="hidden" value="1">
										
										<input id="comercioPropina" name="comercioPropina" type="hidden">
										<input id="propina_monto" name="propina_monto" type="hidden">
										<input id="propina" name="propina" type="hidden">
										<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
											<div class="col-sm-10">
											 <div class="col-sm-2">
													  {{ Form::select('nacionalidad_beneficiario', [
														 'V' => 'V',
														 'E' => 'E',
														 'P' => 'P',
													   ], $beneficiario->nacionalidad, ['class' => 'form-control  input-lg m-b', 'required' => 'required', 'id' => 'idNac', 'readonly' => 'readonly', 'style' => 'width: 140%; margin-left: -20%;']
													  ) }}
											  </div>
											  <div class="input-group m-b col-sm-10">
													{!! Form::text('cedula_beneficiario', $beneficiario->dni, array('placeholder' => 'Cédula', 'id' => 'cedula','class' => 'form-control input-lg m-b', 'readonly' => 'readonly', 'minlength'=>3,'maxlength'=>10 ,'onkeyup'=>'this.value=Numero(this.value)')) !!}
												   <span class="form-text m-b-none">Cédula del cliente que envía la GiftCard.</span>
											  </div>
											</div>
										</div>										
										<div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>
											<div class="col-sm-10">
											 {!! Form::text('first_name_beneficiario', $beneficiario->first_name, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'readonly' => 'readonly', 'maxlength'=>20,'onkeyup'=>'this.value=Text(this.value)')) !!}
											</div>
										</div>
										<div class="form-group"><label class="col-sm-2 control-label">Apellido <span class="text-danger">*</span></label>
											<div class="col-sm-10">
											 {!! Form::text('last_name_beneficiario', $beneficiario->last_name, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'readonly' => 'readonly', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)')) !!}
											</div>
										</div>
										<div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico<span class="text-danger">*</span></label>
											<div class="col-sm-10">
											{!! Form::text('email_beneficiario', $beneficiario->email, array('id'=>'email', 'placeholder' => 'Correo Electrónico', 'onblur'=>'validarEmail()', 'readonly' => 'readonly', 'class' => 'form-control input-lg m-b', 'maxlength'=>50)) !!}
												<div id="msgEmail" class="text-danger" ></div>
											</div>
										</div>								
										<div class="form-group"><label class="col-sm-2 control-label">Número Telefonico<span class="text-danger">*</span></label>
											<div class="col-sm-10">
												<div class="col-sm-2">
												  {{ Form::select('cod_tel_beneficiario', [
													 '58412' => '0412',
													 '58414' => '0414',
													 '58424' => '0424',
													 '58416' => '0416',
													 '58426' => '0426',
												   ], $beneficiario->cod_tel, ['class' => 'form-control  input-lg m-b','required' => 'required', 'readonly' => 'readonly','id'=> 'cod_tel','style' => 'width: 140%; margin-left: -20%;']
												  ) }}
												</div>
												<div class="input-group m-b col-sm-10">
													{!! Form::text('num_tel_beneficiario', $beneficiario->num_tel, array('placeholder' => 'Número Telefonico', 'readonly' => 'readonly','class' => 'form-control input-lg m-b', 'maxlength'=>7 )) !!}
												</div>																								 
											</div>
										</div>										
										<div class="form-group"><div class="col-sm-2">&nbsp;</div>
											<div class="col-sm-10">
												<div class="btn-group">
													<a href="{{ url('gift/list') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>													
													<button class="btn btn-primary btn-sm" id="Confirm_SiExisteBeneficiarioForm" type="button"><i class="fa fa-arrow-circle-o-right"></i> Siguiente</button>
													<button id="Submit_SiExisteBeneficiarioForm" style="display: none" type="submit"></button>													
												</div>
											</div>
										</div>								
									{!! Form::close() !!}										
								@else
									{!! Form::open(array('route' => 'gift.step7','method'=>'POST','class'=>'form-horizontal', 'id'=>'formNoExisteBeneficiario')) !!}
										<div class="alert alert-info" role="alert">Estimado comercio, el Número de Cédula del cliente que recibira la Gift Card no se encuentra registrado. Ingrese toda la información solicitada y presione el botón <strong>Siguiente</strong>.</div>
										<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
															
											<input id="tipo_tarjeta" name="tipo_tarjeta" type="hidden" value="{{ $tipo_tarjeta }}">
											<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
											<input id="fk_id_comercio" name="fk_id_comercio" type="hidden" value="{{ $gift->fk_id_comer }}">
											<input id="monto" name="monto" type="hidden" value="{{ $monto }}">
											<input id="cedula_pagador" name="cedula_pagador" type="hidden" value="{{ $cedula_pagador }}">										
											<input id="carnet" name="carnet" type="hidden" value="{{ $producto_pagador }}">
											<input id="existe_beneficiario" name="existe_beneficiario" type="hidden" value="0">
											
											<input id="comercioPropina" name="comercioPropina" type="hidden">
											<input id="propina_monto" name="propina_monto" type="hidden">
											<input id="propina" name="propina" type="hidden">												

											<div class="col-sm-10">
											 <div class="col-sm-2">
													  {{ Form::select('nacionalidad', [
														 'V' => 'V',
														 'E' => 'E',
														 'P' => 'P',
													   ], $nacionalidad_beneficiario, ['class' => 'form-control  input-lg m-b','id' => 'idNac','readonly' => 'readonly', 'required' => 'required', 'style' => 'width: 140%; margin-left: -20%;']
													  ) }}
											  </div>
											  <div class="input-group m-b col-sm-10">
													{!! Form::text('cedula', $cedula_beneficiario, array('placeholder' => 'Cédula','readonly' => 'readonly', 'class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10 ,'onkeyup'=>'this.value=Numero(this.value)')) !!}
												   <span class="form-text m-b-none">Cédula del cliente a quien va dirigida la GiftCard.</span>
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
											<div class="col-sm-10">
												<div class="btn-group">
													<a href="{{ url('gift/list') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
													<button class="btn btn-primary btn-sm" id="Confirm_NoExisteBeneficiarioForm" type="button"><i class="fa fa-arrow-circle-o-right"></i> Siguiente</button>
													<button id="Submit_NoExisteBeneficiarioForm" style="display: none" type="submit"></button>
												</div>
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
		@if($existe_beneficiario == true)
			$('#idNac').bind('mousedown', function (event) { 
				event.preventDefault();
				event.stopImmediatePropagation();
			});
			$('#cod_tel').bind('mousedown', function (event) { 
				event.preventDefault();
				event.stopImmediatePropagation();
			});
			
			  $("#Confirm_SiExisteBeneficiarioForm").click(function(){
				  if($("#formSiExisteBeneficiario").valid())
				  {
					var opcion = confirm("¿Confirma que toda la información suministrada esta completa y correcta? Al aceptar se guardaran estos datos y no los podra cambiar.");
					if (opcion == true) {
						$("#Submit_SiExisteBeneficiarioForm").click();
						$("#divLoading").show();
						$("#Submit_SiExisteBeneficiarioForm").prop("disabled",true);
					}			  
				  }
			  });				

		@else			
			$('#idNac').bind('mousedown', function (event) { 
				event.preventDefault();
				event.stopImmediatePropagation();
			});

			  $("#Confirm_NoExisteBeneficiarioForm").click(function(){
				  if($("#formNoExisteBeneficiario").valid())
				  {
					var opcion = confirm("¿Confirma que toda la información suministrada esta completa y correcta? Al aceptar se guardaran estos datos y no los podra cambiar.");
					if (opcion == true) {
						$("#Submit_NoExisteBeneficiarioForm").click();
						$("#divLoading").show();
						$("#Submit_NoExisteBeneficiarioForm").prop("disabled",true);
					}			  
				  }
			  });			
		@endif	  
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

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\BeneficiarioGiftCardCreateRequest') !!}

@endsection	

