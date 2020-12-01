@extends('layouts.app')
 @php
   if ($rolUser =='1'|| $rolUser =='2') {
      $user = "Usuarios";
   }else{
      $user = "Clientes";
   }
 @endphp
 @section('titulo')
    Crear {{$user}}
@endsection
@if($rolUser == 6)
<style>
         .perfil2,.perfil3,.perfil4,.perfil6{display:none;}
</style>
@else
<style>
         .perfil2,.perfil3,.perfil4,.perfil5,.perfil6{display:none;}
</style>
@endif
@section('contenido')

{!! Form::open(array('route' => 'users.store','method'=>'POST','class'=>'form-horizontal', 'id'=>'formUser')) !!}
 <div class="row wrapper border-bottom white-bg page-heading">
  <div class="col-lg-8">
    <h2><i class="fa fa-diamond"></i>
                              {{$user}}
                            </h2>
    <ol class="breadcrumb">
      <li>
      <a href="{{ url('home') }}">Panel</a>
      </li>
      <li>
        {{$user}}
      </li>
      <li class="active">
      <strong>Crear</strong>
      </li>
    </ol>
    </div>
    <div class="col-lg-4">
      <div class="title-action">
        <a href="{{route('users.index')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
          <button type="submit" class="btn btn-primary enviarForm" id="form-validation"><span class="btn-label">
                    <i class="fa fa-check"></i>
                </span>Crear</button>
      </div>
  </div>

