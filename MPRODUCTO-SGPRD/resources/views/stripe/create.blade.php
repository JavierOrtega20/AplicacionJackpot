@extends('layouts.app')
@section('titulo')
Autorización con Tarjeta Internacional
@endsection
@if($rol == 6)
<style>
 .perfil2,.perfil3,.perfil4,.perfil6{display:none;}
</style>
@else
<style>
 .perfil2,.perfil3,.perfil4,.perfil5,.perfil6{display:none;}
 .StripeElement {
    background-color: #FFFFFF;
    background-image: none;
    border: 1px solid #e5e6e7;
    border-radius: 1px;
    color: inherit;
    display: block;
    padding: 10px 12px;
    transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;
    width: 81.4% !important;
    margin-bottom: 15px !important;
    margin-left: 15px;

}
</style>
@endif
@section('contenido')

{!! Form::open(array('route' => 'Stripe.payment','method'=>'POST','class'=>'form-horizontal' ,'id'=>'payment-form')) !!}
<input id="giftcard" name="giftcard" type="hidden" value="{{ $giftcard }}">
<input id="cedula_giftcard" name="cedula_giftcard" type="hidden" value="{{ $cedula_giftcard }}">
<input id="nacionalidad_giftcard" name="nacionalidad_giftcard" type="hidden" value="{{ $nacionalidad_giftcard }}">
<input id="total_monto_giftcard" name="total_monto_giftcard" type="hidden" value="{{ $total_monto_giftcard }}">
<input id="fk_dni_recibe" name="fk_dni_recibe" type="hidden" value="{{ $fk_dni_recibe }}">
<input id="fk_carnet_id_recibe" name="fk_carnet_id_recibe" type="hidden" value="{{ $fk_carnet_id_recibe }}">
<input id="monto_original" name="monto_original" type="hidden" value="{{ $monto_original }}">
<input id="comision_monto" name="comision_monto" type="hidden" value="{{ $comision_monto }}">
<input id="dias_vencimiento" name="dias_vencimiento" type="hidden" value="{{ $dias_vencimiento }}">
<input id="giftcard_id" name="giftcard_id" type="hidden" value="{{ $giftcard_id }}">
<input id="giftcard_imagen" name="giftcard_imagen" type="hidden" value="{{ $giftcard_imagen }}">
<div class="row wrapper border-bottom white-bg page-heading">
  <div class="col-lg-8">
    <h2><i class="fa fa-diamond"></i>
      Autorización con Tarjeta Internacional
    </h2>
    <ol class="breadcrumb">
      <li>
        <a href="{{ url('home') }}">Panel</a>
      </li>
      <li>
        Autorización con Tarjeta Internacional
      </li>
      <li class="active">
        <strong>Nueva autorización</strong>
      </li>
    </ol>
  </div>
  <div class="col-lg-4">
    <div class="title-action">
      <a href={{route('transacciones.index')}} class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
      <button type="submit" class="btn btn-primary aut" id="form-validation"><span class="btn-label">
        <i class="fa fa-check"></i>
      </span>Autorizar</button>
    </div>
  </div>

</div>

