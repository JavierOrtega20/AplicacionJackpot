@extends('layouts.app')
@section('titulo', 'Comercios')

@section('contenido')

{!! Form::model($comercio, ['method' => 'PATCH','route' => ['comercios.update',$comercio->IdComercio],'class'=>'form-horizontal','id'=>'form-comercios']) !!}

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
        <strong>Editar</strong>
      </li>
    </ol>
  </div>
  <div class="col-lg-4">
    <div class="title-action">
	  @if(!$comercio->es_sucursal)
		  <a href="{{route('comercios.index')}}" class="btn btn-white" ><i class="fa fa-times"></i> Cancelar </a>
	  @else
			<a href="{{ route('comercios.edit',[$comercio->retorno, 0]) }}" class="btn btn-success" ><i class="fa fa-chevron-circle-left"></i> Regresar </a>
	  @endif	  
      <button type="submit" class="btn btn-primary" id="form-validation" onclick="verifiNumCuenta();"><span class="btn-label">
        <i class="fa fa-check"></i>
      </span>Guardar</button>
    </div>
  </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight ecommerce">
  <div class="row">
      <div class="col-lg-12">
        <div class="ibox float-e-margins">
          <div class="ibox-title">
              <h5>Editar comercio</h5>
          </div>

          <div class="ibox-content">
              <div class="hr-line-dashed"></div>
              <div class="form-group"><label class="col-sm-2 control-label">&nbsp;</label>
				<div class="col-sm-5">
				  @if(!$comercio->es_sucursal)
					  <a href="#sucursales" id="irsucursalesUp" class="btn btn-primary">Ir a sucursales</a>
				  @endif										
				</div>
			  @if(!$comercio->posee_sucursales)			  
				<div class="col-sm-5 text-right">
					<a href="{{route('comercios.canales',[$comercio->IdComercio, $comercio->retorno])}}" class="btn btn-success"><i class="fa fa-compress"></i>&nbsp;Configuracion de terminales</a>                              
				</div>
			  @endif
              </div>			  
              <div class="hr-line-dashed"></div>
              <div class="form-group"><label class="col-sm-2 control-label">Rif <span class="text-danger">*</span></label>
                  <div class="col-sm-10">
          <div class="row">
            <div class="col-md-2">
                        @php
                          $comercio->letrarif = substr($comercio->rif,0,1);

                          $comercio->rif = substr($comercio->rif,1,15);
                        @endphp                   
            {{ Form::select('letrarif', [
			   'J' => 'J',
			   'V' => 'V',
			   'E' => 'E',
			   'R' => 'R',
			   'G' => 'G',
						 ], null, ['class' => 'form-control input-lg m-b','id'=> 'letrarif', 'placeholder'=>'Seleccione']
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
                                  
                    {!! Form::text('descripcion', null, array('placeholder' => 'Nombre','id'=> 'descripcion','class' => 'form-control input-lg m-b', 'value' =>'$comercio->descripcion','maxlength' =>'50')) !!}

                  </div>
  
              </div>

              <div class="hr-line-dashed"></div>
              <div class="form-group"><label class="col-sm-2 control-label">Razón Social <span class="text-danger">*</span></label>
                  <div class="col-sm-10">

                    {!! Form::text('razon_social', null, array('placeholder' => 'Razón Social','id'=> 'razon_social','class' => 'form-control input-lg m-b', 'value' =>'$comercio->razon_social','maxlength' =>'50')) !!}
                                    
                  </div>
              </div>
			  {!! Form::hidden('es_sucursal',null,array('id'=>'es_sucursal')) !!}
			  {!! Form::hidden('retorno',null,array('id'=>'retorno')) !!}

			  <div style="display: none;" id="divSucursal">
				  <div class="hr-line-dashed"></div>  
				  <div class="form-group"><label class="col-sm-2 control-label">Sucursal <span class="text-danger">*</span></label>
					  <div class="col-sm-10">
						{!! Form::text('nombre_sucursal', 'NA', array('placeholder' => 'Sucursal','id'=> 'nombre_sucursal','class' => 'form-control input-lg m-b','maxlength' =>'50')) !!}
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

                  {!! Form::textarea('direccion', null, array('placeholder' => 'Dirección','class' => 'form-control input-lg m-b', 'value' =>'$comercio->direccion','rows'=>6)) !!}
                                    
                </div>
              </div>

              <div class="hr-line-dashed"></div>
              <div class="form-group"><label class="col-sm-2 control-label">Tel&eacute;fono 1 <span class="text-danger">*</span></label>

                <div class="col-sm-10">

                      {!! Form::text('telefono1', null, array('placeholder' => 'Telefono 1','class' => 'form-control input-lg m-b', 'value' =>'$comercio->telefono1','onkeypress'=>'return justNumbers(event);','maxlength' =>'15')) !!}
                                  
                </div>
              </div>

              <div class="hr-line-dashed"></div>
              <div class="form-group"><label class="col-sm-2 control-label">Telefono 2</label>

                  <div class="col-sm-10">

                    {!! Form::text('telefono2', null, array('placeholder' => 'Telefono 2','class' => 'form-control input-lg m-b', 'value' =>'$comercio->telefono2','onkeypress'=>'return justNumbers(event);','maxlength' =>'15')) !!}
                                  
                  </div>
              </div>

              <div class="hr-line-dashed"></div>
              <div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico <span class="text-danger">*</span></label>
                <div class="col-sm-10">

                    {!! Form::text('email', null, array('placeholder' => 'Correo Electrónico','class' => 'form-control input-lg m-b', 'value' =>'$comercio->email','maxlength' =>'50')) !!}             
                </div>
              </div>

              <!--<div class="hr-line-dashed"></div>
              <div class="form-group">
                  <label class="col-sm-2 control-label">
                      Código Afiliación Credicard
                      <span class="text-danger">*</span>
                  </label>
                  <div class="col-sm-10">
                      {!! Form::text('codigo_afi_come', null, array('placeholder' => 'Código de Afiliación del Comercio','class' => 'form-control input-lg m-b','value'=>'$comercio->codigo_afi_come','maxlength' =>'25')) !!}
                  </div>
              </div>-->
			  
              <div class="hr-line-dashed"></div>
              <div class="form-group">
                  <label class="col-sm-2 control-label">
                      Código Afiliación Banplus Pay
                      <span class="text-danger">*</span>
                  </label>
                  <div class="col-sm-10">
                      {!! Form::text('codigo_afi_real', null, array('readonly' => 'readonly','id' => 'codigo_afi_real','class' => 'form-control input-lg m-b','value'=>'$comercio->codigo_afi_real','maxlength' =>'25')) !!}
                  </div>
              </div>			  
              <div class="hr-line-dashed"></div>
             

              <div class="hr-line-dashed"></div>
              <div id="formPpal" class="form-group" >
                <label class="col-sm-2  control-label">
                    Número Cuenta Principal en Bolívares(Consumo)
                </label>
                <div class="col-sm-10">
                    {!! Form::text('num_cta_princ', null, array('id'=>'num_cta','placeholder' => 'Número de Cuenta Principal en Bolívares','class' => 'form-control input-lg m-b','value'=>'$comercio->num_cta_princ','maxlength' =>'20','onkeyup'=>'formatNumCuenta(this)','onchange'=>'formatNumCuenta(this)','onblur'=>'habilitarCtaPal()')) !!}
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
                    <input type="text" name="tasa_cobro_comer" id="tasa_cobro_comer" placeholder="Tasa de cobro de comisión" class="form-control input-lg m-b" maxlength="5" value="{{$tasa_cobro_comer}}">
                    <!--{!! Form::text('tasa_cobro_comer', null, array('placeholder' => 'Tasa de cobro de comisión','class' => 'form-control input-lg m-b','value'=>$tasa_cobro_comer,'maxlength' =>'2','onkeyup'=>'format(this)','onchange'=>'format(this)')) !!}-->
                </div>
                <span style="font-size: 30px;font-weight: bold;">%</span>
              </div>



              <div id="formPpal2" class="form-group" >
                <label class="col-sm-2  control-label ">
                  Número Cuenta Principal en Dólares(Consumo)
                </label>
                <div class="col-sm-10">
                  {!! Form::text('num_cta_princ_dolar', null, array('placeholder' => 'Número de Cuenta Principal Dólares','class' => 'form-control input-lg m-b','maxlength' =>'20', 'minlength' =>'20','id'=>'num_cta_dolar','onkeyup'=>'formatNumCuenta(this)','onchange'=>'formatNumCuenta(this)','onblur'=>'habilitarCtaPalDolar()')) !!}
                     <div id="msgCtaPalDolar" class="text-danger"></div>
                </div>
              </div>
              <div class="hr-line-dashed"></div>
               <div class="form-group" id='cobro_dolar' @if ($comercio->num_cta_princ_dolar == "") style="display: none;" @endif >
                <label class="col-sm-2 control-label">
                    Tasa de Comisión $
                    <span class="text-danger">*</span>
                </label>
                                  
                <div class="col-sm-8">
                    <input type="text" name="tasa_cobro_comer_dolar" id="tasa_cobro_comer_dolar" placeholder="Tasa de cobro de comisión" class="form-control input-lg m-b" maxlength="5" value="{{$tasa_cobro_comer_dolar}}">
                    <!--{!! Form::text('tasa_cobro_comer', null, array('placeholder' => 'Tasa de cobro de comisión','class' => 'form-control input-lg m-b','value'=>$tasa_cobro_comer,'maxlength' =>'2','onkeyup'=>'format(this)','onchange'=>'format(this)')) !!}-->
                </div>
                <span style="font-size: 30px;font-weight: bold;">%</span>
              </div>


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
               <div class="form-group" id='cobro_euro' @if ($comercio->num_cta_princ_euro == "") style="display: none;" @endif>
                <label class="col-sm-2 control-label">
                    Tasa de Comisión €
                    <span class="text-danger">*</span>
                </label>
                                  
                <div class="col-sm-8">
                    <input type="text" name="tasa_cobro_comer_euro" id="tasa_cobro_comer_euro" placeholder="Tasa de cobro de comisión" class="form-control input-lg m-b" maxlength="5" value="{{$tasa_cobro_comer_euro}}">
                    <!--{!! Form::text('tasa_cobro_comer', null, array('placeholder' => 'Tasa de cobro de comisión','class' => 'form-control input-lg m-b','value'=>$tasa_cobro_comer,'maxlength' =>'2','onkeyup'=>'format(this)','onchange'=>'format(this)')) !!}-->
                </div>
                <span style="font-size: 30px;font-weight: bold;">%</span>
              </div>
			  <!--
              <div class="form-group">
                <label class="col-sm-2 control-label">Activar Stripe</label>
                <div class="col-sm-10">
                    @if($comercio->status_stripe == true)
                          {{ Form::checkbox('status_stripe','1',false,array('onclick'=>'verifStripe()','id'=>'status_stripe','checked'=>true)) }}
                    @else
                          {{ Form::checkbox('status_stripe','0',false,array('onclick'=>'verifStripe()','id'=>'status_stripe')) }}
                    @endif
                </div>
              </div>
				@if($comercio->status_stripe == true)
					  <div class="form-group" id="stripe">
						<label class="col-sm-2  control-label ">
						  Tasa de Comisión Stripe
						</label>
						<div class="col-sm-8">
						  {!! Form::text('tasa_cobro_comer_stripe', null, array('placeholder' => 'Tasa de Comisión Stripe','class' => 'form-control input-lg m-b','maxlength' =>'5','id'=>'tasa_cobro_comer_stripe')) !!}
						  <div id="msgCtaPalEuros" class="text-danger"></div>
						</div>
						<span style="font-size: 30px;font-weight: bold;">%</span>
					  </div>
				@else
					  <div class="form-group" id="stripe" style="display: none;">
						<label class="col-sm-2  control-label ">
						  Tasa de Comisión Stripe
						</label>
						<div class="col-sm-8">
						  {!! Form::text('tasa_cobro_comer_stripe', null, array('placeholder' => 'Tasa de Comisión Stripe','required' => 'required','class' => 'form-control input-lg m-b','maxlength' =>'5','id'=>'tasa_cobro_comer_stripe')) !!}
						</div>
						<span style="font-size: 30px;font-weight: bold;">%</span>
					  </div>
				@endif	  
			  <div class="hr-line-dashed"></div>-->		
              <div class="form-group">
                <label class="col-sm-2 control-label">Activar Propina</label>
                <div class="col-sm-10">
                    @if($comercio->propina_act == true)
                          {{ Form::checkbox('propina_act','1',false,array('onclick'=>'verifCheck()','id'=>'propina_act','checked'=>true)) }}
                    @else
                          {{ Form::checkbox('propina_act','0',false,array('onclick'=>'verifCheck()','id'=>'propina_act')) }}
                    @endif
                </div>
              </div>
              
              <div class="dinamico" id="ctas" style="display: none;">
                                  
                <div id="formSec" class="form-group" >
                    <label class="col-sm-2  control-label">
                        Número Cuenta Secundaria en Bolívares(Propina)
                    </label>
                    <div class="col-sm-10">
                        {!! Form::text('num_cta_secu', null, array('id'=>'num_sec','placeholder' => 'Número de Cuenta Secundaria  en Bolívares','class' => 'form-control input-lg m-b','value'=>'$comercio->num_cta_secu','maxlength' =>'20','onkeyup'=>'formatNumCuenta(this)','onchange'=>'formatNumCuenta(this)')) !!}
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
			  @if(!$comercio->es_sucursal)
				  <section id="sucursales">
				  <div class="hr-line-dashed"></div>
				  <div class="form-group" >
					<label class="col-sm-2  control-label">
						&nbsp;
					</label>			  
					  <div class="col-sm-10">
						<p>
						  <a id="mostrarsucursales" class="btn btn-primary" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
							Sucursales ({{ $countSucursales }}) 
						  </a>
						</p>				
						<div class="collapse" id="collapseExample">
						  <div class="card card-body">
						    @include('flash::message')
							<div class="title-action">
								<a id="idNuevaSucursal" href="{{ route('comercios.create', $comercio->IdComercio) }}" class="btn btn-success"><i class="fa fa-plus"></i> Nueva sucursal</a>
							</div>					  
							<table id="datatab" class="table">
									<thead>
									<tr>
										<th >Nombre</th>
										<th id="acc">Acción</th>
									</tr>
									</thead>
									<tbody>
									  @foreach($sucursales as $element)
									  <tr>
										<td >{{ $element->fulldescripcion }}</td>
										<td>
                                          <button type="button" class="btn-white btn btn-sm" data-toggle="modal" data-target="#detalle" onclick="show_comercio('{{ $element -> IdComer}}')" title="Ver">
                                            <i class="fa fa-eye"></i> 
                                             
                                          </button>										
										  <div class="btn-group" >
											  @permission('comercio-edit')
											  <a class="btn-white btn btn-sm" data-target="#detalle_edit" href="{{ route('comercios.edit',[$element -> IdComer, $comercio->IdComercio]) }}" title="Editar">
												<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
												
											 </a>
											  @if ($element->deleted_at == null)
											  
											<a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea desactivar la sucursal: {{$element->fulldescripcion}}, Razón Social: {{$element->razon_social}}?')" href="{{ route('comercios.delete',[$element->IdComer,$comercio->IdComercio]) }}" title="Desactivar">
											  <i class="fa fa-trash-o" aria-hidden="true"></i>
											 
											</a>
											  @else
											<a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea activar la sucursal: {{$element->fulldescripcion}}, Razón Social: {{$element->razon_social}}?')" href="{{ route('comercios.restore',[$element->IdComer,$comercio->IdComercio]) }}" title="Activar">
											  <i class="fa fa-plus-square-o" aria-hidden="true"></i>
											
											</a>
										  @endif
										 </a>
											 @endpermission
										  </div>
										</td>
									  </tr>
									  @endforeach								
									</tbody>
							</table>
						  </div>
						</div>                      
					  </div>					  
				  </div>			  
				</section> 				  
			  @endif			  
              <div class="form-group">
                      <div class="title-action">
                        <a href="{{route('comercios.index')}}" class="btn btn-white" ><i class="fa fa-times"></i> Cancelar </a>
                        <button type="submit" class="btn btn-primary" id="form-validation1" onclick="verifiNumCuenta();"><span class="btn-label">
                          <i class="fa fa-check"></i>
                        </span>Guardar</button>
                      </div>
              </div>
              <input type="hidden" value="{{$bancos->id}}" id="banco" name="banco"/>

          </div>
        </div>
      </div>
  </div>
</div>
{!! Form::close() !!}

@stop

          @section('modal')
          <div class="modal inmodal" id="detalle" tabindex="-1" role="dialog"  aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content animated fadeIn">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                          <i class="fa fa fa-diamond modal-icon"></i>
                          <h2 class="modal-title">Detalle del Comercio</h2>
                          </div>
                          <div class="ibox-content">
                            <ul class="unstyled">
                                  <!--<div id="id"></div>-->
                                  <div id="descripcion"></div>
                                  <div id="razon_social"></div>
                                  <div id="rif"></div>
                                  <div id="direccion"></div>
                                  <div id="telefono1"></div>
                                  <div id="telefono2"></div>
                                  <div id="email"></div>
                                  <div id="propina_act"></div>
                                  <div id="num_cta_princ"></div>
                                  <div id="num_cta_secu"></div>
                                  <div id="estatus"></div>
                                  
                            </ul>
                          </div>

                      <div class="modal-footer">
                          <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
                      </div>
                  </div>
              </div>
        </div>
     @stop

@section('scripts')
<script type="text/javascript">
$(document).ready(function(){
	$('#datatab').DataTable({
	  responsive: true,
	  "language": idioma,

	});
	
});

$(document).ready(function(){	

  


	if($('#num_terminales').val() > 0)
	{
	  var IdTerminales = '';
	  
	  var Afiliacion = parseInt($('#codigo_afi_real').val()).toString();
	  
	  var LargoIdComercio = 8 - Afiliacion.length;
	  
	  for (var i = 1; i <= $('#num_terminales').val(); i++) {
		  if(i == $('#num_terminales').val())
		  {
			  IdTerminales = IdTerminales + Afiliacion + String(i).padStart(LargoIdComercio,"00000000");
		  }
		  else{
			  IdTerminales = IdTerminales + Afiliacion + String(i).padStart(LargoIdComercio,"00000000") + ', ';
		  }		
	  }

	  $('#IdentificadorTerminales').val(IdTerminales);
	}
	else{
		$('#IdentificadorTerminales').val('');
	}	
});

function CrearTerminalesDatos()
{
	if($('#num_terminales').val() > 0)
	{
	  var IdTerminales = '';
	  
	  var Afiliacion = parseInt($('#codigo_afi_real').val()).toString();
	  
	  var LargoIdComercio = 8 - Afiliacion.length;
	  
	  for (var i = 1; i <= $('#num_terminales').val(); i++) {
		  if(i == $('#num_terminales').val())
		  {
			  IdTerminales = IdTerminales + Afiliacion + String(i).padStart(LargoIdComercio,"00000000");
		  }
		  else{
			  IdTerminales = IdTerminales + Afiliacion + String(i).padStart(LargoIdComercio,"00000000") + ', ';
		  }		
	  }

	  $('#IdentificadorTerminales').val(IdTerminales);
	}
	else{
		$('#IdentificadorTerminales').val('');
	}
}

$('#idNuevaSucursal').click( function( e ) {
	if($("#form-comercios").valid())
	{
		return true;
	}
	else{
		swal({
		  title: "Notificación",
		  text: "Debes completar todos los datos de este comercio antes de crear una sucursal.",		  
		  allowOutsideClick: false,
		  allowEscapeKey: false,
		  focusCancel: true,
		  type: "warning",
		  confirmButtonText: 'Ok',
		  confirmButtonColor: '#3085d6',	  
		}).then(function(result){

		  })		
		e.preventDefault();
		return false;		
	}
} );
</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\ComercioEditRequest') !!}

<script src="{!!asset('js/plugins/select2/js/select2.min.js')!!}"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#canal_fisico').change(function() {
        if(this.checked) {
			$("#num_terminales").attr("readonly", false);
        }
		else{
			$("#num_terminales").val('');
			$("#IdentificadorTerminales").val('');			
			$("#num_terminales").attr("readonly", true);
		}
    });
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



$('#fk_id_categoria').on('change', function() {
	
if(this.value != "")
{
	var url = window.location;

	var pat = /edit/;

	if(pat.test(url) == true){
		url=String(url);
		url=url.replace("/{!! $comercio->IdComercio !!}/{!! $comercio->retorno !!}/edit",'');
	}
	
		$.ajax({
				data:'',
				url:url+'/consultaSubcategoria/'+this.value,
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
			   }
		})		
}
else{
	document.querySelector("#fk_id_subcategoria").innerHTML = "";
	var options = '<option value="">Seleccione</option>';
	$('#fk_id_subcategoria').html(options);	
}	
});

