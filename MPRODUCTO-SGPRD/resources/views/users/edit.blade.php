@extends('layouts.app')
 @php
   if ($rolUser =='5') {
      $usuarios = "Clientes";
   }else{
      $usuarios = "Usuarios";
   }
 @endphp
 @section('titulo')
    Editar {{ $usuarios }}
@endsection


@section('contenido')
{{ Form::model($user, ['class'=>'form-horizontal', 'id'=>'form','method' => 'PATCH','route' => ['users.update', $user->id,]]) }}
 <div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-diamond"></i>
                              {{ $usuarios }}</h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>
                {{ $usuarios }}
              </li>
              <li class="active">
              <strong>Editar</strong>
              </li>
            </ol>
            </div>
            <div class="col-lg-4">
              <div class="title-action">
                <a href="{{route('users.index')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
                <button type="submit" class="btn btn-primary" id="form-validation">
                  <span class="btn-label"><i class="fa fa-check"></i></span>
                  Guardar
                </button>
              </div>
          </div>

        </div>

        <div class="wrapper wrapper-content animated fadeInRight ecommerce">
          @include('error')
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Editar {{ $usuarios }}</h5>
                        </div>
                        <div class="ibox-content">



                            {!! Form::hidden('kind', 1, array('placeholder' => 'kind','class' => 'form-control')) !!}

                            <input id="userid" type="hidden" name="" value="{{ $user->id }}">

                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">

                                    <div class="input-group m-b">
                                            <div class="input-group-btn">
                                              {{ Form::select('nacionalidad', [
                                                 'V' => 'V',
                                                 'E' => 'E',
                                                 'P' => 'P',
                                               ], null, ['class' => 'form-group input-lg m-b ', 'placeholder'=>'Seleccione']
                                              ) }}
                                            </div>
                                             {!! Form::text('dni', null, array('placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3,'maxlength'=>10 ,'onkeyup'=>'this.value=Numero(this.value)')) !!}
                                    </div>

                                  </div>
                              </div>

                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                     {!! Form::text('first_name', null, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)')) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Apellido <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    {!! Form::text('last_name', null, array('placeholder' => 'Apellido','class' => 'form-control input-lg m-b', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)')) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Correo Electrónico<span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    {!! Form::text('email', null, array('id'=>'email', 'placeholder' => 'Correo Electrónico', 'onblur'=>'validarEmail()', 'class' => 'form-control input-lg m-b', 'maxlength'=>50)) !!}
                                    <div id="msgEmail" class="text-danger" ></div>
                                  </div>
                              </div>							  
                              <div class="hr-line-dashed"></div>
                              <div class="form-group"><label class="col-sm-2 control-label" for="birthdate">Fecha de Nacimiento</label>

                                  <div class="col-sm-10">
                                      <div class="input-group date" id="birthdate">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text('birthdate', null, array('id'=>'fechaNac','placeholder' => 'Fecha de Nacimiento','class' => 'form-control input-lg m-b', 'readonly') ) !!}
                                      </div>
                                    </div>
                                  </div>
                                <!--div class="form-group"><label class="col-sm-2 control-label">Fecha de Nacimiento</label>
                                  <div class="col-sm-10">
                                    {!! Form::text('birthdate', null, array('id'=>'fechaNac','placeholder' => 'Fecha de Nacimiento','class' => 'form-control input-lg m-b')) !!}
                                  </div>
                                </div-->
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Número Telefonico<span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    <div class="input-group m-b">
                                            <div class="input-group-btn">
                                              {{ Form::select('cod_tel', [
                                                 '58412' => '0412',
                                                 '58414' => '0414',
                                                 '58424' => '0424',
                                                 '58416' => '0416',
                                                 '58426' => '0426',
                                               ], null, ['class' => 'form-group input-lg m-b ', 'placeholder'=>'Seleccione','required','id'=> 'cod_tel']
                                              ) }}
                                            </div>
                                             {!! Form::text('num_tel', null, array('placeholder' => 'Número Telefonico','class' => 'form-control input-lg m-b', 'maxlength'=>7 )) !!}
                                    </div>

                                  </div>
                              </div>

                              @if ($rolUser =='2' || $rolUser =='4' || $rolUser =='6')
                                <div class="hr-line-dashed"></div>
                                <div class="form-group"><label class="col-sm-2 control-label">Perfil <span class="text-danger">*</span></label>

                                    <div class="col-sm-10">
                                      {!! Form::select('roles[]', $roles,$userRole, array('class' => 'form-control','placeholder'=>'Seleccione')) !!}
                                    </div>
                                </div>
                              @else
                                <div class="form-group hidden"><label class="col-sm-2 control-label">Perfil <span class="text-danger">*</span></label>

                                    <div class="col-sm-10">
                                      {!! Form::select('roles[]', $roles,$userRole, array('class' => 'form-control','placeholder'=>'Seleccione')) !!}
                                    </div>
                                </div>
                              @endif

                            @if($rolUser !='5')
                            
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Contraseña <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    {!! Form::password('password', array('placeholder' => 'Contraseña','class' => 'form-control input-lg m-b')) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Confirmar Contraseña <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    {!! Form::password('confirm-password', array('placeholder' => 'Confirmar Contraseña','class' => 'form-control input-lg m-b')) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                              @endif

                              @if($rolUser == 5)
                            <button type="button" class="btn btn-primary" data-bind="click: onAddOther"><span class="btn-label" >
                                            <i class="fa fa-plus"></i>
                                        </span>Otro</button>
                            </button>

<div  data-bind="foreach: carnets">
                                  <div class="perfil5">
                                   
                            <div class="hr-line-dashed"></div>
                             <div  class="hr-line-dashed"></div>
                                <input type="hidden" name="carnets[id][]" data-bind="value: id">

                                 <div class="form-group">
                                <label class="col-sm-2 control-label">Tipo de Producto <span class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                  <select class="form-control  input-lg m-b tipo" name="carnets[tipoProducto][]" style="width: 100%;" data-bind="value: tipo_producto, event:{ change: $parent.permissionChanged}">
                                    <option value="" disabled selected>Seleccione</option>
                                    <option value="1">Interno</option>
                                    <option value="2">Externo</option>
                                  </select>
                                </div>
                              </div>

                              <div class="form-group"><label class="col-sm-2 control-label">Moneda <span class="text-danger">*</span></label>
                                  <div class="col-sm-2">
                                    <select class="form-control  input-lg m-b" name="carnets[fk_monedas][]" style="width: 100%;" data-bind="value: fk_monedas">
                                          <option value="">Seleccione</option>
                                          @foreach($monedas as $moneda)
                                          <option value="{{ $moneda->mon_id }}">{{ $moneda->mon_nombre }}</option>
                                          @endforeach
                                    </select>
                                  </div>
                                  <label class="col-sm-2 control-label">Límite <span class="text-danger">*</span></label>
                                  <!-- ko if: tipo_producto === '2' -->
                                  <div class="col-sm-4">                                  
                                     <input type="text" name="carnets[limite][]" data-bind="value: limite" onkeypress="return justNumbers(event);" class="form-control input-lg m-b limite" onblur="format(this)" maxlength="20" readonly="">
                                  </div>
                                 <!-- /ko -->   
                                  <!-- ko if: tipo_producto === '1' -->
                                  <div class="col-sm-4">                                  
                                     <input type="text" name="carnets[limite][]" data-bind="value: limite" onkeypress="return justNumbers(event);" class="form-control input-lg m-b limite" onblur="format(this)" maxlength="20">
                                  </div>
                                 <!-- /ko --> 
                                  <!-- ko if: tipo_producto === '' -->
                                  <div class="col-sm-4">                                  
                                     <input type="text" name="carnets[limite][]" data-bind="value: limite" onkeypress="return justNumbers(event);" class="form-control input-lg m-b limite" onblur="format(this)" maxlength="20" >
                                  </div>
                                 <!-- /ko -->     
                                  <div class="col-sm-2">
                                    <button type="button" class="btn btn-danger" data-bind="click: $parent.onRemove"><span class="btn-label" >
                                              <i class="fa fa-close"></i>
                                          </span>Eliminar</button>
                                    </button>
                                  </div>
                                  
                                
                              </div>
                             

                           
                                              <!-- ko if: tipo_producto === '2' -->
                  <div class="ver">
                              <div class="form-group">
                  <label class="col-sm-2 control-label">Emisor <span class="text-danger">*</span></label>
                                <div class="col-sm-10">
                    <select class="form-control  input-lg m-b" id="emisor" name="carnets[emisor][]" style="width: 100%;" data-bind="value: cod_emisor">                    
                    @foreach($emisor as $emisores)
                    <option value="{{ $emisores->cod_emisor }}">{{ $emisores->nombre }}</option>
                    @endforeach
                                  </select>
                                </div>
                              </div>
                                <div class="form-group">
                                  <label class="col-sm-2 control-label">Código Cliente Emisor<span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    {!! Form::text('carnets[codClienteEmisor][]', null, array('id'=>'codEmisor','data-bind' => 'value: cod_cliente_emisor','placeholder' => 'Código de cliente emisor','class' => 'form-control input-lg m-b codEmisor','maxlength'=>16, 'onblur'=>'validarCodClientEmisor(this)'))  !!}
                                    <div id="msgCarnet" class="text-danger" ></div>
                                  </div>
                                </div> 
                              </div>                  
                <!-- /ko -->     

                <!-- ko if: tipo_producto === '1' -->
                  <div class="ver" style="display: none;">
								  <div class="form-group"  >
									<label class="col-sm-2 control-label">Emisor <span class="text-danger">*</span></label>
									<div class="col-sm-10">
									  <select class="form-control  input-lg m-b" id="emisor" name="carnets[emisor][]" style="width: 100%;" data-bind="value: cod_emisor">
										@foreach($emisor as $emisores)
										<option value="{{ $emisores->cod_emisor }}">{{ $emisores->nombre }}</option>
										@endforeach
									  </select>
									</div>
								  </div> 
                                <div class="form-group">
                                  <label class="col-sm-2 control-label">Código Cliente Emisor<span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    {!! Form::hidden('carnets[codClienteEmisor][]', null, array('id'=>'codEmisor','data-bind' => 'value: cod_cliente_emisor','placeholder' => 'Código de cliente emisor','class' => 'form-control input-lg m-b codEmisor','maxlength'=>16, 'onblur'=>'validarCodClientEmisor(this)'))  !!}
                                    <div id="msgCarnet" class="text-danger" ></div>
                                  </div>
                                </div> 
                              </div>								  
								<!-- /ko -->     
                <!-- ko if: tipo_producto === '' -->
								  <div class="ver" style="display: none;">
								  <div class="form-group"  >
									<label class="col-sm-2 control-label">Emisor <span class="text-danger">*</span></label>
									<div class="col-sm-10">
									  <select class="form-control  input-lg m-b" id="emisor" name="carnets[emisor][]" style="width: 100%;" data-bind="value: cod_emisor">
										@foreach($emisor as $emisores)
										<option value="{{ $emisores->cod_emisor }}">{{ $emisores->nombre }}</option>
										@endforeach
									  </select>
									</div>
								  </div> 
                                <div class="form-group">
                                  <label class="col-sm-2 control-label">Código Cliente Emisor<span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    {!! Form::text('carnets[codClienteEmisor][]', null, array('id'=>'codEmisor','data-bind' => 'value: cod_cliente_emisor','placeholder' => 'Código de cliente emisor','class' => 'form-control input-lg m-b codEmisor','maxlength'=>16, 'onblur'=>'validarCodClientEmisor(this)'))  !!}
                                    <div id="msgCarnet" class="text-danger" ></div>
                                  </div>
                                </div> 
                              </div>							  
								<!-- /ko -->     								
								
 			
                           
                              <div class="form-group"  >
                                <label class="col-sm-2 control-label">Estatus <span class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                 <select class="form-control  input-lg m-b" name="carnets[transar][]" style="width: 100%;" data-bind="value: transar">
                                    <option value="" disabled>Seleccione</option>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                  </select>
                                </div>
                              </div>




                              
                                <div class="form-group"><label class="col-sm-2 control-label">Tarjeta virtual<span class="text-danger">*</span></label>

                                    <div class="col-sm-10">
                                      {!! Form::text('carnets[carnet][]', null, array('id'=>'carnet','data-bind' => 'value: carnet','placeholder' => 'Codigo de tarjeta virtual','class' => 'form-control input-lg m-b','maxlength'=>16, 'onkeyup'=>'this.value=Numero(this.value)','onblur'=>'validarCarnet(this)')) !!}
                                      <div id="msgCarnet" class="text-danger" ></div>
                                    </div>
                                  </div>

                                  <div class="form-group"><label class="col-sm-2 control-label">Tarjeta real<span class="text-danger">*</span></label>

                                    <div class="col-sm-10">
                                      {!! Form::text('carnets[carnet_real][]', null, array('id'=>'carnetReal','data-bind' => 'value: carnet_real','placeholder' => 'Código de tarjeta real','class' => 'form-control input-lg m-b','maxlength'=>16, 'onkeyup'=>'this.value=Numero(this.value)', 'onblur'=>'validarCarnetReal(this)')) !!}
                                       <div id="msgCarnetReal" class="text-danger" ></div>
                                    </div>
                                  </div>
                               
                        </div><!--perfil 5-->

                        </div><!--data-bind foreach-->

                              @endif
                              <div class="hr-line-dashed"></div>
                              <div class="form-group">
                                  <div class="title-action">
                                    <a href="{{route('users.index')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
                                    <button type="submit" class="btn btn-primary" id="form-validation">
                                      <span class="btn-label">
                                        <i class="fa fa-check"></i>
                                      </span>
                                        Guardar
                                    </button>
                                  </div>
                              </div>
                    </div>
                </div>
            </div>
    </div>
    {!! Form::close() !!}
</div>
@endsection

@section('scripts')
<script src="{!!asset('js/jackpotScripts/jackpotFunctions.js')!!}"></script>
<script src="{!!asset('js/plugins/select2/js/select2.min.js')!!}"></script>
<script src="{!!asset('js/plugins/jasny/jasny-bootstrap.min.js')!!}"></script>
<script type='text/javascript' src="{!!asset('js/knockout-3.5.0.js')!!}"></script>

@if(session('status')=='NoMoneda'))
<script type="text/javascript">
swal("Error", "El campo Moneda no puede quedar en blanco", "error");
</script>
@endif

@if(session('status')=='NoLimite'))
<script type="text/javascript">
swal("Error", "El campo Límite no puede quedar en blanco", "error");
</script>
@endif

@if(session('status')=='NoCarnet'))
<script type="text/javascript">
swal("Error", "El campo Carnet no puede quedar en blanco", "error");
</script>
@endif