</div>

        <div class="wrapper wrapper-content animated fadeInRight ecommerce">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Crear {{$user}}
                            </h5>
                        </div>
                        <div class="ibox-content">

                            @include('error')
                            

                            {!! Form::hidden('kind', 1, array('placeholder' => 'kind','class' => 'form-control')) !!}

                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Cédula <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                     <div class="col-sm-2">
                                        <select class="form-control  input-lg m-b" name="nacionalidad" style="width: 110%;">
                                          <option value="">Seleccione</option>
                                          <option value="V">V</option>
                                          <option value="E">E</option>
                                          <option value="P">P</option>
                                        </select>
                                      </div>
                                      <div class="input-group m-b col-sm-10">
                                           
                                             {!! Form::text('dni', null, array('placeholder' => 'Cédula','class' => 'form-control input-lg m-b', 'minlength'=>3, 'maxlength'=>10,'onkeyup'=>'this.value=Numero(this.value)')) !!}
                                      </div>
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                     {!! Form::text('first_name', null, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b', 'maxlength'=>20,'onkeyup'=>'this.value=Text(this.value)')) !!}
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
                                    {!! Form::text('email', null, array('id'=>'email', 'placeholder' => 'Correo Electrónico','class' => 'form-control input-lg m-b', 'maxlength'=>50)) !!}
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
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Número Telefónico<span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    <div class="col-sm-2">
										{{ Form::select('cod_tel', [
										   '58412' => '0412',
										   '58414' => '0414',
										   '58424' => '0424',
										   '58416' => '0416',
										   '58426' => '0426',
										 ], null, ['class' => 'form-control input-lg m-b','style'=>'width: 110%;', 'placeholder'=>'Seleccione','id'=> 'cod_tel']
										) }}  									
                                    </div>
                                    <div class="input-group m-b col-sm-10">
                                             {!! Form::text('num_tel', null, array('placeholder' => 'Número Telefónico','id'=> 'num_tel','class' => 'form-control input-lg m-b', 'maxlength'=>7,'required')) !!}
                                    </div>
                                  </div>
                              </div>
                            @if($rolUser =='1'|| $rolUser =='2')
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
                               @if($rolUser != 6)
                                  <div class="form-group"><label class="col-sm-2 control-label">Perfil <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    {!! Form::select('roles[]', $roles,[], array('id'=>'perfil','class' => 'form-control select2 input-lg m-b','placeholder'=>'Seleccione')) !!}
                                    <input id="rol" type="hidden" name="perfil">
                                  </div>
                              </div>
                              @endif

                              @if($rolUser == 6)
                                <input id="rol" type="hidden" name="perfil" value="5">
                              @endif




                      <div class="dinamico">
                      {{--  <div class="perfil2">
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Banco <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    <input type="text" class="form-control input-lg m-b" name="banco" value="Banplus" disabled="disabled"> --}}
{{--                                      <select class="form-control input-lg m-b" name="mbanco">
 --}}                                          {{-- <option value="">Seleccione</option> --}}
                                          {{-- @foreach($bancos as $banco)
                                          <option value="{{ $banco->id }}">{{ $banco->descripcion }}</option>
                                          @endforeach
                                      </select> --}} {{--
                                  </div>
                              </div>
                          </div> --}}
                          <div class="perfil3">
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Comercio <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                     <select class="form-control select2 input-lg m-b" name="comercio" style="width: 100%;">
                                          <option value="">Seleccione</option>
                                          @foreach($comercios as $comercio)
                                          <option value="{{ $comercio->id }}">{{ $comercio->descripcion }}</option>
                                          @endforeach
                                      </select>
                                  </div>
                              </div>
                          </div>
                        {{--  <div class="perfil4">
                               <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Call Center <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    <input type="text" class="form-control input-lg m-b" name="call" value="Banplus" disabled="disabled">  --}}
{{--                                      <select class="form-control input-lg m-b" name="mbanco">
 --}}                                          {{-- <option value="">Seleccione</option> --}}
                                          {{-- @foreach($bancos as $banco)
                                          <option value="{{ $banco->id }}">{{ $banco->descripcion }}</option>
                                          @endforeach
                                      </select> --}} {{--
                                  </div>
                              </div>
                          </div>--}}
							@if($rolUser == 6)
							  <div class="form-group">
								<label class="col-md-4 control-label">Si deseas agregar una nueva tarjeta haz click en: </label>
							   <button type="button" class="btn btn-primary" data-bind="click: onAddOther">
                          <span class="btn-label" ><i class="fa fa-plus"></i></span>Otro
								</button>
								</div>
							@endif

                        <div data-bind="foreach: carnets"> 
                          <div class="perfil5" >
                                <div class="hr-line-dashed"></div>
                            <div class="hr-line-dashed"></div>

                                  <div class="form-group">
                                  <label class="col-sm-2 control-label">Tipo de Producto <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    <select class="form-control  input-lg m-b" name="carnets[tipoProducto][]" style="width: 100%;" data-bind="value: tipoProducto, event:{ change: $parent.permissionChanged}" >
                                      <option value="" disabled selected>Seleccione</option>
                                      <option value="1">Interno</option>
                                      <option value="2">Externo</option>
                                     
                                    </select>
                                  </div>
                                </div>

                                <div class="form-group">
                                  <label class="col-sm-2 control-label">Moneda <span class="text-danger">*</span></label>
                                  <div class="col-sm-2">
                                      <select class="form-control  input-lg m-b moneda" name="carnets[fk_monedas][]" style="width: 100%;" data-bind="value: fk_monedas">           
										  @if(count($monedas) > 1)
											  @foreach($monedas as $moneda)
											  <option value="{{ $moneda->mon_id }}">{{ $moneda->mon_nombre }}</option>
											  @endforeach											  
										  @else
											  <option value="{{ $monedas[0]->mon_id }}" selected>{{ $monedas[0]->mon_nombre }}</option>
										  @endif
                                    </select>
                                  </div>
                                   <label class="col-sm-2 control-label">Limite <span class="text-danger">*</span></label>
                                  <div class="col-sm-4">

                                      <input type="text" name="carnets[limite][]" data-bind="value: limite"  onkeypress="return justNumbers(event);" class="form-control input-lg m-b limite" onblur="format(this)" maxlength="20" required>
                                  </div>
                                   
                                  <div class="col-sm-2">
                                      <button type="button" class="btn btn-danger eliminar" data-bind="click: $parent.onRemove">  <span class="btn-label" ><i class="fa fa-close"></i></span>
                                        Eliminar
                                    </button>
                                     
                                  </div>
                                   
                              </div>

                                <div class="ver" style="display: none">  
                                  <div class="form-group  " data-bind = 'attr: { "data-block": $index}'>
                                <label class="col-sm-2 control-label">Emisor <span class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                  <select class="form-control  input-lg m-b" id="emisor" name="carnets[emisor][]" style="width: 100%;" data-bind="value: emisor">                                          
                                    @foreach($emisor as $emisores)
                                    <option value="{{ $emisores->cod_emisor }}">{{ $emisores->nombre }}</option>
                                    @endforeach                       
                                  </select>
                                </div>
                              </div>

                                  <div class="form-group " data-bind = 'attr: { "data-block": $index}'>
                                    <label class="col-sm-2 control-label">Código Cliente Emisor<span class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                      {!! Form::text('carnets[codClienteEmisor][]', null, array('id'=>'codEmisor','data-bind' => 'value: codClienteEmisor','placeholder' => 'Código de cliente emisor','class' => 'form-control input-lg m-b codEmisor','maxlength'=>16,'onblur'=>'validarCodClientEmisor(this)')) !!}
                                 <div id="msgCarnet" class="text-danger" ></div>
                               </div>
                             </div>
                                </div>

                                  <div class="form-group"><label class="col-sm-2 control-label">Tarjeta Virtual<span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                     {!! Form::text('carnets[carnet][]', null, array('id'=>'carnet','data-bind' => 'value: carnet','placeholder' => 'Código de tarjeta virtual','class' => 'form-control input-lg m-b carnet','maxlength'=>16,'onkeyup'=>'this.value=Numero(this.value);validadoCarnet()','onblur'=>'validarCarnet(this)'))  !!}
                                     <div id="msgCarnet" class="text-danger" ></div>
                                  </div>
                              </div>

                              <div class="form-group"><label class="col-sm-2 control-label">Tarjeta Real<span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                     {!! Form::text('carnets[carnet_real][]', null, array('id'=>'carnetReal', 'data-bind' => 'value: carnet_real','placeholder' => 'Código de tarjeta real','class' => 'form-control input-lg m-b carnet_real','maxlength'=>16, 'onkeyup'=>'this.value=Numero(this.value);validadoCarnet()','onblur'=>'validarCarnetReal(this)')) !!}
                                     <div id="msgCarnetReal" class="text-danger" ></div>
                                  </div>
                              </div>
                          </div><!--class perfil 5-->
                        </div><!--div data-bind foreach carnet -->

                          <div class="hr-line-dashed"></div>
                          <div class="form-group">
                              <div class="title-action">
                                <a href="{{route('users.index')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
                                  <button type="submit" class="btn btn-primary enviarForm" id="form-validation"><span class="btn-label">
                                            <i class="fa fa-check"></i>
                                        </span>Crear</button>
                               
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


<script type="text/javascript">
  $('.select2').select2();
</script>
@if($rolUser != 6)
<script type="text/javascript">
  $(".perfil5").remove();
</script>
@endif

@if(session('status')=='NoMoneda'))
<script type="text/javascript">
swal("Error", "El campo Moneda no puede quedar en blanco", "error");
</script>
@endif
                  @if(session('status')=='tipoProducto'))
                  <script type="text/javascript">
                    swal("Error", "El campo Tipo de Producto no puede quedar en blanco", "error");
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
  <script>
            $(document).ready(function() {

                $('#birthdaste').datepicker({
                    todayBtn: "linked",
                    keyboardNavigation: false,
                    forceParse: false,
                    calendarWeeks: true,
                    format: 'dd/mm/yyyy',
                    autoclose: true

                });
                $('#fecha_hasta').datepicker({
                    todayBtn: "linked",
                    keyboardNavigation: false,
                    forceParse: false,
                    calendarWeeks: true,
                    format: 'dd/mm/yyyy',
                    autoclose: true
                });

                      /*$('#tipoPro').on('change', function(){
                        let typeProd = this.value;
                        if(typeProd == '1'){
                          $('.emisorhide').addClass("ocultar");
                          $('.codemihide').addClass("ocultar");

                        }else{
                          $('.emisorhide').removeClass("ocultar");
                          $('.codemihide').removeClass("ocultar");
                        }

                      });*/

            });

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
            $('#fechaNac').prop("type", "hidden");
          }
       
          $(this).find("input[type != 'hidden']").each(function () {
           
        if ( $(this).val()==="") { 
          has_empty = true;
          swal("Error", "Complete todos los campos para Guardar sus cambios", "error");
              $('#fechaNac').prop("type", "text");
              e.preventDefault();
        }
         
      if ( has_empty ) { 
        return false; 
      }

    }); 
        });//final function form-validation
   });//document.ready