$('#letrarif').bind('mousedown', function (event) { 
  if($("#es_sucursal").val() == 'true')
  {
	event.preventDefault();
	event.stopImmediatePropagation();
  }
});

$('#fk_id_categoria').bind('mousedown', function (event) { 
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

function IrSucursales()
{
	$("#mostrarsucursales").click();
  
	var offset = 20; //Offset of 20px

	$('html, body').animate({
		scrollTop: $("#sucursales").offset().top + offset
	}, 100);	
}

$( "#irsucursalesUp" ).click(function() {
	IrSucursales();
});

	$( document ).ready(function() {
		
		//SI REQUIERE IR A SUCURSALES
		if ($('#irsucursales').length) {
			IrSucursales();
		} else {
		  // no existe
		}		
		
		if($("#es_sucursal" ).val())
		{
		   $("#descripcion").attr("readonly", true);
		   $("#razon_social").attr("readonly", true);
		   $("#rif").attr("readonly", true);		     
		   
		   $("#nombre_sucursal").val("{!! $comercio->nombre_sucursal !!}");
		   
		   $("#divSucursal").show();
	   
		   $("#es_sucursal").val('true');
		}
		else{
			$("#es_sucursal").val('false');	
		}
				
		//Motivo Estatus
		ConstruidEstatusMotivo($("#estatus option:selected").text(),"true");
	});
</script>


<script type="text/javascript" >
    
              if($("#propina_act").prop("checked")) {
                      $("#ctas").show(1000);
                      $("#ctas").show("slow");
              }else{
                      $("#ctas").hide(1000);
                      $("#ctas").hide("fast");
              }

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
      
      if( $('#num_cta_dolar').val().length == 0){
        $('#formPpal2').addClass('has-error');
        $("#msgCtaPalDolar").html('El número de cuenta no puede estar vacio si desea colocar una comisión');
        //$("#form-validation").prop("disabled",true);
        document.getElementById('form-validation').disabled = true;
        document.getElementById('form-validation1').disabled = true;   
      }
      $(event.target).val(function (index, value ) {
        return value.replace(/\D/g, "")
                    .replace(/([0-9])([0-9]{2})$/, '$1,$2')
                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
      });
      if($('#tasa_cobro_comer_dolar').val().length == 0){
        $("#msgCtaPalDolar").html("");
        $('#formPpal2').removeClass('has-error');
        document.getElementById('form-validation').disabled = false;
        document.getElementById('form-validation1').disabled = false;
      }

    }
});

