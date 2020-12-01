<!DOCTYPE html>
<html>
<head>
	<title></title>
	 <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
</head>
<body>
	<style>

.centerImg {
  display: block;
  margin-left: auto;
  margin-right: auto;
  width: 180px; 
  height: 61px;
  margin-top: 40px;
}

</style>
<div class="container">
	<div class="row">
		<div>
			<img alt="Banplus" class="centerImg" src="{{asset('img/banplus.png')}}"/>
		</div>
		<br>
		<br>
		<div class="col-md-12" align="center">
			@if($estatus == 'ok')
          		<h4>Estimado cliente, su transacción ha sido aprobada exitosamente</h4>
          		<br>
          		<img alt="" src="{{asset('img/cheque.png')}}"/>   
          		<br>
          		<br>
				@if($es_giftcard)
					<h5><b><i>Para m&aacute;s informaci&oacute;n puede escribirnos a giftcard@banplus.com</i></b></h5>
				@else
					<h5><b><i>Le invitamos a seguir disfrutando de la atención, productos y servicios que le brinda Banplus. Si desea contactarnos puede hacerlo a través del Centro de Atención President’s Club, vía WhatsApp +58 412 – Banplus (2267587), con una llamada al +58 212 909.20.03 o escríbanos al correo electrónico: presidentsclub@banplus.com</i></b></h5>
				@endif
				
				<p>&nbsp;</p>
    		@else
				@if($estatus == 'error')
					<h4>Estimado cliente, su transacción ha sido rechazada</h4>
					<br>
					<img alt="" src="{{asset('img/error.png')}}"/>   
					<br>
					<br>
					@if($es_giftcard)
						<h5><b><i>Para m&aacute;s informaci&oacute;n puede escribirnos a giftcard@banplus.com</i></b></h5>
					@else
						<h5><b><i>Si desea contactarnos puede hacerlo a través del Centro de Atención President’s Club, vía WhatsApp +58 412 – Banplus (2267587), con una llamada al +58 212 909.20.03 o escríbanos al correo electrónico: presidentsclub@banplus.com</i></b></h5>
					@endif										
					<p>&nbsp;</p>
				@else
					@if($estatus == 'InProgress')
						<h4>Estimado cliente, su transacción se encuentra en proceso de autorización.</h4>
						<br>
						<img alt="" src="{{asset('img/error.png')}}"/>   
						<br>
						<br>            
						<h5><b><i>Si desea contactarnos puede hacerlo a través del Centro de Atención President’s Club, vía WhatsApp +58 412 – Banplus (2267587), con una llamada al +58 212 909.20.03 o escríbanos al correo electrónico: presidentsclub@banplus.com</i></b></h5>
						<p>&nbsp;</p>
					@else
						<h4>Estimado cliente, la URL que intentas utilizar es incorrecta o ya no es válida.</h4>
						<br>
						<img alt="" src="{{asset('img/404.png')}}"/>   
						<br>
						<br>
						@if($es_giftcard)
							<h5><b><i>Para m&aacute;s informaci&oacute;n puede escribirnos a giftcard@banplus.com</i></b></h5>
						@else
							<h5><b><i>Si desea contactarnos puede hacerlo a través del Centro de Atención President’s Club, vía WhatsApp +58 412 – Banplus (2267587), con una llamada al +58 212 909.20.03 o escríbanos al correo electrónico: presidentsclub@banplus.com</i></b></h5>
						@endif										
						<p>&nbsp;</p>						
					@endif					
				@endif
			@endif
		</div>
	</div>
</div>
</body>
</html>






