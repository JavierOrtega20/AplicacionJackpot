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
                <h2>E-commerce grid</h2>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.html">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a>E-commerce</a>
                    </li>
                    <li class="breadcrumb-item active">
                        <strong>Products grid</strong>
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
                            <h5>1. Ingrese monto de la GiftCard</h5>
                        </div>
						
                        <div class="ibox-content">


                            <div class="row">
									<div class="col-md-5">

                                    <div class="product-images">
                                        <div>
											<img src="{!!asset('img/'.$gift->imagen)!!}" class="img_gift_card">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
									<form method="POST" action="{{ route('gift.step3') }}" method="POST" enctype="multipart/form-data">
									{{ csrf_field() }}
										<input id="cod_emisor" name="cod_emisor" type="hidden" value="{{ $gift->cod_emisor }}">
										<h2 class="font-bold m-b-xs">
											{{ $gift->nombregift }}
										</h2>
										<small>{{ $gift->lema }}</small>
										<hr>
										<div class="m-t-md">								
											<div class="row">
													  <div class="form-group"><label class="col-sm-2 product-main-price">Monto {{ $gift->mon_simbolo }}: </label>
														  <div class="col-sm-10">
															 <input type="text" name="monto" class="form-control input-lg m-b" required placeholder="" onkeypress="return justNumbers(event);" onblur="format(this)">
														  </div>
													  </div>
													  <div class="form-group"><label class="col-sm-2 control-label">Cédula: </label>
													  <div class="col-sm-10">
														 <div class="col-sm-2">
															  <select class="form-control  input-lg m-b" name="nacionalidad" required style="width: 140%; margin-left: -30%;">
																<option value=""></option>
																<option value="V">V</option>
																<option value="E">E</option>
																<option value="P">P</option>
															  </select>
														  </div>
														  <div class="input-group m-b col-sm-10">
															   <input type="text" name="cedula" class="form-control input-lg m-b" required placeholder="" maxlength="10" onkeyup="this.value=Numero(this.value)">
															   <span class="form-text m-b-none">Cédula del cliente que envía la GiftCard.</span>
														  </div>
													  </div>
												  </div>													  
											</div>
										</div>
										<hr>

										<h4>Descripción del Producto</h4>

										<div class="small text-muted">
											{{ $gift->descripcion }}
										</div>
										<hr>

										<div>
											<div class="btn-group">
												<a href="{{ url('gift/list') }}" class="btn btn-white btn-sm"><i class="fa fa-arrow-circle-o-left"></i> Cancelar</a>
												<button class="btn btn-primary btn-sm" type="submit"><i class="fa fa-arrow-circle-o-right"></i> Siguiente</button>
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
@endsection	
@section('scripts')

<script type="text/javascript">
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
	
</script>
@endsection	