$("#tasa_cobro_comer_euro").on({
    "focus": function (event) {
        $(event.target).select();

    },

    "keyup": function (event) {
      if( $('#num_cta_euro').val().length == 0){
        $('#formPpal3').addClass('has-error');
        $("#msgCtaPalEuros").html('El número de cuenta no puede estar vacio si desea colocar una comisión');
        //$("#form-validation").prop("disabled",true);
        document.getElementById('form-validation').disabled = true;
        document.getElementById('form-validation1').disabled = true;   
      }

        $(event.target).val(function (index, value ) {
            return value.replace(/\D/g, "")
                        .replace(/([0-9])([0-9]{2})$/, '$1,$2')
                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
        });
        if($('#tasa_cobro_comer_euro').val().length == 0){
        $("#msgCtaPalEuros").html("");
        $('#formPpal3').removeClass('has-error');
        document.getElementById('form-validation').disabled = false;
        document.getElementById('form-validation1').disabled = false;
      }
    }
});

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

                function justNumbers(e){
                  var keynum = window.event ? window.event.keyCode : e.which;
                  if ((keynum == 8) || (keynum == 46))
                  return true;
                   
                  return /\d/.test(String.fromCharCode(keynum));
                }


                function cerosIzquierda(num){
                    var numero = num.value;
                    var letra='';
                    var numRif = '';
                    
                    if(numero[0] == 'J'){
                        numero = numero.split('J');
                        numero = numero[1];
                    }else{
                        numero = numero.split('C');
                        numero = numero[1];
                    }

                    /*if(num.value.length >= 1){*/
                        for(var i = 0; i < numero.length; i++){
                            /*if(i==0){
                                letra = num.value[0];
                            }else{*/
                                numRif += numero[i];
                            //}
                        }
                    /*}else{
                        letra = num.value[0];
                    }*/

                   
                    if(!numRif){
                        num.value = num.value[0]+letra+'000000000';
                    }else if(numRif.length == 1){
                        num.value = num.value[0]+letra+'00000000'+numRif;
                    }else if(numRif.length == 2){
                        num.value = num.value[0]+letra+'0000000'+numRif;
                    }else if(numRif.length == 3){
                        num.value = num.value[0]+letra+'000000'+numRif;
                    }else if(numRif.length == 4){
                        num.value = num.value[0]+letra+'00000'+numRif;
                    }else if(numRif.length == 5){
                        num.value = num.value[0]+letra+'0000'+numRif;
                    }else if(numRif.length == 6){
                        num.value = num.value[0]+letra+'000'+numRif;
                    }else if(numRif.length == 7){
                        num.value = num.value[0]+letra+'00'+numRif;
                    }else if(numRif.length == 8){
                        num.value = num.value[0]+letra+'0'+numRif;
                    }
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

       function habilitarCtaSec()

    {
        var camp1= $('#num_sec').val();
        var camp2= '0174';
        var boton= document.getElementById('form-validation');
		if(camp1.length > 0)
		{
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
		else{
			$("#msgCtaPalDolar").html("");
			$('#formPpal2').removeClass('has-error');
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
          document.getElementById('form-validation').disabled = false;
          document.getElementById('form-validation1').disabled = false;
          //boton.disabled = false;
          $('#tasa_cobro_comer_dolar').val('');
          $('#tasa_cobro_comer_dolar').attr('name' , 'tasa_cobro_comer_dolar');
          $('#cobro_dolar').show();
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
      document.getElementById('form-validation').disabled = false;
      document.getElementById('form-validation1').disabled = false;
      $('#tasa_cobro_comer_dolar').val('');
      $('#tasa_cobro_comer_dolar').removeAttr('name');
      $('#cobro_dolar').hide();

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
          document.getElementById('form-validation').disabled = false;
          document.getElementById('form-validation1').disabled = false;
          //boton.disabled = false;
          $('#tasa_cobro_comer_euro').val('');
          $('#tasa_cobro_comer_euro').attr('name' , 'tasa_cobro_comer_euro');
          $('#cobro_euro').show();
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
        document.getElementById('form-validation').disabled = false;
        document.getElementById('form-validation1').disabled = false; 
        $('#tasa_cobro_comer_euro').val('');
        $('#tasa_cobro_comer_euro').removeAttr('name');
        $('#cobro_euro').hide();
    }
    }
	

function show_comercio(rif){
	
	var url = window.location;

	var pat = /edit/;

	if(pat.test(url) == true){
		url=String(url);
		url=url.replace("/{!! $comercio->IdComercio !!}/{!! $comercio->retorno !!}/edit",'');
		url=url.replace("#sucursales",'');
		
		url = url+'/'+rif;
	}	
	
	$.ajax({
			data:'',
			url:url,
			method:'GET',
			cache:false,
			processData:false,
			contentType: false,
		   success: function (data) {
			   
					   				              			//$("#id").html('<li><h3><span class="font-normal">ID: </span>'+data['data'][0].id+'</h3></li>');
                 if (data['data'][0].deleted_at != null) {
                   var estatus = 'Inactivo';
                 }else{
                   var estatus = 'Activo';
                 }
                  $("#estatus").html('<li><h3><span class="font-normal">Estatus: </span>'+estatus+'</h3></li>');					
           			$("#descripcion").html('<li><h3><span class="font-normal">Nombre: </span>'+data['data'][0].descripcion+'</h3></li>');
                $("#razon_social").html('<li><h3><span class="font-normal">Razón Social: </span>'+data['data'][0].razon_social+'</h3></li>');
                $("#rif").html('<li><h3><span class="font-normal">Rif: </span>'+data['data'][0].rif+'</h3></li>');  
           			$("#direccion").html('<li><h3><span class="font-normal">Dirección: </span>'+data['data'][0].direccion+'</h3></li>');
           			$("#telefono1").html('<li><h3><span class="font-normal">Teléfono 1: </span>'+data['data'][0].telefono1+'</h3></li>');
                if(data['data'][0].telefono2){
           			$("#telefono2").html('<li><h3><span class="font-normal">Teléfono 2: </span>'+data['data'][0].telefono2+'</h3></li>');
                }
           			$("#email").html('<li><h3><span class="font-normal">Correo Electrónico: </span>'+data['data'][0].email+'</h3></li>');
                 if (data['data'][0].propina_act == true) {
                   var propina = 'Activo';
                 }else{
                   var propina = 'Inactivo';
                 }
                $("#propina_act").html('<li><h3><span class="font-normal">Propina: </span>'+propina+'</h3></li>');
                $("#num_cta_princ").html('<li><h3><span class="font-normal">Nº Cuenta Consumo: </span>'+data['data'][0].num_cta_princ+'</h3></li>');
                if (data['data'][0].propina_act == true) {
                   $("#num_cta_secu").html('<li><h3><span class="font-normal">Nº Cuenta Propina: </span>'+data['data'][0].num_cta_secu+'</h3></li>');
                 }else{
                   $("#num_cta_secu").html('');
                 }
										
		   }
	})	

}	

</script>
@endsection