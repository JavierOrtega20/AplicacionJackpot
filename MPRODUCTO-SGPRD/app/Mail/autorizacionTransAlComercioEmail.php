<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class autorizacionTransAlComercioEmail extends Mailable
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
	public $moneda;

    public function __construct($montos, $hoy, $desc_comercio, $idTrans, $nombre_completo, $moneda)
    {
        $this -> montos = $montos;
        $this -> hoy = $hoy;
        $this -> desc_comercio = $desc_comercio;
        $this -> idTrans = $idTrans;
        $this -> moneda = $moneda;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        return $this->markdown('emails.autorizacionTransAlComercioEmail')
            ->with([
                'montos' => $this->montos,
                'hoy'       => $this->hoy,
                'desc_comercio' => $this->desc_comercio,
                'idTrans'      => $this->idTrans,
                'nombre_completo'       => $this->nombre_completo,
				'moneda'       => $this->moneda,
            ])
            ->from('noreply@mg.meritop.com','Presidents Pay')
            ->subject('Confirmación de pago recibido');
        
    }
}
