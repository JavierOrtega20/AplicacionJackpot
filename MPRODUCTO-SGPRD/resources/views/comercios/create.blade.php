@extends('layouts.app')
@section('titulo', 'Comercios')



@section('contenido')

<form method="POST" action="{{ route('comercios.store') }}"  enctype="multipart/form-data" class="form-horizontal" id="form-comercios">
<!--{!! Form::open(['method' => 'POST','route' => ['comercios.store','class'=>'form-horizontal']]) !!}-->
                              {{ csrf_field() }}

 <div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-diamond"></i>Comercios</h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>Comercios
              </li>
              <li class="active">
              <strong>Crear nuevo</strong>
              </li>
            </ol>
            </div>
            <div class="col-lg-4">
              <div class="title-action">
                <a href="{{route('comercios.index')}}" class="btn btn-white" ><i class="fa fa-times"></i> Cancelar </a>
				@if($ComercioPrincipal == 0)
					<button type="button" onclick="PreguntarSiRequiereSucursalYEnviar()" class="btn btn-primary" id="form-validation" ><span class="btn-label">
								<i class="fa fa-check"></i>
							</span>Crear</button>
				  </div>
				@else
                <button type="submit" class="btn btn-primary" id="form-validation" ><span class="btn-label">
                            <i class="fa fa-check"></i>
                        </span>Crear</button>
              </div>
				@endif
          </div>

        </div>

        <div class="wrapper wrapper-content animated fadeInRight ecommerce">
                   @include('flash::message')

            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Crear nuevo comercio</h5>
                        </div>
                        <div class="ibox-content">
                            
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Rif <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
									<div class="row">
									  <div class="col-md-2">
										{{ Form::select('letrarif', [
										   'J' => 'J',
										   'V' => 'V',
										   'E' => 'E',
										   'R' => 'R',
										   'G' => 'G',
										 ], null, ['class' => 'form-control input-lg m-b', 'placeholder'=>'Seleccione','id'=> 'letrarif']
										) }}  
									  </div>
									  <div class="col-md-10">
										  {!! Form::text('rif', null, array('placeholder' => 'Rif','class' => 'form-control input-lg m-b','id'=> 'rif','maxlength' =>'12','onkeypress'=> 'return justNumbers(event)')) !!}
										   <!--{!! Form::text('rif', null, array('placeholder' => 'Rif','class' => 'form-control input-lg m-b','maxlength' =>'12','onchange'=>'cerosIzquierda(this)','onkeypress'=> 'return justNumbers(event)')) !!}-->  
									  </div>
									</div>								  
                                    <!--,'onchange'=>'cerosIzquierda(this)'-->				                                    
                                  </div>
                              </div> 
							  
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    <!--<input type="text" placeholder="descripcion" name="descripcion" id="descripcion" class="form-control input-lg m-b">-->
                                     {!! Form::text('descripcion', null, array('placeholder' => 'Nombre','id'=> 'descripcion','class' => 'form-control input-lg m-b','maxlength' =>'50')) !!}
                                  </div>
                              </div>

                              <div class="hr-line-dashed"></div>  
                              <div class="form-group"><label class="col-sm-2 control-label">Razón Social <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    {!! Form::text('razon_social', null, array('placeholder' => 'Razón Social','id'=> 'razon_social','class' => 'form-control input-lg m-b','maxlength' =>'50')) !!}
                                    <!--<input type="text" placeholder="razon_social" name="razon_social" id="razon_social" class="form-control input-lg m-b">-->
                                  </div>
                              </div>  
							  {!! Form::hidden('es_sucursal',null,array('id'=>'es_sucursal')) !!}
							  {!! Form::hidden('ComercioPrincipal',null,array('id'=>'ComercioPrincipal')) !!}
							  {!! Form::hidden('irsucursales','false',array('id'=>'irsucursales')) !!}

							  <div style="display: none;" id="divSucursal">
                              <div class="hr-line-dashed"></div>
								  <div class="form-group"><label class="col-sm-2 control-label">Sucursal <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
										{!! Form::text('nombre_sucursal', 'N\A', array('placeholder' => 'Sucursal','id'=> 'nombre_sucursal','class' => 'form-control input-lg m-b','maxlength' =>'50')) !!}
										<!--<input type="text" placeholder="razon_social" name="razon_social" id="razon_social" class="form-control input-lg m-b">-->
									  </div>
                                  </div>
                              </div>
							  <div class="hr-line-dashed"></div>
							  <div class="form-group"><label class="col-sm-2 control-label">Estatus <span class="text-danger">*</span></label>
								  <div class="col-sm-10">
										{{ Form::select('estatus', $lcomercio_estatus, null, ['class' => 'form-control input-lg m-b', 'placeholder'=>'Seleccione','id'=> 'estatus']) }}
								  </div>
							  </div>
							<div style="display: none;" id="divEstatusMotivo">			  
							  <div class="hr-line-dashed"></div>
							  <div class="form-group"><label class="col-sm-2 control-label">Motivo <span class="text-danger">*</span></label>
								  <div class="col-sm-10">
										{{ Form::select('estatus_motivo', $lcomercio_motivo_estatus, null, ['class' => 'form-control input-lg m-b', 'placeholder'=>'Seleccione','id'=> 'estatus_motivo']) }}
								  </div>
							  </div>
							</div>								  
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Categoría <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
										{{ Form::select('fk_id_categoria', $lcategorias, null, ['class' => 'form-control input-lg m-b', 'placeholder'=>'Seleccione','id'=> 'fk_id_categoria']) }}
                                  </div>
                              </div>	
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Sub-Categoría <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
										{{ Form::select('fk_id_subcategoria', $lsubcategorias, null, ['class' => 'form-control input-lg m-b', 'placeholder'=>'Seleccione','id'=> 'fk_id_subcategoria']) }}
                                  </div>
                              </div>								  
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Calle/Av</label>
                                  <div class="col-sm-10">
                                    {!! Form::text('calle_av', null, array('placeholder' => 'Calle/Av','class' => 'form-control input-lg m-b','maxlength' =>'50')) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Casa/Edificio/Torre</label>
                                  <div class="col-sm-10">
                                    {!! Form::text('casa_edif_torre', null, array('placeholder' => 'Casa/Edificio/Torre','class' => 'form-control input-lg m-b','maxlength' =>'50')) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Local/Oficina</label>
                                  <div class="col-sm-10">
                                    {!! Form::text('local_oficina', null, array('placeholder' => 'Local/Oficina','class' => 'form-control input-lg m-b','maxlength' =>'50')) !!}
                                  </div>
                              </div>	
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Urb./Sector</label>
                                  <div class="col-sm-10">
                                    {!! Form::text('urb_sector', null, array('placeholder' => 'Urb./Sector','class' => 'form-control input-lg m-b','maxlength' =>'50')) !!}
                                  </div>
                    </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Ciudad <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    {!! Form::text('ciudad', null, array('placeholder' => 'Ciudad','class' => 'form-control input-lg m-b','maxlength' =>'50')) !!}
                    </div>
                  </div>                  
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Estado <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
										{{ Form::select('estado', $lestados, null, ['class' => 'form-control input-lg m-b', 'placeholder'=>'Seleccione','id'=> 'estado']) }}
                                  </div>
                              </div> 

                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Dirección <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
  
                                    {!! Form::textarea('direccion', null, array('placeholder' => 'Dirección','class' => 'form-control input-lg m-b','rows'=>6)) !!}
                                    <!--<textarea placeholder="direccion" name="direccion" id="direccion" class="form-control input-lg m-b"></textarea>-->
                                    
                                  </div>
                              </div>

                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Teléfono 1 <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    {!! Form::text('telefono1', null, array('placeholder' => 'Teléfono 1','class' => 'form-control input-lg m-b','onkeypress'=>'return justNumbers(event);','maxlength' =>'15')) !!}
                                    <!--<input type="text" placeholder="telefono1" name="telefono1" id="telefono1" class="form-control input-lg m-b">-->
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label">Teléfono 2</label>

                                  <div class="col-sm-10">
                                    {!! Form::text('telefono2', null, array('placeholder' => 'Teléfono 2','class' => 'form-control input-lg m-b','onkeypress'=>'return justNumbers(event);','maxlength' =>'15')) !!}
                                    <!--<input type="text" placeholder="telefono2" name="telefono2" id="telefono2" class="form-control input-lg m-b">-->
                                  </div>
                              </div>
                              
                              <div class="hr-line-dashed"></div>
  
                              <div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    {!! Form::text('email', null, array('placeholder' => 'Correo Electrónico','class' => 'form-control input-lg m-b','maxlength' =>'50')) !!}
                                    <!--<input type="email" placeholder="email" name="email" id="email" class="form-control input-lg m-b">-->
                                  </div>
                              </div>
                              <!--<div class="hr-line-dashed"></div>
                              <div class="form-group">
                                  <label class="col-sm-2 control-label">
                                    Código Afiliación Credicard
                                    <span class="text-danger">*</span>
                                  </label>
                                  <div class="col-sm-10">
                                    {!! Form::text('codigo_afi_come', null, array('placeholder' => 'Código de Afiliación del Comercio','class' => 'form-control input-lg m-b','maxlength' =>'25')) !!}
                                  </div>
                              </div>-->
                              <div class="form-group">
                                  <label class="col-sm-2 control-label">
                                    Código Afiliación Banplus Pay
                                    <span class="text-danger">*</span>
                                  </label>
                                  <div class="col-sm-10">
                                    {!! Form::text('codigo_afi_real', $AfiliacionPP, array('readonly' => 'readonly','class' => 'form-control input-lg m-b','maxlength' =>'25')) !!}
                                  </div>
                              </div>						  
                                                        
                              
                              <div class="hr-line-dashed"></div>
                              <!--<div class="form-group">
                                  <label class="col-sm-2 control-label">
                                    Número de Cuenta
                                    <span class="text-danger">*</span>
                                  </label>
                                  <div class="col-sm-8">
                                    {!! Form::text('num_cuenta',null,array('placeholder'=>'Número de Cuenta','class'=>'form-control input-lg m-b','maxlength'=>'20','onkeyup'=>'formatNumCuenta(this)','onchange'=>'formatNumCuenta(this)')) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>-->
                              <div id="formPpal" class="form-group" >
                                  <label class="col-sm-2  control-label ">
                                     Número Cuenta Principal en Bolívares(Consumo)
                                  </label>
                                  <div class="col-sm-10">
                                        {!! Form::text('num_cta_princ', null, array('placeholder' => 'Número de Cuenta Principal Bolívares','class' => 'form-control input-lg m-b','maxlength' =>'20','id'=>'num_cta','onkeyup'=>'formatNumCuenta(this)','onchange'=>'formatNumCuenta(this)','onblur'=>'habilitarCtaPal()')) !!}
                                        <div id="msgCtaPal" class="text-danger"></div>
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group">
                                  <label class="col-sm-2 control-label">
                                    Tasa de Comisión Bs
                                    <span class="text-danger">*</span>
                                  </label>
                                  <div class="col-sm-8">
                                    {!! Form::text('tasa_cobro_comer', null, array('id' => 'tasa_cobro_comer','placeholder' => 'Tasa de cobro de comisión Bs','class' => 'form-control input-lg m-b','maxlength' =>'5')) !!}
                                    <!--<input type="text" name="tasa_cobro_comer" id="tasa_cobro_comer" placeholder="Tasa de cobro de comisión" class="form-control input-lg m-b" maxlength="5">-->

                                  </div>
                                  <span style="font-size: 30px;font-weight: bold;">%</span>
                              </div>    


                              <!--CUENTA EN DOLARES-->
                              <div id="formPpal2" class="form-group" >
                                  <label class="col-sm-2  control-label ">
                                     Número Cuenta Principal en Dólares(Consumo)
                                  </label>
                                  <div class="col-sm-10">
                                        {!! Form::text('num_cta_princ_dolar', null, array('placeholder' => 'Número de Cuenta Principal Dólares','class' => 'form-control input-lg m-b','maxlength' =>'20','id'=>'num_cta_dolar','onkeyup'=>'formatNumCuenta(this)','onchange'=>'formatNumCuenta(this)','onblur'=>'habilitarCtaPalDolar()')) !!}
                                        <div id="msgCtaPalDolar" class="text-danger"></div>
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group" id="cobro_dolar" style="display: none">
                                  <label class="col-sm-2 control-label">
                                    Tasa de Comisión $                                    
                                  </label>
                                  <div class="col-sm-8">
                                    {!! Form::text('', null, array('id' => 'tasa_cobro_comer_dolar','placeholder' => 'Tasa de cobro de comisión $','class' => 'form-control input-lg m-b','maxlength' =>'5')) !!}
                                    <!--<input type="text" name="tasa_cobro_comer" id="tasa_cobro_comer" placeholder="Tasa de cobro de comisión" class="form-control input-lg m-b" maxlength="5">-->

                                  </div>
                                  <span style="font-size: 30px;font-weight: bold;">%</span>
                              </div>
                              <!--FIN DE LA CUENTA EN DOLARES -->


                              <!--FIN DE LA CUENTA EN EUROS -->
                              <div id="formPpal3" class="form-group" >
                                  <label class="col-sm-2  control-label ">
                                     Número Cuenta Principal en Euros(Consumo)
                                  </label>
                                  <div class="col-sm-10">
                                        {!! Form::text('num_cta_princ_euro', null, array('placeholder' => 'Número de Cuenta Principal Euros','class' => 'form-control input-lg m-b','maxlength' =>'20','id'=>'num_cta_euro','onkeyup'=>'formatNumCuenta(this)','onchange'=>'formatNumCuenta(this)','onblur'=>'habilitarCtaPalEuro()')) !!}
                                        <div id="msgCtaPalEuros" class="text-danger"></div>
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              <div class="form-group" id="cobro_euro" style="display: none">
                                  <label class="col-sm-2 control-label">
                                    Tasa de Comisión €
                                    
                                  </label>
                                  <div class="col-sm-8">
                                    {!! Form::text('', null, array('id' => 'tasa_cobro_comer_euro','placeholder' => 'Tasa de cobro de comisión €','class' => 'form-control input-lg m-b','maxlength' =>'5')) !!}
                                    <!--<input type="text" name="tasa_cobro_comer" id="tasa_cobro_comer" placeholder="Tasa de cobro de comisión" class="form-control input-lg m-b" maxlength="5">-->

                                  </div>
                                  <span style="font-size: 30px;font-weight: bold;">%</span>
                              </div>							  
                              <!--FIN DE LA CUENTA EN EUROS -->
							  <!--
							  <div class="form-group">
								<label class="col-sm-2 control-label">Activar Stripe</label>
								<div class="col-sm-10">
									{{ Form::checkbox('status_stripe','0',false,array('onclick'=>'verifStripe()','id'=>'status_stripe')) }}
								</div>
							  </div>
							  <div class="form-group" id="stripe" style="display: none;">
								<label class="col-sm-2  control-label ">
								  Tasa de Comisión Stripe
								</label>
								<div class="col-sm-8">
								  {!! Form::text('tasa_cobro_comer_stripe', null, array('placeholder' => 'Tasa de Comisión Stripe','required' => 'required','class' => 'form-control input-lg m-b','maxlength' =>'5','id'=>'tasa_cobro_comer_stripe')) !!}
								  <div id="msgCtaPalEuros" class="text-danger"></div>
								</div>
								<span style="font-size: 30px;font-weight: bold;">%</span>
							  </div>							  						  

                              <div class="hr-line-dashed"></div>-->
                              <div class="form-group">
                                  <label class="col-sm-2  control-label">Activar Propina</label>
                                  <div class="col-sm-10">
                                    {{ Form::checkbox('propina_act','1',false,array('onclick'=>'verifCheck()','id'=>'propina_act')) }}
                                  </div>
                              </div>
                              
                              <div class="dinamico" id="ctas" style="display: none;">
                                  
                                  <div id="formSec" class="form-group" >
                                      <label class="col-sm-2  control-label">
                                        Número Cuenta Secundaria en Bolívares(Propina)
                                      </label>
                                      <div class="col-sm-10">
                                        {!! Form::text('num_cta_secu', null, array('id'=>'num_sec','placeholder' => 'Número de Cuenta Secundaria en Bolívares','class' => 'form-control input-lg m-b','maxlength' =>'20','onkeyup'=>'formatNumCuenta(this)','onchange'=>'formatNumCuenta(this)')) !!}
                                        <div id="msgCtaSec" class="text-danger"></div>
                                      </div>
                                  </div>
                                  <div id="formSec2" class="form-group" >
                                      <label class="col-sm-2  control-label">
                                        Número Cuenta Secundaria en Dólares(Propina)
                                      </label>
                                      <div class="col-sm-10">
                                        {!! Form::text('num_cta_secu_dolar', null, array('id'=>'num_sec_dolar','placeholder' => 'Número de Cuenta Secundaria en Dólares','class' => 'form-control input-lg m-b','maxlength' =>'20','onkeyup'=>'formatNumCuenta(this)','onchange'=>'formatNumCuenta(this)')) !!}
                                        <div id="msgCtaSec" class="text-danger"></div>
                                      </div>
                                  </div>
                                  <div id="formSec3" class="form-group" >
                                      <label class="col-sm-2  control-label">
                                        Número Cuenta Secundaria en Euros(Propina)
                                      </label>
                                      <div class="col-sm-10">
                                        {!! Form::text('num_cta_secu_euro', null, array('id'=>'num_sec_euro','placeholder' => 'Número de Cuenta Secundaria en Euros','class' => 'form-control input-lg m-b','maxlength' =>'20','onkeyup'=>'formatNumCuenta(this)','onchange'=>'formatNumCuenta(this)')) !!}
                                        <div id="msgCtaSec" class="text-danger"></div>
                                      </div>
                                  </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                              <div class="form-group">
                                  <div >
                                      <div class="title-action">
                                        <a href="{{route('comercios.index')}}" class="btn btn-white" ><i class="fa fa-times"></i> Cancelar </a>
										@if($ComercioPrincipal == 0)
											<button type="button" onclick="PreguntarSiRequiereSucursalYEnviar()" class="btn btn-primary" id="form-validation1" ><span class="btn-label">
														<i class="fa fa-check"></i>
													</span>Crear</button>
										  </div>
										@else
                                        <button type="submit" class="btn btn-primary" id="form-validation1" ><span class="btn-label">
                                                    <i class="fa fa-check"></i>
                                                </span>Crear</button>
                                      </div>
										@endif
                                  </div>
                              </div>
                              <!--<div class="hr-line-dashed"></div>
                              <div class="dinamico">
                                <div class="prop" id="prop" style="display:none;">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-10">
                                          5%  {{ Form::radio('taza_propina', 'value',false) }}
                                          10% {{ Form::radio('taza_propina', 'value',false) }}
                                          15% {{ Form::radio('taza_propina', 'value',false) }}
                                          &nbsp;&nbsp;&nbsp;
                                          <label lass="col-sm-2 control-label">
                                          {!! Form::text('monto_propina', null, array('placeholder' => 'Monto Propina','class' => 'form-control input-lg s-a','id'=>'monto_propina','maxlength' =>'25','style'=>'width:300px;','onkeyup'=>'format(this)','onchange'=>'format(this)')) !!}
                                          </label>
                                          &nbsp;&nbsp; 
                                          <label id="msg-formato">Bs</label>
                                        </div>
                                    </div>
                              </div>
                            </div>-->

                              <input type="hidden" name="banco" id="banco" value="{{$bancos[0]->id}}" >
                              <!--<div class="hr-line-dashed"></div>  

                              <div class="form-group"><label class="col-sm-2 control-label">Bancos <span class="text-danger">*</span></label>      
                                  <div class="col-sm-10">
                                        <select class="form-control input-lg m-b" name="banco" id="banco" >
                                            <option value="">Seleccione Bancos</option>
                                                @foreach($bancos as $banco)
                                                  <option value="{{ $banco->id }}">{{ $banco->descripcion }}</option>
                                                 @endforeach
                                        </select>
                                  </div>        
                              </div>-->
                    </div>
                </div>
            </div>

        </div>
      </div>
     </form>
      <!--{!! Form::close() !!}-->
@stop


@section('scripts')
<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\ComercioRequest') !!}

// <script type="text/javascript">
  // $('select').select2();
// </script>

<

<script type="text/javascript">
function PreguntarSiRequiereSucursalYEnviar()
{
	if($("#form-comercios").valid())
	{
		swal({
		  title: "Notificación",
		  text: "¿Desea crear sucursales a este nuevo comercio?",		  
		  allowOutsideClick: false,
		  allowEscapeKey: false,
		  showCancelButton: true,
		  focusCancel: true,
		  type: "question",
		  confirmButtonText: 'Si',
		  cancelButtonText: 'No',
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#2EB540',		  
		}).then(function(result){
		  if(result.value){
			  $("#irsucursales").val("true");
			  $("#form-comercios").submit();	
			}
			else{
			  $("#irsucursales").val("false");
			  $("#form-comercios").submit();	
			}
		  })		  		 			
	}
}

$("#tasa_cobro_comer_stripe").on({
    "focus": function (event) {
        $(event.target).select();

    },

    "keyup": function (event) {
        $(event.target).val(function (index, value ) {
            return value.replace(/\D/g, "")
                        .replace(/([0-9])([0-9]{2})$/, '$1,$2')
                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
      }
});

$( document ).ready(function() {
	
	consultaDatos({{ $ComercioPrincipal }});
	
	//Motivo Estatus
	ConstruidEstatusMotivo($("#estatus option:selected").text(),"true");
});

$('#fk_id_categoria').bind('mousedown', function (event) { 
  if($("#es_sucursal").val() == 'true')
  {
	event.preventDefault();
	event.stopImmediatePropagation();
  }
});

$('#letrarif').bind('mousedown', function (event) { 
  if($("#es_sucursal").val() == 'true')
  {
	event.preventDefault();
	event.stopImmediatePropagation();
  }
});

$('#fk_id_subcategoria').bind('mousedown', function (event) { 
  if($("#es_sucursal").val() == 'true')
  {
	event.preventDefault();
	event.stopImmediatePropagation();
  }
});

function ConstruidEstatusMotivo(value,load)
{

	//Motivo Estatus
	if(value == "Desafiliado" || value == "Suspendido")
	{
		if(load == "false")
		{
			var options = '<option value>Seleccione</option>';
			
			$('#estatus_motivo > option').each(function() {
				if($(this).text() != "Seleccione")
				{
					options += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
				}			
			});
			
			document.querySelector("#estatus_motivo").innerHTML = "";		
			
			$('#estatus_motivo').html(options);			
		}
		
		$("#divEstatusMotivo").show();
	}
	else
	{
		var options = '<option value="0">Seleccione</option>';
		
		$('#estatus_motivo > option').each(function() {
			if($(this).text() != "Seleccione")
			{
				options += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
			}			
		});
		
		document.querySelector("#estatus_motivo").innerHTML = "";		
		
		$('#estatus_motivo').html(options);
		
		$("#divEstatusMotivo").hide();
	}	
}

$("#estatus"). change(function(){
	ConstruidEstatusMotivo($("#estatus option:selected").text(),"false");
});

function GetSubCategoria(Categoria,SetSubCategoria = "")
{
	var url = window.location;

	var pat = /create/;

	if(pat.test(url) == true){
		url=String(url);
		url=url.replace("/0/create",'');
		url=url.replace("/{{ $ComercioPrincipal }}/create",'');
	}
	
	$.ajax({
			data:'',
			url:url+'/consultaSubcategoria/'+Categoria,
			method:'GET',
			cache:false,
			processData:false,
			contentType: false,
		   success: function (data) {
			   document.querySelector("#fk_id_subcategoria").innerHTML = "";
			   
				var options = '<option value="">Seleccione</option>';
				data.forEach(function(subcategoria){
					options += '<option value="' + subcategoria.id + '">' + subcategoria.Nombre + '</option>'
				});
				$('#fk_id_subcategoria').html(options);	

				if(SetSubCategoria != "")
				{
					$("#fk_id_subcategoria").val(SetSubCategoria);
				}	
		   }
	})		
}

$('#fk_id_categoria').on('change', function() {
	
if(this.value != "")
{
	GetSubCategoria(this.value);
}
else{
	document.querySelector("#fk_id_subcategoria").innerHTML = "";
	var options = '<option value="">Seleccione</option>';
	$('#fk_id_subcategoria').html(options);	
}	
});

function ResetControlesSucursal()
{
	$("#descripcion").val("");
	$("#razon_social").val("");
	$("#nombre_sucursal").val("N\A");
	$("#descripcion").attr("readonly", false);
	$("#razon_social").attr("readonly", false);	
	$("#es_sucursal").val('false');
}


function consultaDatos(ComercioPrincipal){

	var url = window.location;


	var pat = /create/;

	if(pat.test(url) == true){
		url=String(url);

		url=url.replace("/create",'');
		url=url.replace("/create",'');

	}

	var LetraRif = $("#letrarif" ).val();
	
	if(ComercioPrincipal != '0')
	{
		var rif = ComercioPrincipal;

		$.ajax({
				data:'',
				url:url,
				method:'GET',
				cache:false,
				processData:false,
				contentType: false,
			   success: function (data) {
				   
				   if(data['data'].length > 0)
				   {
					   $("#descripcion").val(data['data'][0].descripcion);
					   $("#razon_social").val(data['data'][0].razon_social);
					   $("#letrarif").val(data['data'][0].rif.substr(0,1));							   
					   $("#rif").val(data['data'][0].rif.substr(1,15));					   
					   $("#fk_id_categoria").val(data['data'][0].fk_id_categoria);
					   $("#ComercioPrincipal").val(ComercioPrincipal);
					   
					   
					   GetSubCategoria(data['data'][0].fk_id_categoria,data['data'][0].fk_id_subcategoria);
					   
					   $("#rif").attr("readonly", true);
					   $("#descripcion").attr("readonly", true);
					   $("#razon_social").attr("readonly", true);
					   
					   $("#nombre_sucursal").val("");
					   $("#divSucursal").show();
				   
					   $("#es_sucursal").val('true');					   				   
				   }
				   else{
					   ResetControlesSucursal();
				   }											
			   }
		})		
	}
	else{
		ResetControlesSucursal();	
	}	
}



function cerosIzquierda(num){
    var letra='';
    var numRif = '';

    /*if(num.value.length >= 1){*/
        for(var i = 0; i < num.value.length; i++){
            /*if(i==0){
                letra = num.value[0];
            }else{*/
                numRif += num.value[i];
            //}
        }
    /*}else{
        letra = num.value[0];
    }*/

   
    if(!numRif){
        num.value = letra+'000000000';
    }else if(numRif.length == 1){
        num.value = letra+'00000000'+numRif;
    }else if(numRif.length == 2){
        num.value = letra+'0000000'+numRif;
    }else if(numRif.length == 3){
        num.value = letra+'000000'+numRif;
    }else if(numRif.length == 4){
        num.value = letra+'00000'+numRif;
    }else if(numRif.length == 5){
        num.value = letra+'0000'+numRif;
    }else if(numRif.length == 6){
        num.value = letra+'000'+numRif;
    }else if(numRif.length == 7){
        num.value = letra+'00'+numRif;
    }else if(numRif.length == 8){
        num.value = letra+'0'+numRif;
    }
}

$("#tasa_cobro_comer").on({
    "focus": function (event) {
        $(event.target).select();
    },
    "keyup": function (event) {
        $(event.target).val(function (index, value ) {
            return value.replace(/\D/g, "")
                        .replace(/([0-9])([0-9]{2})$/, '$1,$2')
                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
    }
});
$("#tasa_cobro_comer_dolar").on({
    "focus": function (event) {
        $(event.target).select();
    },
    "keyup": function (event) {
        $(event.target).val(function (index, value ) {
            return value.replace(/\D/g, "")
                        .replace(/([0-9])([0-9]{2})$/, '$1,$2')
                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
    }
});
$("#tasa_cobro_comer_euro").on({
    "focus": function (event) {
        $(event.target).select();
    },
    "keyup": function (event) {
        $(event.target).val(function (index, value ) {
            return value.replace(/\D/g, "")
                        .replace(/([0-9])([0-9]{2})$/, '$1,$2')
                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
    }
});

        function verifCheck(){
          if($("#propina_act").prop("checked")) {
              $("#ctas").show(1000);
              $("#ctas").show("slow");
          }else{
              $("#ctas").hide(1000);
              $("#ctas").hide("fast");
          }
        }
		
	  function verifStripe(){
		  if($("#status_stripe").prop("checked")) {
			  $("#stripe").show(1000);
			  $("#stripe").show("slow");
			  
			  
		  }else{
			  $("#stripe").hide(1000);
			  $("#stripe").hide("fast");	  
			  
		  }
	  }			

        //formateo de campos a moneda
        function format(input)
        {
            var num = input.value.replace(/\./g,'');
            if(!isNaN(num)){
                  num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
                  num = num.split('').reverse().join('').replace(/^[\.]/,'');
                  input.value = num;
            }else{ 
                  //$("#msg-formato").html('Solo se permiten valores númericos'); 
                  input.value = input.value.replace(/[^\d\.]*/g,'');
            }
        }

        function formatNumCuenta(input){
          var num = input.value.replace(/\./g,'');
          if(!isNaN(num)){
                  num = num.toString().split('').reverse().join('').replace(/(?=\d*?)(\d{0})/g,'$1');
                  num = num.split('').reverse().join('').replace(/^[]/,'');
                  input.value = num;
            }else{ 
                  //$("#msg-formato").html('Solo se permiten valores númericos'); 
                  input.value = input.value.replace(/[^\d\ ]*/g,'');
            }
        }
  
       //función para bloquear el boton submit para controlar los inserts
      function bloqButton(){
          if($('#form-comercios').valid()){
            $("#form-validation").css("visibility", "hidden");
          }else{
            $("#form-validation").css("visibility", "show");
          }
      }

      function justNumbers(e)
        {
        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) || (keynum == 46))
        return true;
         
        return /\d/.test(String.fromCharCode(keynum));
      }

       function habilitarCtaPal()

    {
        var camp1= $('#num_cta').val();
        var camp2= '0174';
        var boton= document.getElementById('form-validation');
		
		if(camp1.length > 0)
		{
			if (camp1.substr(-20,4) == camp2) {
				$("#msgCtaPal").html("");
				$('#formPpal').removeClass('has-error');
				//$("#form-validation").prop("disabled",false);
				document.getElementById('form-validation').disabled = false;
				document.getElementById('form-validation1').disabled = false;
				//boton.disabled = false;
			}else {
				$('#formPpal').addClass('has-error');
				$("#msgCtaPal").html('El número de cuenta debe iniciar con "0174".');
				//$("#form-validation").prop("disabled",true);
				document.getElementById('form-validation').disabled = true;
				document.getElementById('form-validation1').disabled = true;
				//boton.disabled = true;
			}			
		}
		else{
				$("#msgCtaPal").html("");
				$('#formPpal').removeClass('has-error');
				document.getElementById('form-validation').disabled = false;
				document.getElementById('form-validation1').disabled = false;			
		}
    }

    function habilitarCtaPalDolar()

    {
        var camp1= $('#num_cta_dolar').val();
        var camp2= '01740720';
        var boton= document.getElementById('form-validation');
		if(camp1.length > 0)
		{	
				if (camp1.substr(-20,8) == camp2) {
					$("#msgCtaPalDolar").html("");
					$('#formPpal2').removeClass('has-error');
					//$("#form-validation").prop("disabled",false);
          $('#tasa_cobro_comer_dolar').val('');
          $('#tasa_cobro_comer_dolar').attr('name' , 'tasa_cobro_comer_dolar');
          $('#cobro_dolar').show();
					document.getElementById('form-validation').disabled = false;
					document.getElementById('form-validation1').disabled = false;
					//boton.disabled = false;
         

				}else {
					$('#formPpal2').addClass('has-error');
					$("#msgCtaPalDolar").html('El número de cuenta debe iniciar con "01740720".');
					//$("#form-validation").prop("disabled",true);
					document.getElementById('form-validation').disabled = true;
					document.getElementById('form-validation1').disabled = true;
					//boton.disabled = true;
         
				}
		}
		else{
			$("#msgCtaPalDolar").html("");
			$('#formPpal2').removeClass('has-error');
      $('#tasa_cobro_comer_dolar').val('');
      $('#tasa_cobro_comer_dolar').removeAttr('name');
      $('#cobro_dolar').hide();
			document.getElementById('form-validation').disabled = false;
			document.getElementById('form-validation1').disabled = false;
      
		}
    }
     function habilitarCtaPalEuro()

    {
        var camp1= $('#num_cta_euro').val();
        var camp2= '01740700';
        var boton= document.getElementById('form-validation');
		if(camp1.length > 0)
		{
				if (camp1.substr(-20,8) == camp2) {
					$("#msgCtaPalEuros").html("");
					$('#formPpal3').removeClass('has-error');
					//$("#form-validation").prop("disabled",false);
          $('#tasa_cobro_comer_euro').val('');
          $('#tasa_cobro_comer_euro').attr('name' , 'tasa_cobro_comer_euro');
          $('#cobro_euro').show();
					document.getElementById('form-validation').disabled = false;
					document.getElementById('form-validation1').disabled = false;
					//boton.disabled = false;
         
				}else {
					$('#formPpal3').addClass('has-error');
					$("#msgCtaPalEuros").html('El número de cuenta debe iniciar con "01740700".');
					//$("#form-validation").prop("disabled",true);
					document.getElementById('form-validation').disabled = true;
					document.getElementById('form-validation1').disabled = true;
					//boton.disabled = true;
				}
		}
		else{
				$("#msgCtaPalEuros").html("");
				$('#formPpal3').removeClass('has-error');
        $('#tasa_cobro_comer_euro').val('');
        $('#tasa_cobro_comer_euro').removeAttr('name');
        $('#cobro_euro').hide();
				document.getElementById('form-validation').disabled = false;
				document.getElementById('form-validation1').disabled = false;	
        
		}
    }

       function habilitarCtaSec()

    {
        var camp1= $('#num_sec').val();
        var camp2= '0174';
        var boton= document.getElementById('form-validation');

        if (camp1.substr(-20,4) == camp2) {
            $("#msgCtaSec").html("");
            $('#formSec').removeClass('has-error');
            //$("#form-validation").prop("disabled",false);
            document.getElementById('form-validation').disabled = false;
            document.getElementById('form-validation1').disabled = false;
            //boton.disabled = false;
        }else {
            $('#formSec').addClass('has-error');
            $("#msgCtaSec").html('El número de cuenta debe iniciar con "0174".');
            //$("#form-validation").prop("disabled",true);
            document.getElementById('form-validation').disabled = true;
            document.getElementById('form-validation1').disabled = true;
            //boton.disabled = true;
        }
    }
</script>
@endsection