@if(session('status')=='NoCarnet_real'))
<script type="text/javascript">
swal("Error", "El campo Carnet Virtual no puede quedar en blanco", "error");
</script>
@endif


<script type="text/javascript">
  $('.select2').select2();

   function validarCarnet(e){
	   
              var carnet = $(e).val();
			  var user_id = $("#userid").val();
	   
	   
            var url = window.location;

            var pat = /edit/;

            if(pat.test(url) == true){
              url=String(url);

              url=url.replace("/users/" +user_id +"/edit",'');

            }

              
			  if(carnet.length > 0)
			  {
                $.get(url + "/users/checkCarnetEdit/"+carnet+'/'+ user_id, function(res){

                  if(res == true){
                    $($(e).siblings()[0]).html('La Tarjeta Virtual '+ carnet +' que ha ingresado ya está en uso.');
                    $(e).val('');
                  }else{
                    $($(e).siblings()[0]).html("");
                  }
                });
			  }
  }//END FUNCTION VALIDAR CARNET
  function validarCarnetReal(e){
              var carnet = $(e).val();
              var user_id = $("#userid").val();	  
	  
            var url = window.location;

            var pat = /edit/;

            if(pat.test(url) == true){
              url=String(url);

              url=url.replace("/users/" +user_id +"/edit",'');

            }	  
			if(carnet.length > 0)
			{
                $.get(url + "/users/checkCodClientEmisor/"+carnet+'/'+ user_id, function(res){

                  if(res == true){
                     $($(e).siblings()[0]).html('El número de Tarjeta Real '+ carnet + ' que ha ingresado ya está en uso.');
                     $(e).val('');
                   
                  }else{
                    $($(e).siblings()[0]).html("");
                  }
                });
			}
  }//END FUNCTION VALIDAR CARNET REAL

  function validarCodClientEmisor(e){
     var user_id = $("#userid").val();   
              var url = window.location;

              var pat = /edit/;

              if(pat.test(url) == true){
                url=String(url);

                url=url.replace("/users/" +user_id +"/edit",'');
              } 
        var codClientEmisor = $(e).val();
        if(codClientEmisor.length > 0)
        {              
          $.get(url + "/users/checkCodClientEmisor/"+codClientEmisor+'/'+ user_id, function(res){

            if(res == true){
              $($(e).siblings()[0]).html('El Código de Cliente Emisor '+ codClientEmisor +' que ha ingresado ya está en uso.');
              $(e).val('');
            }else{
              $($(e).siblings()[0]).html("");
            }
          });
        }
  }//END FUNCTION VALIDAR CODIGO CLIENTE EMISOR

  function validarEmail(){
	  
		     var email = $("#email").val();
              var user_id = $("#userid").val();
	  
            var url = window.location;

            var pat = /edit/;

            if(pat.test(url) == true){
              url=String(url);

              url=url.replace("/users/" +user_id +"/edit",'');

            }		  
              
                $.get(url + "/users/checkEmailEdit/"+email+'/'+ user_id, function(res){

                  if(res == true){
                    $("#msgEmail").html('El Correo Electrónico '+ email+' que ha ingresado ya está en uso.');
                    $("#email").val('');
                   
                  }else{
                    $("#msgEmail").html("");
                  }
                });
  }//END FUNCTION VALIDAR CARNET REAL


