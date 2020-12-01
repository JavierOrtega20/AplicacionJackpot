@extends('layouts.app')
@section('titulo', 'GiftCard')        
@section('contenido')
		<style>
			.img_gift_card {
			  width: 300px;
			  height: 187px;
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
                        <strong>Venta</strong>
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
                            <h5></h5>
                        </div>
						
                        <div class="ibox-content">


                            <div class="row">								
								<div class="col-md-4">
                                    <div class="product-images">
                                        <div>
											<img src="" style="display:none" id="img_front_giftcard" class="img_gift_card">
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
                                <div class="col-md-8">
								<form method="POST" action="{{ route('gift.pagar') }}" method="POST" id="formGiftCard" enctype="multipart/form-data">
								{{ csrf_field() }}	
									<input id="existe_receptor" name="existe_receptor" type="hidden" value="0">
									<input id="existe_comprador" name="existe_comprador" type="hidden" value="0">									
									<input id="carnet_real" name="carnet_real" type="hidden" value="">
									<input id="monto_real" name="monto_real" type="hidden" value="">
									<input id="beneficiario_buscado" name="beneficiario_buscado" type="hidden" value="0">
									<input id="comprador_buscado" name="comprador_buscado" type="hidden" value="0">
									<input id="imagen_gift_back" name="imagen_gift_back" type="hidden" value="">
									<div class="col-lg-12">
										<div class="form-group row"><label class="col-sm-2 col-form-label">Importe:</label>
											@foreach($ImagenesRadioButton as $i => $element)
												@if($element->monto != "Otros")
													<div class="col-sm-1">
														<div class="i-checks"><label> <input type="radio" value="{{ $element->monto }}" name="monto_radio"> <i></i>${{ $element->monto }}</label></div>
													</div>
												@endif										
											@endforeach
											<div class="col-sm-1">
												<div class="i-checks"><label> <input type="radio" value="Otros" name="monto_radio"> <i></i>Otro</label></div>
											</div>									
											<div class="col-sm-3">
												{!! Form::text('monto', null, array('id' => 'monto','readonly' => 'readonly', 'placeholder' => '$','class' => 'form-control','maxlength'=>20)) !!}
											</div>
											<div id="msgMonto" class="text-danger col-sm-10" ></div>
										</div>																		
										<div class="ibox ">
											<div class="ibox-title">
												<h5><i class="fa fa-id-card-o" aria-hidden="true"></i> ¿Quien compra la GiftCard?</h5>
												<div class="ibox-tools">
													<a class="collapse-link">
														<i class="fa fa-chevron-up"></i>
													</a>
												</div>
											</div>
											<div class="ibox-content">
												<div class="row">
													<div id="BuscarComprador">
														<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
															 <div class="col-sm-3">
																	  {{ Form::select('nacionalidad_buscar', [
																		 'V' => 'V',
																		 'E' => 'E',
																		 'P' => 'P',
																	   ], null, ['id' => 'nacionalidad_buscar','class' => 'form-control  input-lg m-b', 'placeholder'=>'', 'required' => 'required']
																	  ) }}
															  </div>
															  <div class="col-sm-7">
																	{!! Form::text('cedula_buscar', null, array('id' => 'cedula_buscar','placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10 ,'onkeyup'=>'this.value=Numero(this.value)')) !!}
																   <span class="form-text m-b-none">Cédula del cliente que envía la GiftCard.</span>
																   <div id="msgCedula_Comprador" class="text-danger" ></div>
															  </div>
														</div>
														<div class="form-group"><div class="col-sm-2">&nbsp;</div>
														<div class="col-sm-10">
															<div class="btn-group">
																<button id="buttonBuscarComprador" class="btn btn-success btn-sm" type="button"><i class="fa fa-search"></i> Buscar comprador</button>
															</div>
														</div>
														</div>
													</div>												
													<div id="ExisteComprador" style="display: none">
															<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>																
																 <div class="col-sm-3">
																		  {{ Form::select('nacionalidad_comprador_e', [
																			 'V' => 'V',
																			 'E' => 'E',
																			 'P' => 'P',
																		   ], null, ['class' => 'form-control  input-lg m-b', 'required' => 'required','id' => 'nacionalidad_comprador_e']
																		  ) }}
																  </div>
																  <div class="col-sm-7">
																		{!! Form::text('cedula_comprador_e', null, array('id' => 'cedula_comprador_e','placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10, 'onkeyup'=>'this.value=Numero(this.value)')) !!}
																	   <span class="form-text m-b-none">Cédula del cliente que envía la GiftCard.</span>
																  </div>
																  <div class="input-group m-b col-sm-12"><label class="col-sm-2 control-label"></label>
																		<div class="col-sm-10">
																		 <button id="buttonCancelarComprador_e" class="btn btn-danger btn-sm" type="button"><i class="fa fa-search"></i> Buscar de nuevo</button>
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
																 {!! Form::text('last_name_comprador_e', null, array('id' => 'last_name_comprador_e','placeholder' => 'Apellido','class' => 'form-control input-lg m-b', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)')) !!}
																</div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico<span class="text-danger">*</span></label>
																<div class="col-sm-10">
																{!! Form::text('email_comprador_e', null, array('id'=>'email_comprador_e', 'placeholder' => 'Correo Electrónico', 'class' => 'form-control input-lg m-b', 'maxlength'=>50)) !!}
																	<div id="msgEmail" class="text-danger" ></div>
																</div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Número Telefonico<span class="text-danger">*</span></label>
																<div class="col-sm-3">
																  {{ Form::select('cod_tel_comprador_e', [
																	 '58412' => '0412',
																	 '58414' => '0414',
																	 '58424' => '0424',
																	 '58416' => '0416',
																	 '58426' => '0426',
																   ], null, ['class' => 'form-control  input-lg m-b','required' => 'required', 'placeholder'=>'', 'id'=> 'cod_tel_comprador_e']
																  ) }}
																</div>
																<div class="col-sm-7">
																	{!! Form::text('num_tel_comprador_e', null, array('id'=> 'num_tel_comprador_e', 'placeholder' => 'Número Telefonico','class' => 'form-control input-lg m-b', 'maxlength'=>7 )) !!}
																</div>																								 
															</div>							
													</div>
													<div id="NoExisteComprador" style="display: none">									
															<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
																<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
																 <div class="col-sm-3">
																		  {{ Form::select('nacionalidad_comprador', [
																			 'V' => 'V',
																			 'E' => 'E',
																			 'P' => 'P',
																		   ], null, ['class' => 'form-control  input-lg m-b', 'required' => 'required','id' => 'nacionalidad_comprador']
																		  ) }}
																  </div>
																  <div class="col-sm-7">
																		{!! Form::text('cedula_comprador', null, array('id' => 'cedula_comprador','placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10, 'onkeyup'=>'this.value=Numero(this.value)')) !!}
																	   <span class="form-text m-b-none">Cédula del cliente que envía la GiftCard.</span>																	   
																  </div>
																  <div class="input-group m-b col-sm-12"><label class="col-sm-2 control-label"></label>
																		<div class="col-sm-10">
																		 <button id="buttonCancelarComprador" class="btn btn-danger btn-sm" type="button"><i class="fa fa-search"></i> Buscar de nuevo</button>
																		</div>																  																		
																  </div>
															</div>													
															<div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>
																<div class="col-sm-10">
																 {!! Form::text('first_name_comprador', null, array('placeholder' => 'Nombre','id' => 'first_name_comprador','class' => 'form-control input-lg m-b', 'maxlength'=>20,'onkeyup'=>'this.value=Text(this.value)')) !!}
																</div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Apellido <span class="text-danger">*</span></label>
																<div class="col-sm-10">
																 {!! Form::text('last_name_comprador', null, array('placeholder' => 'Apellido','id' => 'last_name_comprador','class' => 'form-control input-lg m-b', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)')) !!}
																</div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico<span class="text-danger">*</span></label>
																<div class="col-sm-10">
																{!! Form::text('email_comprador', null, array('id'=>'email_comprador', 'placeholder' => 'Correo Electrónico', 'class' => 'form-control input-lg m-b', 'maxlength'=>50)) !!}
																	<div id="msgEmail" class="text-danger" ></div>
																</div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Número Telefonico<span class="text-danger">*</span></label>
																	<div class="col-sm-3">
																	  {{ Form::select('cod_tel_comprador', [
																		 '58412' => '0412',
																		 '58414' => '0414',
																		 '58424' => '0424',
																		 '58416' => '0416',
																		 '58426' => '0426',
																	   ], null, ['class' => 'form-control  input-lg m-b','required' => 'required', 'placeholder'=>'', 'id'=> 'cod_tel_comprador']
																	  ) }}
																	</div>
																	<div class="col-sm-7">
																		{!! Form::text('num_tel_comprador', null, array('placeholder' => 'Número Telefonico','id' => 'num_tel_comprador','class' => 'form-control input-lg m-b', 'maxlength'=>7 )) !!}
																	</div>																								 
															</div>							
													</div>													
												</div>
											</div>
										</div>
									</div>									

									<div class="col-lg-12">
										<div class="ibox ">
											<div class="ibox-title">
												<h5><i class="fa fa-id-card" aria-hidden="true"></i> ¿Quien recibe la Giftcard?</h5>
												<div class="ibox-tools">
													<a class="collapse-link">
														<i class="fa fa-chevron-up"></i>
													</a>
												</div>
											</div>
											<div class="ibox-content">
												<div class="row">
													<div id="BuscarReceptor">
														<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
															 <div class="col-sm-3">
																	  {{ Form::select('nacionalidad_receptor_buscar', [
																		 'V' => 'V',
																		 'E' => 'E',
																		 'P' => 'P',
																	   ], null, ['id' => 'nacionalidad_receptor_buscar','class' => 'form-control  input-lg m-b', 'placeholder'=>'', 'required' => 'required']
																	  ) }}
															  </div>
															  <div class="col-sm-7">
																	{!! Form::text('cedula_receptor_buscar', null, array('id' => 'cedula_receptor_buscar','placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10 ,'onkeyup'=>'this.value=Numero(this.value)')) !!}
																   <span class="form-text m-b-none">Cédula del receptor de la GiftCard.</span>
																   <div id="msgCedula_Receptor" class="text-danger" ></div>
															  </div>
														</div>
														<div class="form-group"><div class="col-sm-2">&nbsp;</div>
														<div class="col-sm-10">
															<div class="btn-group">
																<button id="buttonBuscarReceptor" class="btn btn-success btn-sm" type="button"><i class="fa fa-search"></i> Buscar receptor</button>
															</div>
														</div>
														</div>
													</div>												
													<div id="ExisteReceptor" style="display: none">														
															<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>
																 <div class="col-sm-3">
																		  {{ Form::select('nacionalidad_receptor_e', [
																			 'V' => 'V',
																			 'E' => 'E',
																			 'P' => 'P',
																		   ], null, ['class' => 'form-control  input-lg m-b', 'required' => 'required','id' => 'nacionalidad_receptor_e']
																		  ) }}
																  </div>
																  <div class="col-sm-7">
																		{!! Form::text('cedula_receptor_e', null, array('id' => 'cedula_receptor_e','placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10, 'onkeyup'=>'this.value=Numero(this.value)')) !!}
																	   <span class="form-text m-b-none">Cédula del receptor de la GiftCard.</span>
																  </div>
																  <div class="input-group m-b col-sm-12"><label class="col-sm-2 control-label"></label>
																		<div class="col-sm-10">
																		 <button id="buttonCancelarReceptor_e" class="btn btn-danger btn-sm" type="button"><i class="fa fa-search"></i> Buscar de nuevo</button>
																		</div>																  																		
																  </div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>
																<div class="col-sm-10">
																 {!! Form::text('first_name_receptor_e', null, array('id' => 'first_name_receptor_e','placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'maxlength'=>20,'onkeyup'=>'this.value=Text(this.value)')) !!}
																</div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Apellido <span class="text-danger">*</span></label>
																<div class="col-sm-10">
																 {!! Form::text('last_name_receptor_e', null, array('id' => 'last_name_receptor_e','placeholder' => 'Apellido','class' => 'form-control input-lg m-b', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)')) !!}
																</div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico<span class="text-danger">*</span></label>
																<div class="col-sm-10">
																{!! Form::text('email_receptor_e', null, array('id'=>'email_receptor_e', 'placeholder' => 'Correo Electrónico', 'class' => 'form-control input-lg m-b', 'maxlength'=>50)) !!}
																	<div id="msgEmail" class="text-danger" ></div>
																</div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Número Telefonico<span class="text-danger">*</span></label>
																	<div class="col-sm-3">
																	  {{ Form::select('cod_tel_receptor_e', [
																		 '58412' => '0412',
																		 '58414' => '0414',
																		 '58424' => '0424',
																		 '58416' => '0416',
																		 '58426' => '0426',
																	   ], null, ['class' => 'form-control  input-lg m-b','required' => 'required', 'placeholder'=>'', 'id'=> 'cod_tel_receptor_e']
																	  ) }}
																	</div>
																	<div class="col-sm-7">
																		{!! Form::text('num_tel_receptor_e', null, array('id'=> 'num_tel_receptor_e', 'placeholder' => 'Número Telefonico','class' => 'form-control input-lg m-b', 'maxlength'=>7 )) !!}
																	</div>																								 
															</div>
													</div>
													<div id="NoExisteReceptor" style="display: none">
													
														<div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>																
																 <div class="col-sm-3">
																		  {{ Form::select('nacionalidad_receptor', [
																			 'V' => 'V',
																			 'E' => 'E',
																			 'P' => 'P',
																		   ], null, ['class' => 'form-control  input-lg m-b', 'required' => 'required','id' => 'nacionalidad_receptor']
																		  ) }}
																  </div>
																  <div class="col-sm-7">
																		{!! Form::text('cedula_receptor', null, array('id' => 'cedula_receptor','placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10, 'onkeyup'=>'this.value=Numero(this.value)')) !!}
																	   <span class="form-text m-b-none">Cédula del receptor de la GiftCard.</span>																	   
																  </div>
																  <div class="input-group m-b col-sm-12"><label class="col-sm-2 control-label"></label>
																		<div class="col-sm-10">
																		 <button id="buttonCancelarReceptor" class="btn btn-danger btn-sm" type="button"><i class="fa fa-search"></i> Buscar de nuevo</button>
																		</div>																  																		
																  </div>																  
															</div>															
															<div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>
																<div class="col-sm-10">
																 {!! Form::text('first_name_receptor', null, array('placeholder' => 'Nombre','id' => 'first_name_receptor','class' => 'form-control input-lg m-b', 'maxlength'=>20,'onkeyup'=>'this.value=Text(this.value)')) !!}
																</div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Apellido <span class="text-danger">*</span></label>
																<div class="col-sm-10">
																 {!! Form::text('last_name_receptor', null, array('placeholder' => 'Apellido','id' => 'last_name_receptor','class' => 'form-control input-lg m-b', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)')) !!}
																</div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico<span class="text-danger">*</span></label>
																<div class="col-sm-10">
																{!! Form::text('email_receptor', null, array('id'=>'email_receptor', 'placeholder' => 'Correo Electrónico', 'class' => 'form-control input-lg m-b', 'maxlength'=>50)) !!}
																	<div id="msgEmail" class="text-danger" ></div>
																</div>
															</div>
															<div class="form-group"><label class="col-sm-2 control-label">Número Telefonico<span class="text-danger">*</span></label>
																	<div class="col-sm-3">
																	  {{ Form::select('cod_tel_receptor', [
																		 '58412' => '0412',
																		 '58414' => '0414',
																		 '58424' => '0424',
																		 '58416' => '0416',
																		 '58426' => '0426',
																	   ], null, ['class' => 'form-control  input-lg m-b','required' => 'required', 'placeholder'=>'', 'id'=> 'cod_tel_receptor']
																	  ) }}
																	</div>
																	<div class="col-sm-7">
																		{!! Form::text('num_tel_receptor', null, array('placeholder' => 'Número Telefonico','id' => 'num_tel_receptor', 'class' => 'form-control input-lg m-b', 'maxlength'=>7 )) !!}
																	</div>																								 
															</div>
													</div>	
												</div>
											</div>
										</div>
									</div>
									
									<div class="col-lg-12" style="display: none">
										<div class="ibox ">
											<div class="ibox-title">
												<h5><i class="fa fa-calendar" aria-hidden="true"></i> ¿Cuando se entrega?</h5>
												<div class="ibox-tools">
													<a class="collapse-link">
														<i class="fa fa-chevron-up"></i>
													</a>
												</div>
											</div>
											<div class="ibox-content">
												<div class="row">
													<div class="form-group"><label class="col-sm-2 control-label">Fecha: </label>
														<div class="col-sm-10">
														 {!! Form::text('fecha_entrega', null, ['class'=>'input-sm form-control','placeholder' => 'Ahora mismo','id'=>'fecha_entrega']) !!}
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<div class="col-lg-12">
										<div class="ibox ">
											<div class="ibox-title">
												<h5><i class="fa fa-credit-card" aria-hidden="true"></i> Medio de pago</h5>
												<div class="ibox-tools">
													<a class="collapse-link">
														<i class="fa fa-chevron-up"></i>
													</a>
												</div>
											</div>
											<div class="ibox-content">
												<div class="row">
													<div class="form-group">
													<label class="col-sm-2 control-label">Método de Pago<span class="text-danger">*</span></label>
													
														<div class="col-sm-10">
															<select class="form-control  input-lg m-b" name="carnet" id="carnetSelect">
															  <option value="">Seleccione</option>										  
															</select>
														</div>
													</div>	
												</div>
											</div>
										</div>
									</div>									
									
									<div class="form-group"><div class="col-sm-2">&nbsp;</div>
										<div class="col-sm-10">
											<div class="btn-group">
												<a href="{{ url('home') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
												<button id="Confirm_Form" class="btn btn-primary btn-sm" type="button"><i class="fa fa-arrow-circle-o-right"></i> Pagar</button>
												<button id="Submit_Form" style="display: none" type="submit"></button>
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

	<!-- Modal -->
	<div id="myModal" class="modal fade" role="dialog">
	  <div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
		  <div class="modal-header">
			<div class="forum-icon">
				<i class="fa fa-credit-card"></i>				
			</div>			
			<h1 class="modal-title">Detalle de GiftCard</h1>
		  </div>
		  <div class="modal-body">
			<div id="d_ced_cmp"></div>
			<div id="d_nom_cmp"></div>
			<div id="d_ced_rep"></div>
			<div id="d_nom_rep"></div>
			<div id="d_monto"></div>
			<div id="d_comision_com"></div>
			<div id="d_comision_cli"></div>
			<div id="d_total"></div>
		  </div>
		  <div class="modal-footer">
		  <button type="button" id="comprar_giftcard" class="btn btn-block btn-primary">Comprar GiftCard</button>
			<button type="button" id="cancelar_giftcard" class="btn btn-block btn-default" data-dismiss="modal">Cancelar</button>
		  </div>
		</div>

	  </div>
	</div>
		
@endsection	
@section('scripts')
<script>
	$(document).ready(function () {
		$('.i-checks').iCheck({
			checkboxClass: 'icheckbox_square-green',
			radioClass: 'iradio_square-green',
		});
	});
</script>
<script type="text/javascript">

	$(document).ready(function(){

		$('input').on('ifChecked', function(event){
		  if(event.target.defaultValue == "Otros"){
			  $("#monto").val("");
			  $('#monto').prop('readonly', false);
		  }
		  else{
			  $("#monto").val("");
			  $('#monto').prop('readonly', true);
		  }
		  
		  CambiarImagen(event.target.defaultValue);
		});
		
		function CambiarImagen($monto)
		{
			@foreach($Imagenes as $i => $element)
				if('{{ $element->monto }}' == $monto)
				{
					$("#img_front_giftcard").attr("src", '{{ asset("img/GiftCard/".$element->nombre_imagen) }}');
					$("#img_front_giftcard").css("display", "block");
					$("#imagen_gift_back").val('{{ $element->nombre_imagen }}');
				}
			@endforeach
		}
		
		$('#monto').numeric(",");	

	});	

	function PrintMessage(Value)
	{
		swal({
			title: "Notificación",
			text: Value,
			allowOutsideClick: false,
			allowEscapeKey: false,
			type: "warning"
			}).then(function(result){
		});
	}
	
	function confirmar_compra(monto)
	{
		var d_ced_cmp;
		var d_nom_cmp;
		var d_ced_rep;
		var d_nom_rep;
		var d_comision;
		var d_comision_com;
		var d_comision_cli;
		var d_total;
		
		if($('#existe_comprador').val() == '1')
		{
			d_ced_cmp = $('#nacionalidad_comprador_e').val() + '-'  + $('#cedula_comprador_e').val();
			d_nom_cmp = $('#first_name_comprador_e').val() + ' ' + $('#last_name_comprador_e').val();
		}
		else{
			d_ced_cmp = $('#nacionalidad_comprador').val() + '-'  + $('#cedula_comprador').val();
			d_nom_cmp = $('#first_name_comprador').val() + $('#last_name_comprador').val();			
		}
		
		if($('#existe_receptor').val() == '1')
		{
			d_ced_rep = $('#nacionalidad_receptor_e').val() + '-'  + $('#cedula_receptor_e').val();
			d_nom_rep = $('#first_name_receptor_e').val() + ' ' + $('#last_name_receptor_e').val();
		}
		else{
			d_ced_rep = $('#nacionalidad_comprador').val() + '-'  + $('#cedula_comprador').val();
			d_nom_rep = $('#first_name_comprador').val() + ' ' + $('#last_name_comprador').val();			
		}
		
		monto = parseFloat(monto);
		d_comision = parseFloat('{{ $gift->tasa_comision }}');
		
		var d_total_comision = ((monto * (d_comision / 100)) + parseFloat('{{ $gift->monto_fijo }}')).toFixed(2);
		
		if('{{ $gift->paga_comision }}' == '2')
		{
			d_comision_com = '0,00';
			d_comision_cli = d_total_comision;
			d_total = (parseFloat(monto) + parseFloat(d_total_comision)).toFixed(2);
		}
		else{
			d_comision_cli = d_total_comision;
			d_comision_com = '0,00';
			d_total = monto;
		}
		
		$("#d_ced_cmp").html('<h2><span class="font-normal">Cédula cliente emisor: </span>' + d_ced_cmp + '</h2>');
		$("#d_nom_cmp").html('<h2><span class="font-normal">Nombre cliente emisor: </span>' + d_nom_cmp + '</h2>');
		$("#d_ced_rep").html('<h2><span class="font-normal">Cédula cliente receptor: </span>' + d_ced_rep + '</h2>');
		$("#d_nom_rep").html('<h2><span class="font-normal">Nombre cliente receptor: </span>' + d_nom_rep + '</h2>');
		$("#d_monto").html('<h2><span class="font-normal">Monto GiftCard: </span>' + monto.toString().replace(".", ",") + '$</h2>');
		$("#d_comision_com").html('<h2><span class="font-normal">Comision comercio: </span>' + d_comision_com.toString().replace(".", ",") + '$</h2>');
		$("#d_comision_cli").html('<h2><span class="font-normal">Comision cliente: </span>' + d_comision_cli.toString().replace(".", ",") + '$</h2>');
		$("#d_total").html('<h2><span class="font-normal">Total a debitar al cliente emisor: </span>' + d_total.toString().replace(".", ",") + '$</h2>');
	}
	
	$("#comprar_giftcard" ).click(function() {
		$('#cancelar_giftcard').attr('disabled', true);
		$('#comprar_giftcard').attr('disabled', true);
		$("#divLoading").show();
		$("#Submit_Form").click();
	});	
	
	$(document).ready(function(){
		$("#Confirm_Form").click(function(){
			
			$("#msgMonto").html("");

			if($("#comprador_buscado").val() == "0")
			{
				PrintMessage("Debe realizar la busqueda del cliente que envía antes de continuar");
				return;				
			}
			else{
				if($("#beneficiario_buscado").val() == "0")
				{
					PrintMessage("Debe realizar la busqueda del beneficiario antes de continuar");
					return;				
				}	
			}
			
			if($("#formGiftCard").valid())
			{

				var monto_minimo = '{{ $gift->monto_minimo }}'.replace(".", ",");
				var monto_maximo = '{{ $MontoMaximo }}'.replace(".", ",");
				var monto = '';
				
				//Validar que haya seleccionado un monto
				if($("#monto").val() == "")
				{
					var radioValue = $("input[name='monto_radio']:checked").val();
					
					if(typeof radioValue === 'undefined')
					{
						$("#msgMonto").html("Debe indicar el monto de la GiftCard");
						PrintMessage("Debe indicar el monto de la GiftCard");
						return;
					}
					else{
						if(radioValue == "Otros")
						{
							$("#msgMonto").html("Debe indicar el monto de la GiftCard");
							PrintMessage("Debe indicar el monto de la GiftCard");
							return;							
						}
						else{
							monto = radioValue + ',00';
							$("#monto_real").val(monto);							
						}
					}	
				}
				else{
					monto = $("#monto").val();
					$("#monto_real").val(monto);
				}

				//Valores del Producto
				var ProductoMontoTipo = $("#carnetSelect").val().split("/");
				$("#carnet_real").val(ProductoMontoTipo[0]);
				
				if(ProductoMontoTipo[2] == "Interno")
				{					
					var disponible = ProductoMontoTipo[1];
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
						  PrintMessage("El monto mínimo permitido es de " + monto_minimo + '{{ $gift->mon_simbolo }}');
						  return;
					  }
					  
						if(monto > monto_maximo){						
							$("#msgMonto").html("El monto máximo permitido es de " + monto_maximo + '{{ $gift->mon_simbolo }}');
							PrintMessage("El monto máximo permitido es de " + monto_maximo + '{{ $gift->mon_simbolo }}');
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


								var pat = /venta/;

								if(pat.test(url) == true){
									url=String(url);

									url=url.replace("/gift/venta",'');
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
							//$("#divLoading").show();
							//$("#Submit_Form").click();
							confirmar_compra(monto);
							$('#myModal').modal('show'); 
						}
			
				}
				else{
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
						PrintMessage("El monto mínimo permitido es de " + monto_minimo + '{{ $gift->mon_simbolo }}');
					return;
					}
					
					if(monto > monto_maximo){						
						$("#msgMonto").html("El monto máximo permitido es de " + monto_maximo + '{{ $gift->mon_simbolo }}');
						PrintMessage("El monto máximo permitido es de " + monto_maximo + '{{ $gift->mon_simbolo }}');
					return;
					}					
					
					confirmar_compra(monto);
					$('#myModal').modal('show'); 					
					//$("#divLoading").show();
					//$("#Submit_Form").click();
					
				}
			}
			//$("#Submit_Form").click();
		});
		
		
	  $("#buttonBuscarComprador").click(function(){
		consultaDatosComprador();
	  });
	  
	  $("#buttonBuscarReceptor").click(function(){
		consultaDatosReceptor();
	  });

		function LimpiarFormularioReceptor()
		{
		  $("#first_name_receptor").val("");
		  $("#last_name_receptor").val("");
		  $("#email_receptor").val("");
		  $("#cod_tel_receptor").val("");
		  $("#num_tel_receptor").val("");
		  $("#BuscarReceptor").css("display", "block");
		  $("#ExisteReceptor").css("display", "none");	
		  $("#NoExisteReceptor").css("display", "none");		  
		}	

	  $("#buttonCancelarReceptor").click(function(){
		  LimpiarFormularioReceptor();
	  });

	  $("#buttonCancelarReceptor_e").click(function(){
		  LimpiarFormularioReceptor();
	  });

		function LimpiarFormularioComprador()
		{
		  $("#first_name_comprador").val("");
		  $("#last_name_comprador").val("");
		  $("#email_comprador").val("");
		  $("#cod_tel_comprador").val("");
		  $("#num_tel_comprador").val("");
		  $("#BuscarComprador").css("display", "block");
		  $("#ExisteComprador").css("display", "none");	
		  $("#NoExisteComprador").css("display", "none");
		  $("#carnetSelect").empty();
		  $("#carnetSelect").append('<option value="">Seleccione</option>');
		}	

	  $("#buttonCancelarComprador").click(function(){
		  LimpiarFormularioComprador();
	  });

	  $("#buttonCancelarComprador_e").click(function(){
		  LimpiarFormularioComprador();
	  });	  
		
	  $("#Confirm_SioExisteClienteForm").click(function(){
		$("#Submit_SiExisteClienteForm").click();
	  });
		$('#nacionalidad_comprador_e').bind('mousedown', function (event) { 
			event.preventDefault();
			event.stopImmediatePropagation();
		});
		
		$('#nacionalidad_comprador').bind('mousedown', function (event) { 
			event.preventDefault();
			event.stopImmediatePropagation();
		});	 
		
		$('#nacionalidad_receptor_e').bind('mousedown', function (event) { 
			event.preventDefault();
			event.stopImmediatePropagation();
		});	
		$('#nacionalidad_receptor').bind('mousedown', function (event) { 
			event.preventDefault();
			event.stopImmediatePropagation();
		});			

		$('#fecha_entrega').datepicker({
			ignoreReadonly: true,
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			format: 'dd/mm/yyyy',
			autoclose: true
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
  
  function consultaDatosComprador()
  {	
	var cedula = $("#cedula_buscar").val();
	var nacionalidad = $("#nacionalidad_buscar").val();
	$("#msgCedula_Comprador").html("");
	
	if(cedula == "")
	{
		$("#msgCedula_Comprador").html("Ingrese el número de cédula");
		return;
	}
	
	if(nacionalidad == "")
	{
		$("#msgCedula_Comprador").html("Ingrese el tipo de documento");
		return;
	}	
	
	var url = window.location;

	var pat = /venta/;

	if(pat.test(url) == true){
		url=String(url);

		url=url.replace("/venta/{{ $gift->cod_emisor }}",'');
		url=url.replace("/venta/{{ $gift->cod_emisor }}",'');
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
		  $("#BuscarComprador_cedula_real").css("display", "block");
		  
			for(var i=0; i < respuesta[0].productos.length; i++){
				$("#carnetSelect").append('<option value="' + respuesta[0].productos[i].carnet + '/' + respuesta[0].productos[i].disponible + '/' + respuesta[0].productos[i].tipo_carnet + '">' + respuesta[0].productos[i].carnet.substr(-20,4) +' XXXX XXXX '+ respuesta[0].productos[i].carnet.substr(-4) + '</option>');
			}
			//$("#carnetSelect").append('<option value="Stripe/0,00/Externo">POS INTERNACIONAL</option>');
			$("#carnetSelect").append('<option value="Otros/0,00/Externo">OTROS</option>');
			
			$("#cedula_comprador").val("00000010");
			$("#first_name_comprador").val("Default");
			$("#last_name_comprador").val("Default");
			$("#email_comprador").val("defaultmail@default.com");
			$("#cod_tel_comprador").val("58412");
			$("#num_tel_comprador").val("0003300");
			$("#existe_comprador").val("1");
			$("#comprador_buscado").val("1");
			
		  
							 
	  }else{
		  $("#nacionalidad_comprador").val(nacionalidad);
		  $("#cedula_comprador").val(cedula);
		  $('#cedula_comprador').prop('readonly', true);
		  $('#nacionalidad_comprador').attr('readonly', true);
		  $('#Confirm_NoExisteClienteForm').attr('disabled', false);
		  		 
		  
		  $("#NoExisteComprador").css("display", "block");
		  $("#ExisteComprador").css("display", "none");
		  $("#BuscarComprador").css("display", "none");

		  //$("#carnetSelect").append('<option value="Stripe/0,0/Externo">POS INTERNACIONAL</option>');
		  $("#carnetSelect").append('<option value="Otros/0,0/Externo">OTROS</option>');
		  $("#existe_comprador").val("0");
		  $("#comprador_buscado").val("1");
	  }

	  $("#divLoading").hide();
	});			  
  }
  
  function consultaDatosReceptor()
  {	
	var cedula = $("#cedula_receptor_buscar").val();
	var nacionalidad = $("#nacionalidad_receptor_buscar").val();
	
	if(cedula == "")
	{
		$("#msgCedula_Receptor").html("Ingrese el número de cédula");
		return;
	}
	
	if(nacionalidad == "")
	{
		$("#msgCedula_Receptor").html("Ingrese el número de cédula");
		return;
	}

	var url = window.location;

	var pat = /venta/;

	if(pat.test(url) == true){
		url=String(url);

		url=url.replace("/venta/{{ $gift->cod_emisor }}",'');
		url=url.replace("/venta/{{ $gift->cod_emisor }}",'');
	}	
	
	$("#divLoading").show();
	
	$.get(url + "/consultaDatos/"+ cedula+ '/' + nacionalidad, function(respuesta){

	  if (!respuesta['fallido'] && respuesta.length) {	  
		  
		  $("#nacionalidad_receptor_e").val(respuesta[0].nacionalidad);
		  $("#cedula_receptor_e").val(respuesta[0].cedula);
		  $("#first_name_receptor_e").val(respuesta[0].first_name);
		  $("#last_name_receptor_e").val(respuesta[0].last_name);
		  $("#email_receptor_e").val(respuesta[0].email);
		  $("#cod_tel_receptor_e").val(respuesta[0].cod_tel);
		  $("#num_tel_receptor_e").val(respuesta[0].num_tel);
		  		  
		  $('#cedula_receptor_e').prop('readonly', true);
		  $('#first_name_receptor_e').prop('readonly', true);
		  $('#last_name_receptor_e').prop('readonly', true);
		  $('#email_receptor_e').prop('readonly', true);
		  $('#num_tel_receptor_e').prop('readonly', true);
		  $('#cod_tel_receptor_e').attr('disabled',true);
		  $('#nacionalidad_receptor_e').attr('readonly', true);
		  $('#Confirm_SiExisteClienteForm').attr('disabled', false);
		  		  	  
		  $("#NoExisteReceptor").css("display", "none");
		  $("#ExisteReceptor").css("display", "block");
		  $("#BuscarReceptor").css("display", "none");
		  $("#BuscarReceptor_Cedula_Real").css("display", "block");
		  
			$("#cedula_receptor").val("00000010");
			$("#first_name_receptor").val("Default");
			$("#last_name_receptor").val("Default");
			$("#email_receptor").val("defaultmail@default.com");
			$("#cod_tel_receptor").val("58412");
			$("#num_tel_receptor").val("0003300");	

			$("#existe_receptor").val("1");			
			$("#beneficiario_buscado").val("1");
			  
		  
							 
	  }else{
		  $("#nacionalidad_receptor").val(nacionalidad);
		  $("#cedula_receptor").val(cedula);
		  $('#cedula_receptor').prop('readonly', true);
		  $('#nacionalidad_receptor').attr('readonly', true);
		  $('#Confirm_NoExisteClienteForm').attr('disabled', false);
		  
		  
		  $("#NoExisteReceptor").css("display", "block");
		  $("#ExisteReceptor").css("display", "none");
		  $("#BuscarReceptor").css("display", "none");
			$("#BuscarReceptor_Cedula_Real").css("display", "block");		  
			$("#existe_receptor").val("0");
			$("#beneficiario_buscado").val("1");			
	  }

	  $("#divLoading").hide();
	});			  
  }  
</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>

{!! JsValidator::formRequest('App\Http\Requests\VentaGiftCardCompleteRequest') !!}
@endsection	