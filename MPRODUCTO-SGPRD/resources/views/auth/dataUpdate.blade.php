@extends('layouts.appLogin')

@section('contenido')

    <div class="middle-box text-center loginscreen animated fadeInDown">

        <div>
            @include('success')
            <p>Confirme su dirección actual

            </p>
            {!! Form::model($comercio, ['class'=>'form-horizontal','method' => 'PATCH','route' => ['address.update', $comercio[0]->fk_id_comercio,]]) !!}
                                        {{ csrf_field() }}
            <div class="row">
              <div class="col-md-12">
                <div class="form-group ">
                  <label for="ejemplo_password_3" class="col-lg-2 control-label">Estado:</label>
                      <select class="form-control" id="esta" name="estad" required>
                        <option value="" disabled selected>Seleccione</option>
                      </select>
                </div>
              </div>
            </div>

             <div class="row">
                <div class="col-md-12">
                <div class="form-group ">
                  <label for=""  class="col-lg-2 control-label">Ciudad</label>
                  <input class="form-control" type="text" name="ciudad" id="ciudad" required pattern="[A-Za-z ]{3,35}" maxlength="35" placeholder="Ciudad">
                </div>
              </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                <div class="form-group ">
                  <label for=""  class="col-lg-2 control-label">Dirección</label>
                  <input class="form-control" type="text" name="direccion" id="direccion" required maxlength="100" placeholder="nombre del Usuario">
                </div>
              </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                <div class="form-group ">
                  <label for=""  class="col-lg-2 control-label">Calle/Av</label>
                  <input class="form-control" type="text" name="calle" id="calle" required maxlength="50" placeholder="Calle / Av">
                </div>
              </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                <div class="form-group ">
                  <label for=""  class="col-lg-2 control-label">Casa/Edificio/Torre</label>
                  <input class="form-control" type="text" name="casa" id="casa" required  maxlength="50" placeholder="Casa/Edificio/Torre">
                </div>
              </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                <div class="form-group "  class="col-lg-2 control-label">
                  <label for=""  class="col-lg-2 control-label">Local/Oficina</label>
                  <input class="form-control" type="text" name="local" id="local" required maxlength="50" placeholder="Local/Oficina">
                </div>
              </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                <div class="form-group ">
                  <label for=""  class="col-lg-2 control-label">Urb. Sector</label>
                  <input class="form-control" type="text" name="sector" id="sector" required maxlength="50" placeholder="Urb. Sector">
                </div>
              </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                <div class="form-group ">
                  <label for=""  class="col-lg-2 control-label">Teléfono</label>
                  <input class="form-control" type="text" name="telefono" id="telefono" required pattern="[0-9]{10,11}" maxlength="12" placeholder="Teléfono">
                </div>
              </div>
            </div>

            <div class="form-check">
                <input type="checkbox" name="checkDir" class="form-check-input" id="check">
                <label class="form-check-label" for="exampleCheck1">Mantener Dirección</label>
             </div>

                
                <button type="submit" id="act" class="btn btn-primary block full-width m-b">Enviar</button>

                
              {!!Form::close()!!}
            <p class="m-t"> <small>Meritop C.A. &copy; 2018</small> </p>
        </div>
    </div>

@endsection

@section('script')
<script type="text/javascript">
    

    $(document).ready(function(){
         var comercio = {!! json_encode($comercio) !!}; 

        
            $('#check').on('click', function(){
                 
                if($('#check').prop('checked') ) {

                    $("#esta").attr('disabled', true);  
                    $("#ciudad").attr('disabled', true);
                    $("#direccion").attr('disabled', true);
                    $("#calle").attr('disabled', true);
                    $("#casa").attr('disabled', true);
                    $("#local").attr('disabled', true);
                    $("#sector").attr('disabled', true);
                    $("#telefono").attr('disabled', true);
                   
                    
                }else{

                   $("#esta").attr('disabled', false);  
                    $("#ciudad").attr('disabled', false);
                    $("#direccion").attr('disabled', false);
                    $("#calle").attr('disabled', false);
                    $("#casa").attr('disabled', false);
                    $("#local").attr('disabled', false);
                    $("#sector").attr('disabled', false);
                    $("#telefono").attr('disabled', false);
                }
            });
               

        $.get( "{{URL('/estados')}}",function(data){

        for(var i=0; i<data.length; i++){                  
            $("#esta").append('<option value="'+data[i].id+'">'+data[i].nombre+'</option>');
        } 
        $("#esta").val(comercio[0].estado);           
        });
      
           
                    
            $("#ciudad").val(comercio[0].ciudad);
            $("#direccion").val(comercio[0].direccion);
            $("#calle").val(comercio[0].calle_av);
            $("#casa").val(comercio[0].casa_edif_torre);
            $("#local").val(comercio[0].local_oficina);
            $("#sector").val(comercio[0].urb_sector);
            $("#telefono").val(comercio[0].telefono1);  


        
    });
</script>
@endsection