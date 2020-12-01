@extends('layouts.app')
 @section('titulo')
    Lista de Perfiles
@endsection
@section('contenido')
        <div class="row wrapper border-bottom white-bg page-heading">
          <div class="col-lg-8">
            <h2><i class="fa fa-users"></i>   Perfiles</h2>
            <ol class="breadcrumb">
              <li>
              <a href="{{ url('home') }}">Panel</a>
              </li>
              <li>Perfiles
              </li>
              <li class="active">
              <strong>Listado</strong>
              </li>
            </ol>
            </div>
            @permission('user-create')
            <div class="col-lg-4">
              <div class="title-action">
                <a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nuevo</a>
              </div>
          </div>
          @endpermission
        </div>
	
	
	
        <div class="wrapper wrapper-content ecommerce">
          @include('success')
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-content">
                          <h2>
                              <span class="text-navy"> Perfiles</span>
                          </h2>
                          <div class="hr-line-dashed"></div>
                          <div class="table-responsive">
                            <table class="footable table table-stripped toggle-arrow-tiny" data-page-size="10">
                                <thead>
            						          <tr>
                  								  <th>Nombre</th>
                  									<th>Descripción</th>
                  									<th width="120">Acción</th>
                  								</tr>
                  							</thead>
                  							@foreach ($roles as $key => $role)
                  							<tr>
                  								<td>{{ $role->display_name }}</td>
                  								<td>{{ $role->description }}</td>
                  								<td>
                  									<div class="btn-group">
                                      <a class="btn-white btn btn-sm" data-target="#detalle_edit" href="{{ route('roles.show',$role->id) }}">
                                        Ver
                                     </a>
                                      @permission('role-edit')
                                      <a class="btn-white btn btn-sm" data-target="#detalle_edit" href="{{ route('roles.edit',$role->id) }}">
                                        Editar
                                     </a>
                                     @endpermission
                                    </div>
                  							   </td>
                  							</tr>
                  							@endforeach
                                <tfoot>
                                  <tr>
                                      <td colspan="7">
                                          <ul class="pagination pull-right"></ul>
                                      </td>
                                  </tr>
                                  </tfoot>
                  							</table>
                                </div>							
	                     </div>
                    </div>
                </div>
          </div>
      </div>

@endsection

@section('scripts')


<script>
    $(document).ready(function() {

        $('.footable').footable();



    });

</script>

@endsection