</script>
<script>
    $(document).ready(function() {

        $('#birthdate').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            format: 'yyyy-mm-dd',
            autoclose: true
        });

         function validadoCarnet(){
            $("#msgCarnet").html("");
    }

        $( "form" ).on( "submit", function(e) { 

      var has_empty = false;
        
          var fechaNac = $($(this).find("input[name = 'birthdate']")).val();
          if(fechaNac =="" || fechaNac ==null){
            $('#fechaNac').prop("type", "hidden");          }
       
          $(this).find("input[type != 'hidden']").each(function () {
           
        if ( $(this).val()==="") { 
          has_empty = true;
          swal("Error", "Complete todos los campos para Guardar sus cambios", "error");
              e.preventDefault();
        }
         
      if ( has_empty ) { 
        return false; 
      }

          }); 
    });  
      });
</script>

<script type="text/javascript">
    function justNumbers(e){
        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) || (keynum == 46))
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

  function Text(string){//solo letras
    var out = '';
    //Se añaden las letras validas
    var filtro = 'abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ';//Caracteres validos

    for (var i=0; i<string.length; i++)
       if (filtro.indexOf(string.charAt(i)) != -1)
       out += string.charAt(i);
    return out;
  }

  function Numero(string){//solo numeros
    var out = '';
    //Se añaden los numeros validas
    var filtro = '1234567890';//Caracteres validos

    for (var i=0; i<string.length; i++)
       if (filtro.indexOf(string.charAt(i)) != -1)
       out += string.charAt(i);
    return out;
  }

  /*function format(input){
            var num = input.value.replace(/\./g,'');
            if(!isNaN(num)){
                  num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
                  num = num.split('').reverse().join('').replace(/^[\.]/,'');
                  input.value = num;
            }else{ */
                  //$("#msg-formato").html('Solo se permiten valores númericos');
                  //input.value = input.value.replace(/[^\d\.]*/g,'');
            /*}
  }*/
    /* View Model */
  var CarnetViewModel = function() {
    var self = this;
    var emptyCarnet = {
      'id': null,
      'fk_monedas':"",
      'limite': "",
      'carnet': "",
      'carnet_real': "",
      'tipo_producto': "",
      'cod_emisor': "",
      'cod_cliente_emisor': "",
      'transar': "",
    };

    var carnets = JSON.parse('{!!json_encode($carnets)!!}');
   
    this.carnets = ko.observableArray(carnets);
   
    self.onAddOther= function (){
      
      self.carnets.push({...emptyCarnet})
    }
    self.onRemove = function (item){
    if(item.carnet != "" || item.carnet_real != "" || item.limite != "" || item.transar != ""){
        swal("Error", "No puedes eliminar un producto ya creado. Utiliza la opción inactivar  para que no se pueda operar con el producto", "error");
    }else{
        self.carnets.remove(item);
    }
    }//onRemove


    var limite = "";
    self.permissionChanged = function (item, event){

      if (item.tipo_producto == '1'){

        $($(event.originalEvent.target).parents()[2]).find('.ver').hide();
        $($(event.originalEvent.target).parents()[2]).find('.limite').prop("readonly", false);
        $($(event.originalEvent.target).parents()[2]).find('.limite').val(limite); 

        $($(event.originalEvent.target).parents()[2]).find('.codEmisor').prop("type", "hidden");  

      }else{   

        $($(event.originalEvent.target).parents()[2]).find('.ver').show();
        limite = $($(event.originalEvent.target).parents()[2]).find('.limite').val();
        
        $($(event.originalEvent.target).parents()[2]).find('.limite').val('0,00');
        $($(event.originalEvent.target).parents()[2]).find('.limite').prop("readonly", true);
        $($(event.originalEvent.target).parents()[2]).find('.codEmisor').prop("type", "text"); 

      }//else
    }//function permissionChanged  


    }
ko.applyBindings(new CarnetViewModel());


</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\UsersEditRequest') !!}

@endsection
