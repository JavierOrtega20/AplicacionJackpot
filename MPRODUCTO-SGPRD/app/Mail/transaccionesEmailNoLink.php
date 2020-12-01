<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class transaccionesEmailNoLink extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $montos;
    public $token;
    public $hoy;
    public $desc_comercio;
    public $idTrans;
    public $nombre_completo;
	public $moneda;
	public $producto;


    public function __construct($montos, $token, $fecha, $desc_comercio, $idTrans, $nombre_completo, $moneda, $producto)
    {
        // $this->data_order = $data_order;
        // $this->items = $items;
        // $this->logo = $logo;
        $this -> montos = $montos;
        $this -> token = $token;
        $this -> hoy = $fecha;
        $this -> desc_comercio = $desc_comercio;
        $this -> idTrans = $idTrans;
        $this -> nombre_completo = $nombre_completo;
		$this -> moneda = $moneda;
		$this -> producto = $producto;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        // return $this->view('emails.order.deliveredstatusorder')
        return $this->markdown('emails.transaccionesEmailNoLink')
            ->with([
                'montos' => $this->montos,
                'token'      => $this->token,
                'hoy'       => $this->hoy,
                'desc_comercio' => $this->desc_comercio,
                'idTrans'      => $this->idTrans,
                'nombre_completo'       => $this->nombre_completo,
				'moneda'       => $this->moneda,
				'producto'       => $this->producto,
            ])
            ->from('noreply@meritop.com','Meritop')
            ->subject('Confirmación de transacción'/*.$this->data_order->id*/);
    // }
        
    }
}
