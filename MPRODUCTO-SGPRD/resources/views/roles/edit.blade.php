@extends('layouts.app')
 @section('titulo')
    Editar Perfil
@endsection
@section('contenido')

{!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id],'class'=>'form-horizontal']) !!}
 <div class="row wrapper border-bottom white-bg page-heading">
  <div class="col-lg-8">
    <h2><i class="fa fa-diamond"></i>   Perfiles</h2>
    <ol class="breadcrumb">
      <li>
      <a href="{{ url('home') }}">Panel</a>
      </li>
      <li>Perfiles
      </li>
      <li class="active">
      <strong>Editar Perfiles</strong>
      </li>
    </ol>
    </div>
    <div class="col-lg-4">
      <div class="title-action">
        <a href="{{route('roles.index')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
        <button type="submit" class="btn btn-primary" id="form-validation"><span class="btn-label">
                    <i class="fa fa-check"></i>
                </span>Guardar</button>
      </div>
  </div>

</div>

<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    @include('error')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Editar perfiles</h5>
                </div>
                <div class="ibox-content">

                      <div class="hr-line-dashed"></div>
                          <div class="form-group"><label class="col-sm-2 control-label">Nombre <span class="text-danger">*</span></label>

                          <div class="col-sm-10">
                             {!! Form::text('name', null, array('placeholder' => 'Nombre','class' => 'form-control input-lg m-b','readonly'=>true)) !!}
                          </div>
                      </div>
                      <div class="hr-line-dashed"></div>
                          <div class="form-group"><label class="col-sm-2 control-label">Nombre para mostrar<span class="text-danger">*</span></label>

                          <div class="col-sm-10">
                            {!! Form::text('display_name', null, array('placeholder' => 'Nombre para mostrar','class' => 'form-control input-lg m-b','readonly'=>true)) !!}
                          </div>
                      </div>
                      <div class="hr-line-dashed"></div>
                          <div class="form-group"><label class="col-sm-2 control-label">Descripcion <span class="text-danger">*</span></label>

                          <div class="col-sm-10">
                            {!! Form::textarea('description', null, array('placeholder' => 'Descripción','class' => 'form-control','style'=>'height:100px','readonly'=>true)) !!}
                          </div>
                      </div>                            
                      <div class="hr-line-dashed"></div>
                          <div class="form-group"><label class="col-sm-2 control-label">Permisos <span class="text-danger">*</span></label>

                          <div class="col-sm-10">
                            @foreach($permission as $value)
                                <div class="col-md-2 col-xs-12">
                                    <div class="form-group">

                                        <div class="icheckbox">
                                            <label>
                                                {{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class' => 'name')) }}
                                                {{ $value->display_name }}


                                            </label>

                                        </div>
                                    </div>
                                </div>

                            @endforeach
                          </div>
                      </div>
                      <div class="hr-line-dashed"></div>
                      <div class="form-group">
                          <div class="title-action">
                            <a href="{{route('roles.index')}}" class="btn btn-white"><i class="fa fa-times"></i> Cancelar </a>
                            <button type="submit" class="btn btn-primary" id="form-validation"><span class="btn-label">
                                        <i class="fa fa-check"></i>
                                    </span>Guardar</button>
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

	<script>
        $('.styled1').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            //radioClass: 'iradio_minimal',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
	</script>
<script src="{!!asset('js/plugins/jsvalidator/jsvalidation.js')!!}"></script>
{!! JsValidator::formRequest('App\Http\Requests\RoleRequest') !!}
@endsection