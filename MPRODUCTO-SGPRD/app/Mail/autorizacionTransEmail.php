<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class autorizacionTransEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $montos;
    public $hoy;
    public $desc_comercio;
    public $idTrans;
    public $nombre_completo;
	public $producto;
	public $es_giftcard;
	public $saldo_gift;

    public function __construct($montos, $hoy, $desc_comercio, $idTrans, $nombre_completo, $moneda, $producto, $es_giftcard = false, $saldo_gift = 0)
    {
        $this -> montos = $montos;
        $this -> hoy = $hoy;
        $this -> desc_comercio = $desc_comercio;
        $this -> idTrans = $idTrans;
        $this -> nombre_completo = $nombre_completo;
		$this -> moneda = $moneda;
		$this -> producto = $producto;
		$this -> es_giftcard = $es_giftcard;
		$this -> saldo_gift = $saldo_gift;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        return $this->markdown('emails.autorizacionTransEmail')
            ->with([
                'montos' => $this->montos,
                'hoy'       => $this->hoy,
                'desc_comercio' => $this->desc_comercio,
                'idTrans'      => $this->idTrans,
                'nombre_completo'       => $this->nombre_completo,
				'moneda'       => $this->moneda,
				'producto'       => $this->producto,
				'es_giftcard'       => $this->es_giftcard,
				'saldo_gift'       => $this->saldo_gift,
            ])
            ->from('noreply@mg.meritop.com','Meritop')
            ->subject('Envío de autorización procesada'/*.$this->data_order->id*/);
        
    }
}
