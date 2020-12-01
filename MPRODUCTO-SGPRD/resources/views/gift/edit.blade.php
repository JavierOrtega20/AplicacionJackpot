@extends('layouts.app')
@section('titulo', 'GiftCard')        
@section('contenido')
@php
	use App\Models\User;
	
	$user= User::find(Auth::user()->id);
	$roles= $user->roles;
	$rol = null;
	foreach ($roles as $value) {
		$rol = $value->id;
	}
@endphp	
		<style>
			.img_gift_card {
			  width: 200px;
			  height: 124px;
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
                        <strong>Crear</strong>
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
                            <h5>Crear GiftCard</h5>
                        </div>						
                        <div class="ibox-content">
							<form method="POST" action="{{ route('gift.update') }}" enctype="multipart/form-data" class="form-horizontal" id="formGiftCard">
							<input id="filename" name="filename" type="hidden" value="{{ $gift->imagen }}">
							<input id="ExitsImage25" name="ExitsImage25" type="hidden" value="{{ $imagen25 != null ? $imagen25->nombre_imagen : 0 }}">
							<input id="ExitsImage50" name="ExitsImage50" type="hidden" value="{{ $imagen50 != null ? $imagen50->nombre_imagen : 0 }}">
							<input id="ExitsImage100" name="ExitsImage100" type="hidden" value="{{ $imagen100 != null ? $imagen100->nombre_imagen : 0 }}">
							<input id="ExitsImage200" name="ExitsImage200" type="hidden" value="{{ $imagen200 != null ? $imagen200->nombre_imagen : 0 }}">
							<input id="ExitsImageOtros" name="ExitsImageOtros" type="hidden" value="{{ $imagenOtros != null ? $imagenOtros->nombre_imagen : 0 }}">
							
							<input id="id" name="id" type="hidden" value="{{ $gift->id }}">
							{{ csrf_field() }}
								<div class="row">
									<div class="col-lg-12">
									  <div class="hr-line-dashed"></div>
									  <div class="form-group"><label class="col-sm-2 control-label">Comercio Emisor<span class="text-danger">*</span></label>
										  <div class="col-sm-10">
												<select class="form-control select2 input-lg m-b" id="comercio_emisor" disabled name="comercio_emisor">
													<option value="">Seleccione</option>
													@foreach($comercios as $element)
														@if($element->rif == $gift->rif)
															<option value="{{ $element->rif }}" selected>({{ $element->rif }}) {{ $element->descripcion }}</option>
														@else
															<option value="{{ $element->rif }}">({{ $element->rif }}) {{ $element->descripcion }}</option>
														@endif														
													@endforeach
												</select>										  
										  </div>
									  </div>									  
									  <div class="hr-line-dashed"></div>
									  <div class="form-group"><label class="col-sm-2 control-label">Nombre de la GiftCard<span class="text-danger">*</span></label>
										  <div class="col-sm-10">
											 {!! Form::text('nombre', $gift->nombre, array('placeholder' => 'Nombre de la GiftCard','id'=> 'nombre','class' => 'form-control input-lg m-b','maxlength' =>'200')) !!}
										  </div>
									  </div>
									  <div class="hr-line-dashed"></div>
									  <div class="form-group"><label class="col-sm-2 control-label">Descripción<span class="text-danger">*</span></label>
										  <div class="col-sm-10">
											 {!! Form::text('descripcion', $gift->descripcion, array('placeholder' => 'Descripción','id'=> 'descripcion','class' => 'form-control input-lg m-b','maxlength' =>'200')) !!}
										  </div>
									  </div>
									  <div class="hr-line-dashed"></div>
									  <div class="form-group"><label class="col-sm-2 control-label">Lema Comercial</label>
										  <div class="col-sm-10">
											 {!! Form::text('lema_comercial', $gift->lema, array('placeholder' => 'Lema Comercial','id'=> 'lema_comercial','class' => 'form-control input-lg m-b','maxlength' =>'200')) !!}
										  </div>
									  </div>	
									  <div class="hr-line-dashed"></div>
									  <div class="form-group"><label class="col-sm-2 control-label">Monto mínimo de venta GiftCard<span class="text-danger">*</span></label>
										  <div class="col-sm-10">
											 {!! Form::text('monto_minimo', str_replace('.', ',', $gift->monto_minimo), array('placeholder' => 'Monto mínimo de venta GiftCard','id'=> 'monto_minimo', 'class' => 'form-control input-lg m-b','maxlength' =>'6')) !!}
										  </div>
									  </div>
									  <div class="hr-line-dashed"></div>
									  <div class="form-group"><label class="col-sm-2 control-label">Vencimiento en días:<span class="text-danger">*</span></label>
										  <div class="col-sm-10">
											 {!! Form::text('dias_vencimiento', $gift->dias_vencimiento, array('placeholder' => 'Vencimiento en días','id'=> 'dias_vencimiento','class' => 'form-control input-lg m-b','onkeyup' => 'this.value=Numero(this.value)','maxlength' =>'3')) !!}
										  </div>
									  </div>
									  <div class="hr-line-dashed"></div>
									  <div class="form-group"><label class="col-sm-2 control-label">Comisión aplica:<span class="text-danger">*</span></label>
											<div class="col-sm-3">
											<label class="radio inline">
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ Form::radio('paga_comision', 'cliente' , $gift->paga_comision == 2 ? true : false) }}
												Cliente
											</label>
											</div>
											<div class="col-sm-3">
											<label class="radio inline">
												{{ Form::radio('paga_comision', 'comercio' , $gift->paga_comision == 1 ? true : false) }}
												Comercio
											</label>
										  </div>
									  </div>										  
									  <div class="hr-line-dashed"></div>
									  <div class="form-group">
										  <label class="col-sm-2 control-label">Comisión %<span class="text-danger">*</span></label>
										  <div class="col-sm-4">
											@if($rol == 3)
												{!! Form::text('p_comision', str_replace('.', ',', $gift->tasa_comision), array('placeholder' => 'Comisión %','id'=> 'p_comision','class' => 'form-control input-lg m-b', 'readonly' => 'readonly','maxlength' =>'6')) !!}
											@else
												{!! Form::text('p_comision', str_replace('.', ',', $gift->tasa_comision), array('placeholder' => 'Comisión %','id'=> 'p_comision','class' => 'form-control input-lg m-b' ,'maxlength' =>'6')) !!}
											@endif											 
										  </div>
										  <label class="col-sm-2 control-label">Comisión Fija<span class="text-danger">*</span></label>
										  <div class="col-sm-4">
											@if($rol == 3)
												{!! Form::text('m_comision_fijo', str_replace('.', ',', $gift->monto_fijo), array('placeholder' => 'Comisión Fija', 'readonly' => 'readonly','id'=> 'm_comision_fijo','class' => 'form-control input-lg m-b','maxlength' =>'6')) !!}
											@else
												{!! Form::text('m_comision_fijo', str_replace('.', ',', $gift->monto_fijo), array('placeholder' => 'Comisión Fija','id'=> 'm_comision_fijo','class' => 'form-control input-lg m-b','maxlength' =>'6')) !!}
											@endif											  											 
										  </div>										  
									  </div>
									  <div class="hr-line-dashed"></div>
										<div class="form-group">
										<label class="col-sm-2 control-label">Activar PIN</label>
										<div class="col-sm-10">
											  <div class="col-sm-1 text-right">
												 <div class="i-checks"><label> <input name="requiere_pin" {{ $gift->requiere_pin ? 'checked' : '' }} id="requiere_pin" type="checkbox" value="requiere_pin"> <i></i></label></div>												 
											  </div>
											  <div class="col-sm-11">
											  <div class="alert alert-info" role="alert">Cree un pin para proteger la venta de GiftCard de sus propios medios de pago. Cámbielo cuando desee.</div>   
											  </div>
										</div>
										</div>
										@if($gift->requiere_pin == true)
											<input id="clear_password" name="clear_password" type="hidden" value="1">
											  <div class="form-group" id="div_pin">
												<label class="col-sm-2  control-label ">
												  
												</label>
												<div class="col-sm-10">
												<input type="password" class="form-control input-lg m-b" placeholder="*****************" maxlength="6" name="pin" id="pin" onkeyup="this.value=Numero(this.value);" value="6dd42a">
												</div>
											  </div>
										@else
											<input id="clear_password" name="clear_password" type="hidden" value="1">
											  <div class="form-group" id="div_pin" style="display: none;">
												<label class="col-sm-2  control-label ">
												  
												</label>
												<div class="col-sm-10">
												<input type="password" class="form-control input-lg m-b" placeholder="*****************" disabled maxlength="6" id="pin" name="pin" onkeyup="this.value=Numero(this.value);">
												</div>
											  </div>
										@endif	  							  
									  <div class="hr-line-dashed"></div>
									  <div class="form-group">
										  <div class="col-sm-1">											 
										  </div>
										  <div class="col-sm-1 text-right">
											 <div class="i-checks"><label> <input name="CHECKg25" {{ $imagen25 != null ? 'checked' : '' }} id="CHECKg25" type="checkbox" value="25"> <i></i></label></div>
										  </div>
										  <div class="col-sm-2 text-left">
											<h2>25$</h2>
										  </div>										  
										  <div class="col-sm-4 text-left">
											 <input name="IMGg25" id="IMGg25" type="file" {{ $imagen25 != null ? '' : 'disabled' }} onchange="previewFile25(this);" class="archivo custom-file-input">
										  </div>
										  <div class="col-sm-4 text-left">
											 <img id="previewImg25" {{ $imagen25 != null ? '' : 'style=display:none' }} class="img_gift_card" src="{{ $imagen25 != null ? env('APP_URL').'/img/GiftCard/'.$imagen25->nombre_imagen : '' }}" alt="Placeholder">
										  </div>										  
									  </div>
									  <div class="hr-line-dashed"></div>
									  <div class="form-group">
										  <div class="col-sm-1">											 
										  </div>
										  <div class="col-sm-1 text-right">
											 <div class="i-checks"><label> <input name="CHECKg50" {{ $imagen50 != null ? 'checked' : '' }} id="CHECKg50" type="checkbox" value="50"> <i></i></label></div>
										  </div>
										  <div class="col-sm-2  text-left">
											<h2>50$</h2>
										  </div>										  
										  <div class="col-sm-4 text-left">
											 <input name="IMGg50" id="IMGg50" type="file" {{ $imagen50 != null ? '' : 'disabled' }} onchange="previewFile50(this);" class="archivo custom-file-input">
										  </div>
										  <div class="col-sm-4 text-left">
											 <img id="previewImg50" {{ $imagen50 != null ? '' : 'style=display:none' }} class="img_gift_card" src="{{ $imagen50 != null ? env('APP_URL').'/img/GiftCard/'.$imagen50->nombre_imagen : '' }}" alt="Placeholder">
										  </div>										  
									  </div>
									  <div class="hr-line-dashed"></div>
									  <div class="form-group">
										  <div class="col-sm-1">											 
										  </div>
										  <div class="col-sm-1 text-right">
											 <div class="i-checks"><label> <input name="CHECKg100" {{ $imagen100 != null ? 'checked' : '' }} id="CHECKg100" type="checkbox" value="100"> <i></i></label></div>
										  </div>
										  <div class="col-sm-2  text-left">
											<h2>100$</h2>
										  </div>										  
										  <div class="col-sm-4 text-left">
											 <input name="IMGg100" id="IMGg100" type="file" {{ $imagen100 != null ? '' : 'disabled' }} onchange="previewFile100(this);" class="archivo custom-file-input">
										  </div>
										  <div class="col-sm-4 text-left">
											 <img id="previewImg100" {{ $imagen100 != null ? '' : 'style=display:none' }} class="img_gift_card" src="{{ $imagen100 != null ? env('APP_URL').'/img/GiftCard/'.$imagen100->nombre_imagen : '' }}" alt="Placeholder">
										  </div>										  
									  </div>
									  <div class="hr-line-dashed"></div>
									  <div class="form-group">
										  <div class="col-sm-1">											 
										  </div>
										  <div class="col-sm-1 text-right">
											 <div class="i-checks"><label> <input name="CHECKg200" {{ $imagen200 != null ? 'checked' : '' }} id="CHECKg200" type="checkbox" value="200"> <i></i></label></div>
										  </div>
										  <div class="col-sm-2  text-left">
											<h2>200$</h2>
										  </div>										  
										  <div class="col-sm-4 text-left">
											 <input name="IMGg200" id="IMGg200" type="file" {{ $imagen200 != null ? '' : 'disabled' }} onchange="previewFile200(this);" class="archivo custom-file-input">
										  </div>
										  <div class="col-sm-4 text-left">
											 <img id="previewImg200" {{ $imagen200 != null ? '' : 'style=display:none' }} class="img_gift_card" src="{{ $imagen200 != null ? env('APP_URL').'/img/GiftCard/'.$imagen200->nombre_imagen : '' }}" alt="Placeholder">
										  </div>										  
									  </div>
									  <div class="hr-line-dashed"></div>
									  <div class="form-group">
										  <div class="col-sm-1">											 
										  </div>
										  <div class="col-sm-1 text-right">
											<div class="i-checks"><label> <input name="CHECKgOtros" id="CHECKgOtros" type="checkbox" value="Otros" value="Otros" disabled checked="true"> <i></i></label></div>
										  </div>
										  <div class="col-sm-2  text-left">
											<h2>Otros $</h2>
										  </div>										  
										  <div class="col-sm-4 text-left">
											 <input name="IMGgOtros" id="IMGgOtros" type="file" onchange="previewFileOtros(this);" class="archivo custom-file-input">
										  </div>
										  <div class="col-sm-4 text-left">
											 <img id="previewImgOtros" class="img_gift_card" src="{{ $imagenOtros != null ? env('APP_URL').'/img/GiftCard/'.$imagenOtros->nombre_imagen : '' }}" alt="Placeholder">
										  </div>										  
									  </div>									  
									  <div class="hr-line-dashed"></div>
										<div class="form-group">
											<div>
												<div class="title-action">
													@if($rol == 3)
														<a href="{{ url('home') }}" class="btn btn-white" ><i class="fa fa-times"></i> Cancelar </a>
													@else
														<a href="{{route('gift.index')}}" class="btn btn-white" ><i class="fa fa-times"></i> Cancelar </a>
													@endif
													<button type="button" class="btn btn-primary" id="form-validation" ><span class="btn-label">
																<i class="fa fa-check"></i>
															</span>Guardar</button>														
													<button type="submit" style="display:none" id="submit-form" ></button>													
												</div>												
											</div>												
										</div>								  
									</div>																
								</div>
							</form>
                        </div>						
					</div>
					<hr>
				</div>						
			</div>			
		</div>		
