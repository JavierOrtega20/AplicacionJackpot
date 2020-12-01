@extends('layouts.app')
 @php
   if ($rolUser =='1'|| $rolUser =='2') {
      $user = "Usuarios";
   }else{
      $user = "Clientes";
   }
 @endphp
 @section('titulo')
    Lista de {{ $user }}
@endsection
@section('contenido')

        <div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-users"></i>
                {{ $user }}
            </h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>
                {{ $user }}
              </li>
              <li class="active">
              <strong>Listado</strong>
              </li>
            </ol>
            </div>
            @permission('user-create')
            <div class="col-lg-4">
              <div class="title-action">
                <!--<a href="{{ url('users/import') }}" class="btn btn-white"><i class="far fa-file-excel"></i> Importar </a>-->
                <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nuevo </a>
              </div>
          </div>
          @endpermission
        </div>

        <div class="wrapper wrapper-content ecommerce">
          @include('success')
          @if($rolUser =='6' || $rolUser =='1'  || $rolUser =='2')
          <div class="ibox-content m-b-sm border-bottom">
                <div class="row">
                 <form method="post" action=" {{ url('users') }} ">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" value="get">
                            <div class="panel-body">
                                <div class="form-inline" >
                                    <div class="form-group" >
                                        <label class="control-label" for="nombre">Nombre: </label><br>
                                        <div class="input-group">
                                        {!! Form::text('nombre', null, ['class'=>'input-sm form-control','id'=>'nombre']) !!}
                                    </div>

                                </div>

                                <div class="form-group" id="data_5">
                                        <label class="control-label" for="dateranges">Apellido: </label><br>
                                        <div class="input-group">
                                        {!! Form::text('apellido', null, ['class'=>'input-sm form-control','id'=>'apellido']) !!}

                                        </div>

                                </div>

                                <div class="form-group" id="data_5">
                                    <label class="control-label" for="dateranges">C&eacute;dula:</label><br>
                                    <div class="input-group date">
                                      <input type="text" name="cedula" id="cedula" onkeypress="return justNumbers(event);" class="input-sm form-control" maxlength="10" value="{{$request->cedula}}">
                                    </div>

                                </div>
                                  <div class="form-group" id="data_5">
                                      <label class="control-label" for="dateranges">Correo Electronico:</label><br>
                                      <input type="text" name="correo" id="correo" class="input-sm form-control" maxlength="100" value="{{$request->correo}}">
                                  </div>
                                
                                <div class="form-group" >
                                  <label class="control-label" for="dateranges">Estatus: </label><br>
                                  <div class="input-group date" >
                                    <select class="form-control input-sm" name="estatus">
                                      <option value="">Seleccione una opción</option>
                                      <option value="1">Activo</option>
                                      <option value="0">Inactivo</option>
                                    </select>
                                  </div>
                                </div>
  
                                <button type="submit" class="btn btn-primary" style="margin-top: 18px;">Buscar
                                </button>
                                <button type="button" id="refresh" class="btn btn-primary" style="margin-top: 18px;">Borrar
                                  </button>
                                
                                </div>
                            </div>
                    </form>
              </div>

          
            </div>
        @endif
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-content">
                          <h2>
                            {{$countUser}}
                              <span class="text-navy">
                              {{ $user }} en Total
                            </span>
                          </h2> 
                          <span class="text-navy" >
                            <a href="{{ url('UserExcel') }}" class="btn btn-primary" id="descargar">
                              <i class="fa fa-book"></i> 
                              Descargar
                            </a>
                          </span>

                          <span class="text-navy" >
                            <a href="{{ url('users') }}" class="btn btn-primary" id="descargar">
                              <i class="fa fa-eraser"></i> borrar
                            </a>
                          </span>


                          <div class="hr-line-dashed"></div>

                            @if($rolUser !='6')

                          <div class="table-responsive">
          <table id="datatab" class="table" >
                                <thead>
                                      <tr>
                                  <th>Nombre y Apellido</th>
                                  <th>Correo Electrónico</th>
                                  <th>Perfil</th>
                                  <th>Estatus</th>
                                  <th width="200">Acción</th>
                                  </tr>
                                </thead>
                              @foreach ($data as $key => $user)
                              <tr>
                                <td>{{ $user->first_name .' '. $user->last_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                  @if(!empty($user->roles))
                                    @foreach($user->roles as $v)
                                      <label class="label label-success">{{ $v->display_name }}</label>
                                    @endforeach
                                  @endif
                                </td>
                                <td>
                                  @if (empty($user->deleted_at))
                                    Activo
                                    @else
                                    Inactivo
                                  @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn-white btn btn-sm" data-toggle="modal" data-target="#detalle" onclick="show_users('{{ $user->id}}')" title="Ver ">
                                           <i class="fa fa-eye"></i> 
                                          
                                        </button>
                                        @permission('user-edit')
                                          @if (!empty($user->deleted_at))

                                          @else
                                            <a class="btn-white btn btn-sm" data-target="#detalle_edit" href="{{ route('users.edit',$user->id) }}" title="Editar">
                                              <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                              
                                            </a>
                                         @endif
                                       @endpermission
                                       @permission('user-delete')
                                        @if (empty($user->deleted_at))
                                          <a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea desactivar el usuario {{$user->first_name .' '. $user->last_name}}?')" href="{{ route('users.desactivar',$user->id) }}" title="Desactivar">
                                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                                           
                                          </a>
                                            @else
                                          <a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea activar el usuario {{$user->first_name .' '. $user->last_name}}?')" href="{{ route('users.activar',$user->id) }}" title="Activar">
                                            <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                                          
                                          </a>
                                        @endif
                                       </a>
                                       @endpermission
                                    </div>
                                </td>
                              </tr>
                              @endforeach
                              </table>
                            </div>
                             @else
                            <div class="table-responsive">
                            <table id="datatab" class="table" >
                              <thead>
                                <tr>
                                  <th>Nombre</th>
                                  <th>Cédula</th>                  
                                  <th>Correo Electrónico</th>
                                  <th>Membresía</th>
                                  <!--th>Límite</th-->
                                  <th>Estatus</th>
                                  <th width="80">Fecha</th>
                                  <th width="100">Acción</th>
                                </tr>
                              </thead>

                            @foreach ($data as $key => $user)

                            <tr>
                              <td>{{ $user->first_name .' '. $user->last_name }}</td>
                              <td>{{ $user->dni }}</td>
                              <td>{{ $user->email }}</td>
                              <td>

                                @if ($user->carnets->count())
                                  @php
                                    $carnets = join(', ',$user->carnets->map(function($carnet){
                                      return substr($carnet->carnet, 0, 4). ' XXXX XXXX '. substr($carnet->carnet, strlen($carnet->carnet) -4, 4). '('.(is_null($carnet->moneda) ? '-' : $carnet->moneda->mon_simbolo).')';
                                    })->toArray());
                                  
                                  @endphp
                                  {{$carnets}}
                                @else
                                    -
                                @endif
                              </td>
                              <!--td>{{ number_format($user->limite,2,',','.') }}</td-->
                  
                              <td>
                                  @if (empty($user->deleted_at))
                                    Activo
                                    @else
                                    Inactivo
                                  @endif
                                </td>
                              <td>
                                  {{date('d-m-Y', strtotime($user->created_at)) }}
                                </td>             
                              <td>
                                
                                  <div class="btn-group">
                                    
                                      
                                      <button class="btn-white btn btn-sm"  data-toggle="modal" data-target="#detalle" onclick="show_users('{{ $user->id}}')" title="Ver">
                                        <i class="fa fa-eye"></i> 
                                      </button>
                                      @permission('user-edit')
                                        @if (!empty($user->deleted_at))

                                        @else
                                          <a title="Editar" class="btn-white btn btn-sm" data-target="#detalle_edit" href="{{ route('users.edit',$user->id) }}"   >
                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                           
                                          </a>
                                       @endif
                                     @endpermission
                                     @permission('user-delete')
                                      @if (empty($user->deleted_at))
                                        <a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea desactivar el usuario {{$user->first_name .' '. $user->last_name}}?')" href="{{ route('users.desactivar',$user->id) }}" title="Desactivar">
                                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                                        </a>
                                          @else
                                        <a class="btn-white btn btn-sm" onclick ="return confirm('¿Desea activar el usuario {{$user->first_name .' '. $user->last_name}}?')" href="{{ route('users.activar',$user->id) }}" title="Activar">
                                       <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                                        </a>
                                      @endif
                                     </a>
                                     @endpermission
                                  </div>
                              </td>
                            </tr>
                            @endforeach
                            </table>
                          </div>
                           @endif

            </div>
                    </div>
                </div>
            </div>
  </div>
@endsection

@section('modal')

<div class="modal inmodal" id="detalle" tabindex="-1" role="dialog"  aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content animated fadeIn">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                  <i class="fa fa-users modal-icon"></i>
                  <h2 class="modal-title">Detalle del usuario</h2>
                  </div>
                  <div class="ibox-content">
                  @if($rolUser !='6')
                    <ul class="unstyled">
                          <div id="id"></div>
                          <div id="nacionalidad"></div>
                          <div id="dni"></div>
                          <div id="first_name"></div>
                          <div id="last_name"></div>
                          <div id="email"></div>
                          <div id="birthdate"></div>
                    </ul>
                    @else
                          <ul class="unstyled">
                          <div id="id"></div>
                          <div id="nacionalidad"></div>
                          <div id="dni"></div>
                          <div id="first_name"></div>
                          <div id="last_name"></div>
                          <div id="email"></div>
                          <div id="carnet"></div>
                          <div id="limite"></div>
                          <div id="birthdate"></div>
                    </ul>

                    @endif
                  </div>

              <div class="modal-footer">
                  <button type="button" class="btn btn-white" data-dismiss="modal">Cerrar</button>
              </div>
          </div>
      </div>
    </div>

@endsection

@section('scripts')
<script type="text/javascript" src="{{ asset('js/jackpotScripts/jackpotFunctions.js') }}"></script>

<script>

  $(document).ready(function() {
    $('#datatab').DataTable({
      responsive: true,
      "language": idioma,

    });
    $('#refresh').on('click', function(){
     location = window.location;
      
    });
  });

    $("#export").click(function(){
 
    $.get("{{URL('/excel')}}");
  });

    function justNumbers(e){
        var keynum = window.event ? window.event.keyCode : e.which;
        if ((keynum == 8) /*|| (keynum == 46) || (keynum == 44)*/)
        return true;

        return /\d/.test(String.fromCharCode(keynum));
}

</script>

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

@endsection
