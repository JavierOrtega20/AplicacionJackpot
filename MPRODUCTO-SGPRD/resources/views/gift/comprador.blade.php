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
                            <h5>1. ¿Quien compra la GiftCard?</h5>
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
									<div id="BuscarComprador">
										<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
											<div class="col-sm-10">
											 <div class="col-sm-2">
													  {{ Form::select('nacionalidad_buscar', [
														 'V' => 'V',
														 'E' => 'E',
														 'P' => 'P',
													   ], null, ['id' => 'nacionalidad_buscar','class' => 'form-control  input-lg m-b', 'placeholder'=>'Seleccione', 'required' => 'required', 'style' => 'width: 140%; margin-left: -20%;']
													  ) }}
											  </div>
											  <div class="input-group m-b col-sm-10">
													{!! Form::text('cedula_buscar', null, array('id' => 'cedula_buscar','placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10 ,'onkeyup'=>'this.value=Numero(this.value)')) !!}
												   <span class="form-text m-b-none">Cédula del cliente a quien va dirigida la GiftCard.</span>
											  </div>
											</div>
										</div>
										<div class="form-group"><div class="col-sm-2">&nbsp;</div>
										<div class="col-sm-10">
											<div class="btn-group">
												<a href="{{ url('gift/list') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
												<button id="buttonBuscarComprador" class="btn btn-success btn-sm" type="button"><i class="fa fa-search"></i> Buscar comprador</button>
											</div>
										</div>
										</div>
									</div>								
									<div id="ExisteComprador" style="display: none">
										<form method="POST" action="{{ route('gift.receptor') }}" method="POST" enctype="multipart/form-data">
											{{ csrf_field() }}										
											<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
												<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
												<input id="existe_cliente" name="existe_cliente" type="hidden" value="1">
												<div class="col-sm-10">
												 <div class="col-sm-2">
														  {{ Form::select('nacionalidad_comprador_e', [
															 'V' => 'V',
															 'E' => 'E',
															 'P' => 'P',
														   ], null, ['class' => 'form-control  input-lg m-b', 'required' => 'required','id' => 'nacionalidad_comprador_e','style' => 'width: 150%; margin-left: -30%;']
														  ) }}
												  </div>
												  <div class="input-group m-b col-sm-10">
														{!! Form::text('cedula_comprador_e', null, array('id' => 'cedula_comprador_e','placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10, 'onkeyup'=>'this.value=Numero(this.value)')) !!}
													   <span class="form-text m-b-none">Cédula del cliente que envía la GiftCard.</span>
												  </div>
												</div>
											</div>
											<div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>
												<div class="col-sm-10">
												 {!! Form::text('first_name_comprador_e', null, array('id' => 'first_name_comprador_e','placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'maxlength'=>20,'onkeyup'=>'this.value=Text(this.value)')) !!}
												</div>
											</div>
											<div class="form-group"><label class="col-sm-2 control-label">Apellido <span class="text-danger">*</span></label>
												<div class="col-sm-10">
												 {!! Form::text('last_name_comprador_e', null, array('id' => 'last_name_comprador_e','placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)')) !!}
												</div>
											</div>
											<div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico<span class="text-danger">*</span></label>
												<div class="col-sm-10">
												{!! Form::text('email_comprador_e', null, array('id'=>'email_comprador_e', 'placeholder' => 'Correo Electrónico', 'class' => 'form-control input-lg m-b', 'maxlength'=>50)) !!}
													<div id="msgEmail" class="text-danger" ></div>
												</div>
											</div>
											<div class="form-group"><label class="col-sm-2 control-label">Número Telefonico<span class="text-danger">*</span></label>
												<div class="col-sm-10">
													<div class="col-sm-3">
													  {{ Form::select('cod_tel_comprador_e', [
														 '58412' => '0412',
														 '58414' => '0414',
														 '58424' => '0424',
														 '58416' => '0416',
														 '58426' => '0426',
													   ], null, ['class' => 'form-control  input-lg m-b','required' => 'required', 'placeholder'=>'Seleccione', 'id'=> 'cod_tel_comprador_e','style' => 'width: 130%; margin-left: -20%;']
													  ) }}
													</div>
													<div class="input-group m-b col-sm-9">
														{!! Form::text('num_tel_comprador_e', null, array('id'=> 'num_tel_comprador_e', 'placeholder' => 'Número Telefonico','class' => 'form-control input-lg m-b', 'maxlength'=>7 )) !!}
													</div>																								 
												</div>
											</div>
											<div class="form-group"><div class="col-sm-2">&nbsp;</div>
												<div class="col-sm-10">
													<div class="btn-group">
														<a href="{{ url('gift/list') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
														<button id="Confirm_SioExisteClienteForm" disabled class="btn btn-primary btn-sm" type="button"><i class="fa fa-arrow-circle-o-right"></i> Siguiente</button>
														<button id="Submit_SiExisteClienteForm" style="display: none" type="submit"></button>
													</div>
												</div>
											</div>	
										</form>									
									</div>
									<div id="NoExisteComprador" style="display: none">
										<form method="POST" action="{{ route('gift.receptor') }}" method="POST" enctype="multipart/form-data">
											{{ csrf_field() }}										
											<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
												<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
												<input id="existe_cliente" name="existe_cliente" type="hidden" value="0">
												<div class="col-sm-10">
												 <div class="col-sm-2">
														  {{ Form::select('nacionalidad_comprador', [
															 'V' => 'V',
															 'E' => 'E',
															 'P' => 'P',
														   ], null, ['class' => 'form-control  input-lg m-b', 'required' => 'required','id' => 'nacionalidad_comprador','style' => 'width: 140%; margin-left: -20%;']
														  ) }}
												  </div>
												  <div class="input-group m-b col-sm-10">
														{!! Form::text('cedula_comprador', null, array('id' => 'cedula_comprador','placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10, 'onkeyup'=>'this.value=Numero(this.value)')) !!}
													   <span class="form-text m-b-none">Cédula del cliente que envía la GiftCard.</span>
												  </div>
												</div>
											</div>
											<div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>
												<div class="col-sm-10">
												 {!! Form::text('first_name_comprador', null, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'maxlength'=>20,'onkeyup'=>'this.value=Text(this.value)')) !!}
												</div>
											</div>
											<div class="form-group"><label class="col-sm-2 control-label">Apellido <span class="text-danger">*</span></label>
												<div class="col-sm-10">
												 {!! Form::text('last_name_comprador', null, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)')) !!}
												</div>
											</div>
											<div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico<span class="text-danger">*</span></label>
												<div class="col-sm-10">
												{!! Form::text('email_comprador', null, array('id'=>'email', 'placeholder' => 'Correo Electrónico', 'class' => 'form-control input-lg m-b', 'maxlength'=>50)) !!}
													<div id="msgEmail" class="text-danger" ></div>
												</div>
											</div>
											<div class="form-group"><label class="col-sm-2 control-label">Número Telefonico<span class="text-danger">*</span></label>
												<div class="col-sm-10">
													<div class="col-sm-2">
													  {{ Form::select('cod_tel_comprador', [
														 '58412' => '0412',
														 '58414' => '0414',
														 '58424' => '0424',
														 '58416' => '0416',
														 '58426' => '0426',
													   ], null, ['class' => 'form-control  input-lg m-b','required' => 'required', 'placeholder'=>'Seleccione', 'id'=> 'cod_tel','style' => 'width: 140%; margin-left: -20%;']
													  ) }}
													</div>
													<div class="input-group m-b col-sm-10">
														{!! Form::text('num_tel_comprador', null, array('placeholder' => 'Número Telefonico','class' => 'form-control input-lg m-b', 'maxlength'=>7 )) !!}
													</div>																								 
												</div>
											</div>
											<div class="form-group"><div class="col-sm-2">&nbsp;</div>
												<div class="col-sm-10">
													<div class="btn-group">
														<a href="{{ url('gift/list') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
														<button id="Confirm_NoExisteClienteForm" disabled class="btn btn-primary btn-sm" type="button"><i class="fa fa-arrow-circle-o-right"></i> Siguiente</button>
														<button id="Submit_NoExisteClienteForm" style="display: none" type="submit"></button>
													</div>
												</div>
											</div>	
										</form>									
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
	$(document).ready(function(){
	  $("#buttonBuscarComprador").click(function(){
		consultaDatos();
	  });
	  $("#Confirm_SioExisteClienteForm").click(function(){
		$("#Submit_SiExisteClienteForm").click();
	  });
	  $("#Confirm_NoExisteClienteForm").click(function(){
		$("#Submit_NoExisteClienteForm").click();
	  });	  
		$('#nacionalidad_comprador_e').bind('mousedown', function (event) { 
			event.preventDefault();
			event.stopImmediatePropagation();
		});	  
		$('#nacionalidad_comprador').bind('mousedown', function (event) { 
			event.preventDefault();
			event.stopImmediatePropagation();
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
  
  function consultaDatos()
  {	
	var cedula = $("#cedula_buscar").val();
	var nacionalidad = $("#nacionalidad_buscar").val();
	
	if(cedula == "")
	{
		alert("Ingrese el número de cédula");
		return;
	}
	
	if(nacionalidad == "")
	{
		alert("Ingrese el tipo de documento");
		return;
	}	
	
	var url = window.location;

	var pat = /comprador/;

	if(pat.test(url) == true){
		url=String(url);

		url=url.replace("/comprador/{{ $gift->cod_emisor }}",'');
		url=url.replace("/comprador/{{ $gift->cod_emisor }}",'');
	}	
	
	$("#divLoading").show();
	
	$.get(url + "/consultaDatos/"+ cedula+ '/' + nacionalidad, function(respuesta){

	  if (!respuesta['fallido'] && respuesta.length) {	  
		  
		  $("#nacionalidad_comprador_e").val(respuesta[0].nacionalidad);
		  $("#cedula_comprador_e").val(respuesta[0].cedula);
		  $("#first_name_comprador_e").val(respuesta[0].first_name);
		  $("#last_name_comprador_e").val(respuesta[0].last_name);
		  $("#email_comprador_e").val(respuesta[0].email);
		  $("#cod_tel_comprador_e").val(respuesta[0].cod_tel);
		  $("#num_tel_comprador_e").val(respuesta[0].num_tel);
		  		  
		  $('#cedula_comprador_e').prop('readonly', true);
		  $('#first_name_comprador_e').prop('readonly', true);
		  $('#last_name_comprador_e').prop('readonly', true);
		  $('#email_comprador_e').prop('readonly', true);
		  $('#num_tel_comprador_e').prop('readonly', true);
		  $('#cod_tel_comprador_e').attr('disabled',true);
		  $('#nacionalidad_comprador_e').attr('readonly', true);
		  $('#Confirm_SioExisteClienteForm').attr('disabled', false);
		  		  	  
		  $("#NoExisteComprador").css("display", "none");
		  $("#ExisteComprador").css("display", "block");
		  $("#BuscarComprador").css("display", "none");
		  
							 
	  }else{
		  $("#nacionalidad_comprador").val(nacionalidad);
		  $("#cedula_comprador").val(cedula);
		  $('#cedula_comprador').prop('readonly', true);
		  $('#nacionalidad_comprador').attr('readonly', true);
		  $('#Confirm_NoExisteClienteForm').attr('disabled', false);
		  
		  
		  $("#NoExisteComprador").css("display", "block");
		  $("#ExisteComprador").css("display", "none");
		  $("#BuscarComprador").css("display", "none");			  
	  }

	  $("#divLoading").hide();
	});			  
  }
</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>

{!! JsValidator::formRequest('App\Http\Requests\PagadorGiftCardCreateRequest') !!}
@endsection	