@endsection	
@section('scripts')
<script src="{!!asset('js/plugins/select2/js/select2.min.js')!!}"></script>
<script type="text/javascript">
  $('.select2').select2();
</script>
<script>
	$(document).ready(function () {
		$("#pin").click(function(){
			if($("#clear_password").val() == 1)
			{
				$("#pin").val("");
			}
		});
		
		$('.i-checks').iCheck({
			checkboxClass: 'icheckbox_square-green',
			radioClass: 'iradio_square-green',
		});
		
		$('input').on('ifChecked', function(event){
			if(event.target.defaultValue == 'requiere_pin')
			{
			  $("#div_pin").show(1000);
			  $("#div_pin").show("slow");
			  $("#pin").prop("disabled", false);			  
			}
			else{				
				$("#IMGg"+ event.target.defaultValue).val(null);
				$("#previewImg" + event.target.defaultValue).css("display", "none");				
			}
			
		});
		
		$('input').on('ifUnchecked', function(event){
			if(event.target.defaultValue == 'requiere_pin')
			{
				  $("#pin").prop("disabled", true);
				  $("#div_pin").hide(1000);
				  $("#div_pin").hide("fast");				
			}
			else{
			  $("#IMGg"+ event.target.defaultValue).prop("disabled", true);
			  $("#IMGg"+ event.target.defaultValue).val(null);
			  $("#previewImg" + event.target.defaultValue).css("display", "none");				
			}	
		});

		$("#form-validation").click(function(){
			
			if($("#formGiftCard").valid())
			{				
				
				if(!ValidateImages('25'))
				{
					return;
				}
				if(!ValidateImages('50'))
				{
					return;
				}
				if(!ValidateImages('100'))
				{
					return;
				}
				if(!ValidateImages('200'))
				{
					return;
				}
				
				var monto_maximo = '{{ $MontoMaximo }}'.replace(".", ",");

				//VALIDAR COMISION
				var p_comision = $("#p_comision").val();
				var p_comision_mont = "";
				
				if(p_comision){
				  p_comision = p_comision.split(".",10);
				  for(var i = 0;i<p_comision.length;i++){
					  p_comision_mont = p_comision_mont.concat(p_comision[i])
				  }
				}else{
					p_comision_mont = '0';
				}
				
				p_comision_mont = p_comision_mont.replace(",",".");
				p_comision = parseFloat(p_comision_mont);				
				
				if(p_comision > 100)
				{
					PrintMessage('La comisión no puede ser mayor al 100%');
					return;
				}
				
				//VALIDAR MONTO MINIMO
				var monto_minimo = $("#monto_minimo").val();
				var monto_minimo_mont = "";
				
				if(monto_minimo){
				  monto_minimo = monto_minimo.split(".",10);
				  for(var i = 0;i<monto_minimo.length;i++){
					  monto_minimo_mont = monto_minimo_mont.concat(monto_minimo[i])
				  }
				}else{
					monto_minimo_mont = '0';
				}
				
				monto_minimo_mont = monto_minimo_mont.replace(",",".");
				monto_minimo = parseFloat(monto_minimo_mont);				
				
				if(monto_minimo > monto_maximo)
				{
					PrintMessage('El monto mìnimo no puede superar el monto maximo permitido');
					return;
				}

				//VALIDAR COMISION FIJA
				var m_comision_fijo = $("#m_comision_fijo").val();
				var m_comision_fijo_mont = "";
				
				if(m_comision_fijo){
				  m_comision_fijo = m_comision_fijo.split(".",10);
				  for(var i = 0;i<m_comision_fijo.length;i++){
					  m_comision_fijo_mont = m_comision_fijo_mont.concat(m_comision_fijo[i])
				  }
				}else{
					m_comision_fijo_mont = '0';
				}
				
				m_comision_fijo_mont = m_comision_fijo_mont.replace(",",".");
				m_comision_fijo = parseFloat(m_comision_fijo_mont);				
				
				if(m_comision_fijo > monto_maximo)
				{
					PrintMessage('La comisión fija no puede superar el monto máximo permitido');
					return;
				}
				
				
				//Enviar el formulario
				$("#divLoading").show();
				$("#submit-form").click();				
			}							
		});
	});

	function ValidateImages(Monto)
	{
		if($("#ExitsImage" + Monto).val() == "0")
		{
			if($("#CHECKg" + Monto).prop("checked") == true)
			{
				var imgVal = $('#IMGg' + Monto).val();
				
				if(imgVal=='') 
				{ 
					PrintMessage('Por favor seleccione la imagen para el monto ' + Monto + '$');
					return false;
				}
			}				
		}

		return true;		
	}