<div class="wrapper wrapper-content animated fadeInRight ecommerce">
  <div class="row">
    <div class="col-lg-12">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5>Autorizar Transacción
          </h5>
        </div>
        <div class="ibox-content">
		<div class="MessageDiv">@include('flash::message')</div>
           

          <div class="hr-line-dashed"></div>
          @if ($rol==4)
          <div class="form-group">
            <label class="col-sm-2 control-label">Comercios <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <select class="form-control select2 input-lg m-b" id="fk_id_comercio" name="fk_id_comercio" style="width: 100%;">
                  <option value="">Seleccione un comercio</option>
                    @foreach($comercios as $element)
                      <option value="{{ $element->id }}">{{ $element->descripcion }}</option>
                    @endforeach
                </select>
                <div id="msgComercio" class="text-danger" ></div>
              </div>
          </div>
          @endif

          <div class="form-group">
            <label class="col-sm-2 control-label">Número de Cédula <span class="text-danger">*</span></label>
            <div class="col-sm-10">
              <div class="col-sm-2">                  
					@if($giftcard == 1)
						<select class="form-control input-lg m-b" readonly name="nacionalidad" required style="width: 120%; margin-left: -9%;">
							<option value="{{ $nacionalidad_giftcard }}">{{ $nacionalidad_giftcard }}</option>		
						</select>
					@else
						<select class="form-control  input-lg m-b" name="nacionalidad" required style="width: 120%; margin-left: -9%;">
							<option value="">Seleccione</option>
							<option value="V">V</option>
							<option value="E">E</option>
							<option value="P">P</option>			
						</select>
					@endif					                    
              </div>
              <div class="input-group m-b col-sm-10">
					@if($giftcard == 1)
					  <input type="text" placeholder="Cédula" readonly name="cedula" id="cedula" class="form-control input-lg m-b" maxlength="10" required value = "{{ $cedula_giftcard }}">
					  <div id="msm" class="text-danger"></div>				
					@else
					  <input type="text" placeholder="Cédula" name="cedula" id="cedula" class="form-control input-lg m-b" maxlength="10" onkeyup="validarCedula(this)" onkeypress="return justNumbers(event);" required>
					  <div id="msm" class="text-danger"></div>				
					@endif			                  
                <div id="msgCedula" class="text-danger" ></div>
              </div>
            </div>
          </div>

          <div class="showBlock hideShow">

            <div class="form-group">
              <label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                {!! Form::text('first_name', null, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b nombre', 'maxlength'=>20,'onkeyup'=>'this.value=Text(this.value)','id'=>'nombre' )) !!}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">Apellido <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                {!! Form::text('last_name', null, array('placeholder' => 'Apellido','class' => 'form-control input-lg m-b apellido', 'maxlength'=>20 ,'onkeyup'=>'this.value=Text(this.value)', )) !!}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">Correo Electrónico<span class="text-danger">*</span></label>
              <div class="col-sm-10">
                {!! Form::text('email', null, array('id'=>'email', 'placeholder' => 'Correo Electrónico','class' => 'form-control input-lg m-b correo', 'onkeyup'=> 'validarEmail(this)', 'maxlength'=>50, )) !!}
                  <div id="msgEmail" class="text-danger" ></div>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">Número Telefónico<span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <div class="col-sm-2">
                  {{ Form::select('cod_tel',[
                    '58412' => '0412',
                    '58414' => '0414',
                    '58424' => '0424',
                    '58416' => '0416',
                    '58426' => '0426',
                    ], null, ['class' => 'form-control input-lg m-b','style'=>'width: 120%; margin-left: -9%;', 'placeholder'=>'Seleccione','id'=> 'cod_tel', ])
                  }}                    
                </div>
                <div class="input-group m-b col-sm-10">
                  {!! Form::text('num_tel', null, array('placeholder' => 'Número Telefónico','id'=> 'num_tel','class' => 'form-control input-lg m-b telefono', 'maxlength'=>7, )) !!}
                </div>
              </div>
            </div>

          </div>

          <div class="hr-line-dashed"></div>
          <div class="form-group">
            <label class="col-sm-2 control-label">Número de Tarjeta<span class="text-danger">*</span></label>
            <div class="col-sm-10 " id="card-element"></div>
           <div class="col-md-offset-2 text-danger" id="card-errors" role="alert"></div>   
          </div>

        
          <div class="form-group">
            <label class="col-sm-2 control-label">Monto $ <span class="text-danger">*</span></label>
            <div class="col-sm-10">
			@if($giftcard == 1)
              <input type="text" placeholder="Monto en $" readonly name="amount" id="monto" class="form-control input-lg m-b monto" maxlength="21", required value="{{ str_replace('.', ',',$total_monto_giftcard) }}">
              <div id="msm" class="text-danger"></div>				
			@else
              <input type="text" placeholder="Monto en $" name="amount" id="monto" class="form-control input-lg m-b monto" onkeypress="return justNumbers(event);"  data-thousands="." data-decimal="," autocomplete="off" pattern="[0-9,.]{1,21}" maxlength="21", required>
              <div id="msm" class="text-danger"></div>				
			@endif
            </div>
          </div>

          <div class="hr-line-dashed"></div>
          <div class="form-group">
            <div class="title-action">
              <a href="{{url('list/monedas')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
              <button type="submit" class="btn btn-primary aut" id="form-validation"><span class="btn-label"><i class="fa fa-check"></i>
              </span>Autorizar
            </button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.js"></script>


<script type="text/javascript">
  $('.select2').select2();
</script>
@if($rol != 6)
<script type="text/javascript">
  $(".perfil5").remove();
</script>
@endif
<!-- validacion de creacion exitosa -->
@if(session('status')=='ok')
<script type="text/javascript">
  swal({
    title: "Aprobado!",
    text: "Operacion Exitosa",
    type: "success",
    confirmButtonText: "Cerrar" 
  });
</script>
@endif
<!-- validacion error llave duplicada -->
@if(session('status')=='duplicado')
<script type="text/javascript">
  swal("Error", "La divisa que intenta crear ya se encuentra registrada, por favor verifique los datos ingresados", "error");
</script>
@endif
<!-- validacion de creacion exitosa -->
@if(session('status')=='error1')
<script type="text/javascript">
  swal("Error", "Comuniquese con el Administrador del Sistema, Cargo No Autorizado", "error");
</script>
@endif

@if(session('status')=='error2')
<script type="text/javascript">
  swal("Error", "Comuniquese con el Administrador del Sistema,  Usuario No Existente", "error");
</script>
@endif

@if(session('status')=='error3')
<script type="text/javascript">
  swal("Error", "Comuniquese con el Administrador del Sistema", "error");
</script>
@endif

<script>
$(document).ready(function() {
    setTimeout(function() {
		// Declaramos la capa mediante una clase para ocultarlo
        $(".MessageDiv").fadeOut(3000);
    },30000);
});
$(document).ready(function(){
	//VALIDAR SI SE TRATA DE UNA GIFTCARD
	if('{{ $giftcard }}' == '1')
	{
        $(".showBlock").hide();
        $('.nombre').attr('required', false);
        $('.apellido').attr('required', false);
        $('.correo').attr('required', false);
        $('.telefono').attr('required', false);		
	}
	
 jQuery("#payment-form").validate({
    rules: {
      nacionalidad: "required",
      cedula: "required",
      first_name: "required",
      last_name: "required",
      amount: "required",
      email: "required",
      cod_tel: "required",
      num_tel: "required",
    },
    messages: {
      nacionalidad: "Por favor ingrese los datos requeridos",
      cedula: "Por favor ingrese los datos requeridos",
      first_name: "Por favor ingrese los datos requeridos",    
      last_name: "Por favor ingrese los datos requeridos",    
      amount: "Por favor ingrese los datos requeridos",
      email: "Por favor ingrese los datos requeridos",
      cod_tel: "Por favor ingrese los datos requeridos",
      num_tel: "Por favor ingrese los datos requeridos",                              
    }
  });
 

//FORMATO PARA EL MONTO KEYUP
$(".monto").on('keyup', function (event){
  var value = $(this).val();   var montoIngresado = value.split('.').join('');
  montoIngresado = montoIngresado.replace(',', '.');
  var A = parseFloat(montoIngresado);
  var B = parseFloat("{{ config('webConfig.MontoMinimo') }}");
  if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105)) {

     if (A < B){
      $('#msm').html("Monto inválido.Por favor introduzca un valor superior o igual a $"+ "{{ config('webConfig.MontoMinimo') }}", "value")
      $('.aut').attr('disabled', true);
      $("#monto").maskMoney({ allowEmpty: true })
      return false;
    }else {
      $('#msm').html("", "value")
      $('.aut').attr('disabled', false);
    }
  }
  if (event.keyCode === 8) {
    if (A < B) {                           
      $('#msm').html("Monto inválido.Por favor introduzca un valor superior o igual a $" + "{{ config('webConfig.MontoMinimo') }}", "value")
      $('.aut').attr('disabled', true);
      $("#monto").maskMoney({ allowEmpty: true })
    } else {
      $('#msm').html("", "value");
      $('.aut').attr('disabled', false);
    }
  }else{
      $("#monto").maskMoney({ allowEmpty: true });
      return false;
  }
  $(".monto").maskMoney({ allowEmpty: true});
}).keyup();



});//document ready

 function justNumbers(e){
    var keynum = window.event ? window.event.keyCode : e.which;
    if ((keynum == 8) || (keynum == 46) || (keynum == 44))
      return true;
    return /\d/.test(String.fromCharCode(keynum));
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

var globalTimeout = null;
function validarCedula(e){
  if (globalTimeout != null){
    clearTimeout(globalTimeout);
  }
  globalTimeout = setTimeout(function() {
    globalTimeout = null;
    var dni = $('#cedula').val(); 
    $.get("checkCliente/"+dni+'/', function(data){
      if(data.data){
        $(".showBlock").hide();
        $('.nombre').attr('required', false);
        $('.apellido').attr('required', false);
        $('.correo').attr('required', false);
        $('.telefono').attr('required', false);

      }else{
        $(".showBlock").show();
        $('.nombre').attr('required', 'required');
        $('.apellido').attr('required', 'required');
        $('.correo').attr('required', 'required');
        $('.telefono').attr('required', 'required');
      }
    });
  }, 400); 
}//END FUNCTION VALIDAR CEDULA CLIENTE

//VALIDACION DE CORREO ELECTRONICO
  function validarEmail(e){
     if (globalTimeout != null){
    clearTimeout(globalTimeout);
  }
  
            var url = window.location;

            var pat = /Stripe/;

            if(pat.test(url) == true){
              url=String(url);

              url=url.replace("/Stripe",'');
            }       
        globalTimeout = setTimeout(function() {
              globalTimeout = null;
              var email = $("#email").val();
              
                $.get(url + "/users/checkEmail/"+email+'/', function(res){
                  if(res == true){
                    $("#msgEmail").html('El Correo Electrónico '+ email+' que ha ingresado ya está en uso.');
                    $("#email").val('');
                   
                  }else{
                    $("#msgEmail").html("");
                  }
                });
        }, 400);
  }
//FINALIZAR LA VALIDACION DE CORREO ELECTRONICO

// Set your publishable key: remember to change this to your live publishable key in production
// See your keys here: https://dashboard.stripe.com/account/apikeys
var stripe = Stripe("{{ config('services.stripe.key') }}", {locale: 'es-419'});
var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
var style = {
  base: {
    fontSize: '18px',
  },
};

// Create an instance of the card Element.
var card = elements.create('card', {style: style});

// Add an instance of the card Element into the `card-element` <div>.
card.mount('#card-element');

// Create a token or display an error when the form is submitted.
var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event){
  event.preventDefault();
  var nForm = $("#payment-form").validate();
  nForm.form();

  if(nForm.valid()===true){
    stripe.createToken(card).then(function(result){
      if (result.error){
        // Inform the customer that there was an error.
        var errorElement = document.getElementById('card-errors');
        errorElement.textContent = result.error.message;
      }else{
        // Send the token to your server.
        stripeTokenHandler(result.token);
      }
    });    
  }else{
    return false;
  }  
});

function stripeTokenHandler(token) {
// Insert the token ID into the form so it gets submitted to the server
var form = document.getElementById('payment-form');

var hiddenInput = document.createElement('input');
hiddenInput.setAttribute('type', 'hidden');
hiddenInput.setAttribute('name', 'stripeToken');
hiddenInput.setAttribute('value', token.id);
form.appendChild(hiddenInput);
// Submit the form
form.submit();
}

</script>

@endsection
