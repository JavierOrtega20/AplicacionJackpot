@extends('layouts.app')
@section('titulo', 'GiftCard')        
@section('contenido')
<style>
.product-imitation {
  text-align: center;
  padding: 0px 0;
  background-color: #f8f8f9;
  color: #bebec3;
  font-weight: 600;
}
.imgcard {
  width: 238px;
  height: 150px;
  border-radius: 10px;
}
@supports(object-fit: cover){
    .product-imitation img{
      height: 100%;
      object-fit: cover;
      object-position: center center;
    }
}
</style>
		<div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-lg-10">
                <h2><i class="fa fa-gift"></i>   Gift Card</h2>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.html">Panel</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a>Gift Card</a>
                    </li>
                    <li class="breadcrumb-item active">
                        <strong>Listado</strong>
                    </li>
                </ol>
            </div>
            <div class="col-lg-2">

            </div>
        </div>
		<div class="wrapper wrapper-content animated fadeInRight">
		
			<div class="row">
				<div class="col-lg-12">			
				@foreach($listgift as $gift)
					<div class="col-md-3">
						<div class="ibox">
							<div class="ibox-content product-box">

								<div class="product-imitation">
									<img src="{!!asset('img/GiftCard/'.$gift->imagen)!!}" class="imgcard">
								</div>
								<div class="product-desc">
									<span class="product-price">
										{{ $gift->mon_simbolo }}
									</span>
									<small class="text-muted">{{ $gift->emisor }}</small>
									<a href="#" class="product-name"> {{ $gift->nombregift }}</a>



									<div class="small m-t-xs">
										{{ $gift->descripcion }}
									</div>
									<!--
									<div class="m-t text-righ">
										<a href="{{ route('gift.step2', [ $gift->cod_emisor ]) }}" class="btn btn-xs btn-outline btn-primary">Comprar <i class="fa fa-long-arrow-right"></i> </a>
									</div>
									-->
									<!--
									<div class="m-t text-righ">
										<a href="{{ route('gift.comprador', [ $gift->cod_emisor ]) }}" class="btn btn-xs btn-outline btn-primary">Comprar <i class="fa fa-long-arrow-right"></i> </a>
									</div>
									-->
									<div class="m-t text-righ">
										<a href="{{ route('gift.venta', [ $gift->cod_emisor ]) }}" class="btn btn-xs btn-outline btn-primary">Comprar <i class="fa fa-long-arrow-right"></i> </a>
									</div>										
								</div>
							</div>
						</div>
					</div>			
				@endforeach		
				</div>				
			</div>
		</div>
@endsection		