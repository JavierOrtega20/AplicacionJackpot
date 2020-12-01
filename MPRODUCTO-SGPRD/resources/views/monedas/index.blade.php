@extends('layouts.app')
 @section('titulo')
    Crear Divisa
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

{!! Form::open(array('route' => 'monedas.store','method'=>'POST','class'=>'form-horizontal')) !!}
 <div class="row wrapper border-bottom white-bg page-heading">
  <div class="col-lg-8">
    <h2><i class="fa fa-diamond"></i>
                              Monedas
                            </h2>
    <ol class="breadcrumb">
      <li>
      <a href="{{ url('home') }}">Panel</a>
      </li>
      <li>
        Monedas
      </li>
      <li class="active">
      <strong>Crear</strong>
      </li>
    </ol>
    </div>
    <div class="col-lg-4">
      <div class="title-action">
        <a href="{{url('list/monedas')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
        <button type="submit" class="btn btn-primary" id="form-validation"><span class="btn-label">
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
                            <h5>Crear Moneda
                            </h5>
                        </div>
                        <div class="ibox-content">

                            @include('error')
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Divisa <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                     {!! Form::text('divisa', null, array('placeholder' => 'Divisa','class' => 'form-control input-lg m-b', 'maxlength'=>20,'onkeyup'=>'this.value=Text(this.value)')) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Símbolo <span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    {!! Form::text('simbolo', null, array('placeholder' => 'Símbolo','class' => 'form-control input-lg m-b', 'maxlength'=>7 )) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>
                                  <div class="form-group"><label class="col-sm-2 control-label">Descripción<span class="text-danger">*</span></label>

                                  <div class="col-sm-10">
                                    {!! Form::textarea('descripcion', null, array('placeholder' => 'Descripción','class' => 'form-control input-lg m-b', 'rows'=> 3, 'maxlength'=>50)) !!}
                                  </div>
                              </div>
                              <div class="hr-line-dashed"></div>

                          <div class="hr-line-dashed"></div>
                          <div class="form-group">
                              <div class="title-action">
                                <a href="{{url('list/monedas')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
                                <button type="submit" class="btn btn-primary" id="form-validation"><span class="btn-label">
                                            <i class="fa fa-check"></i>
                                        </span>Crear</button>
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
<script type="text/javascript">
  $('.select2').select2();
</script>
@if($rolUser != 6)
<script type="text/javascript">
  $(".perfil5").remove();
</script>

@endif
<!-- validacion de creacion exitosa -->
@if(session('status')=='ok'))
<script type="text/javascript">
swal("Bien", "Creación Exitosa", "success");
</script>
@endif
<!-- validacion error llave duplicada -->
@if(session('status')=='duplicado'))
<script type="text/javascript">
swal("Error", "La divisa que intenta crear ya se encuentra registrada, por favor verifique los datos ingresados", "error");
</script>
@endif
<!-- validacion de creacion exitosa -->
@if(session('status')=='error'))
<script type="text/javascript">
swal("Error", "Comuniquese con el Administrador del Sistema", "error");
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
      });

    function validadoCarnet(){
            $("#msgCarnet").html("");
    }

</script>

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
</script>

<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\MonedasRequest') !!}

@endsection
