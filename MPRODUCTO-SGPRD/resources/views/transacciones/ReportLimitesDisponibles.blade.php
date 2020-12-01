@extends('layouts.app')
@section('titulo', 'Transacciones')

@section('contenido')

<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-8">
		<h2><i class="fa fa-credit-card"></i> Reporte de Limites y Disponibles</h2>
		<ol class="breadcrumb">
			<li>
				<a href="{{ url('home') }}">Panel</a>
			</li>
			<li>Reporte de Limites y Disponibles
			</li>
			<li class="active">
				<strong>Exportar</strong>
			</li>
		</ol>
	</div>
	<div class="col-lg-4">
		<div class="title-action">

		</div>
	</div>

</div>
@include('flash::message')

<div class="wrapper wrapper-content ecommerce">
	<div class="wrapper wrapper-content ecommerce">



	 <div class="row">
		<div class="col-md-12">
			<div class="ibox">
				 <div class="ibox-content">
				 	<h2>

				 		<span class="text-navy">Limites y Disponibles</span>
						<span class="text-navy col-md-offset-8" hidden>
				 			<a href="{{ url('descarga/LimitesDisponibles') }}" class="btn btn-primary" id="descargar">
                			<i class="fa fa-book"></i>
                				Descargar
                			</a>
				 		</span>

				 	</h2>

					<div id="preloader">
						<img class="blink" src="{{asset('img/banplus.png')}}" alt=""/>
					</div>

				 </div>

			</div>
		</div>
	</div>

</div>
</div>
@stop

@section('scripts')

<!-- Page-Level Scripts -->
<script>


	function Text(string){//solo letras
		    var out = '';
		    //Se añaden las letras validas
		    var filtro = 'abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ ';//Caracteres validos

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
	$(document).ready(function() {

		window.location = $("#descargar").attr("href");

				setTimeout(function() {
					$("#preloader").hide();
				}, 25000);

        $('#tables').DataTable({
              responsive: true,
              "language": idioma,
            });

		$('#fecha_desde').datepicker({
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

		 $('.footable').footable();
	});


	$(document).ready(function() {
	    $('#customers').DataTable( {
	        "scrollX": true,
	        "paginate":false,
	        "searching":false,
	        "info":     false
	    } );
	} );

</script>
<!-- end page js -->
@endsection
