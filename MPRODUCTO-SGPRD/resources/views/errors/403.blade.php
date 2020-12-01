@extends('layouts.app')
 
@section('contenido')
	<div class="error-page">
    <h2 class="headline text-red">ERROR 403</h2>
    <div class="error-content">
        <h3><i class="fa fa-danger text-red"></i> Acceso denegado.</h3>
        <p>
            No tiene privilegios para acceder a este sitio <a href='{{ url('/home') }}'>pagina principal</a>
        </p>
       
    </div><!-- /.error-content -->
</div><!-- /.error-page -->
@endsection