</script>	
<script type="text/javascript">
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

	  function Numero(string){//solo numeros
		var out = '';
		//Se añaden los numeros validas
		var filtro = '1234567890';//Caracteres validos

		for (var i=0; i<string.length; i++)
		   if (filtro.indexOf(string.charAt(i)) != -1)
		   out += string.charAt(i);
		return out;
	  }	
	  
$("#monto_minimo").on({
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
$("#p_comision").on({
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
$("#m_comision_fijo").on({
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
</script>
<script>
    function previewFile25(input){
        var file = $("#IMGg25").get(0).files[0];
 
        if(file){
            var reader = new FileReader();
 
            reader.onload = function(){
                $("#previewImg25").attr("src", reader.result);
				$("#previewImg25").css("display", "block");
            }
 
            reader.readAsDataURL(file);
        }
		else{
			$("#previewImg25").css("display", "none");
		}
    }
    function previewFile50(input){
        var file = $("#IMGg50").get(0).files[0];
 
        if(file){
            var reader = new FileReader();
 
            reader.onload = function(){
                $("#previewImg50").attr("src", reader.result);
				$("#previewImg50").css("display", "block");
            }
 
            reader.readAsDataURL(file);
        }
		else{
			$("#previewImg50").css("display", "none");
		}
    }
    function previewFile100(input){
        var file = $("#IMGg100").get(0).files[0];
 
        if(file){
            var reader = new FileReader();
 
            reader.onload = function(){
                $("#previewImg100").attr("src", reader.result);
				$("#previewImg100").css("display", "block");
            }
 
            reader.readAsDataURL(file);
        }
		else{
			$("#previewImg100").css("display", "none");
		}
    }
    function previewFile200(input){
        var file = $("#IMGg200").get(0).files[0];
 
        if(file){
            var reader = new FileReader();
 
            reader.onload = function(){
                $("#previewImg200").attr("src", reader.result);
				$("#previewImg200").css("display", "block");
            }
 
            reader.readAsDataURL(file);
        }
		else{
			$("#previewImg200").css("display", "none");
		}
    }
    function previewFileOtros(input){
        var file = $("#IMGgOtros").get(0).files[0];
 
        if(file){
            var reader = new FileReader();
 
            reader.onload = function(){
                $("#previewImgOtros").attr("src", reader.result);
				$("#previewImgOtros").css("display", "block");
            }
 
            reader.readAsDataURL(file);
        }
		else{
			$("#previewImgOtros").css("display", "none");
		}
    }		
</script>
<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\GiftEditRequest') !!}

@endsection	