</script>



<script type="text/javascript">

   function validarCarnet(e){
            var url = window.location;

            var pat = /create/;

            if(pat.test(url) == true){
              url=String(url);

              url=url.replace("/users/create",'');

            } 
			var carnet = $(e).val();			
			if(carnet.length > 0)
			{              
                $.get(url + "/users/checkCarnets/"+carnet+'/', function(res){

                  if(res == true){
                    
                    $($(e).siblings()[0]).html('El número de Tarjeta Virtual '+ carnet +' que ha ingresado ya está en uso.');
                    $(e).val('');
                  }else{
                    $($(e).siblings()[0]).html("");
                  }
                });
			}
  }//END FUNCTION VALIDAR CARNET
  function validarCarnetReal(e){
            var url = window.location;

            var pat = /create/;

            if(pat.test(url) == true){
              url=String(url);

              url=url.replace("/users/create",'');

            }  	  

              var carnet = $(e).val();
			  if(carnet.length > 0)
			  {
                $.get(url + "/users/checkCarnetReal/"+carnet+'/', function(res){

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
              var url = window.location;

              var pat = /create/;

              if(pat.test(url) == true){
                url=String(url);

                url=url.replace("/users/create",'');

              } 
        var codClientEmisor = $(e).val();   
       
        if(codClientEmisor.length > 0)
        {              
                  $.get(url + "/users/checkCodClientEmisorCreate/"+codClientEmisor+'/', function(res){

                    if(res == true){
                      
                      $($(e).siblings()[0]).html('El Código de Cliente Emisor  '+ codClientEmisor +' que ha ingresado ya está en uso.');
                      $(e).val('');
                    }else{
                      $($(e).siblings()[0]).html("");
                    }
                  });
        }
    }//END FUNCTION VALIDAR CODIGO CLIENTE EMISOR

  function validarEmail(){
            var url = window.location;

            var pat = /create/;

            if(pat.test(url) == true){
              url=String(url);

              url=url.replace("/users/create",'');

            }   	  

              var email = $("#email").val();
              
                $.get(url + "/users/checkEmail/"+email+'/', function(res){

                  if(res == true){
                    $("#msgEmail").html('El Correo Electrónico '+ email+' que ha ingresado ya está en uso.');
                    $("#email").val('');
                   
                  }else{
                    $("#msgEmail").html("");
                  }
                });
  }//END FUNCTION VALIDAR CARNET REAL

  function validarMinimo(){}
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
            }else{
                  //$("#msg-formato").html('Solo se permiten valores númericos'); */
                  //input.value = input.value.replace(/[^\d\.]*/g,'');
            /*}
  }*/
  /* View Model */
  var CarnetViewModel = function() {
    var self = this;
    var carnet = {
      'fk_monedas':"",
      'limite': "",
      'carnet': "",
              'carnet_real': "",
              'tipoProducto': "",
              'emisor': "",
              'codClienteEmisor': "",

    };
    this.carnets = ko.observableArray([{...carnet}]);

    self.onAddOther= function (){
      self.carnets.push({...carnet})
    }
    self.onRemove = function (item){
        console.log( self.carnets().length );
        if(self.carnets().length>1){
      self.carnets.remove(item);
        }else{
           swal("Error", "No puede eliminar un producto que no ha sido creado.", "error");
        }
       
    }

              self.permissionChanged = function (item, event){
                  
                  
              
                if (item.tipoProducto == '1'){ 

                  $($(event.originalEvent.target).parents()[2]).find('.ver').hide();
                  $($(event.originalEvent.target).parents()[2]).find('.limite').prop("readonly", false);              

                  $($(event.originalEvent.target).parents()[2]).find('.codEmisor').prop("type", "hidden"); 
                 
              } else { 

                  $($(event.originalEvent.target).parents()[2]).find('.ver').show()
        
                  $($(event.originalEvent.target).parents()[2]).find('.limite').val('0,00');
                  $($(event.originalEvent.target).parents()[2]).find('.limite').prop("readonly", true);
                  
                  $($(event.originalEvent.target).parents()[2]).find('.codEmisor').prop("type", "text"); 
            
                }//else
              }//function permissionChanged   
            
};
 
ko.applyBindings(new CarnetViewModel());
</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\UsersRequest') !!}

@endsection
