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
								@if($buscar_beneficiario == true)
									{!! Form::open(array('route' => 'gift.step6','method'=>'POST','class'=>'form-horizontal', 'id'=>'formBuscarBeneficiario')) !!}
										<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
											<input id="tipo_tarjeta" name="tipo_tarjeta" type="hidden" value="{{ $tipo_tarjeta }}">
											<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
											<input id="monto" name="monto" type="hidden" value="{{ $monto }}">
											<input id="cedula_pagador" name="cedula_pagador" type="hidden" value="{{ $cedula_pagador }}">
											<input id="producto_pagador" name="producto_pagador" type="hidden" value="{{ $producto_pagador }}">
											<input id="buscar_beneficiario" name="buscar_beneficiario" type="hidden" value="1">
											
											<div class="col-sm-10">
											 <div class="col-sm-2">
													  {{ Form::select('nacionalidad_buscar', [
														 'V' => 'V',
														 'E' => 'E',
														 'P' => 'P',
													   ], null, ['class' => 'form-control  input-lg m-b', 'placeholder'=>'Seleccione', 'required' => 'required', 'style' => 'width: 140%; margin-left: -20%;']
													  ) }}
											  </div>
											  <div class="input-group m-b col-sm-10">
													{!! Form::text('cedula_buscar', null, array('placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10 ,'onkeyup'=>'this.value=Numero(this.value)')) !!}
												   <span class="form-text m-b-none">Cédula del cliente a quien va dirigida la GiftCard.</span>
											  </div>
											</div>
										</div>
										<div class="form-group"><div class="col-sm-2">&nbsp;</div>
											<div class="col-sm-10">
												<div class="btn-group">
													<a href="{{ url('gift/list') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
													<button class="btn btn-success btn-sm" type="submit"><i class="fa fa-search"></i> Buscar beneficiario</button>
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

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\BeneficiarioGiftCardCreateRequest') !!}

@endsection	

