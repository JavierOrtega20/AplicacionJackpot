<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MontoExcedido extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $nombreComercio;
    public $rif;
    public $cedula;
    public $nombreCliente;
    public $monto;
    public $date;
	public $producto;
	public $moneda;



    public function __construct($rif, $nombreComercio, $cedula, $nombreCliente, $monto, $date, $producto, $moneda)

    {
        $this -> rif = $rif;
        $this -> nombreComercio = $nombreComercio;
        $this -> cedula = $cedula;
        $this -> nombreCliente = $nombreCliente;
        $this -> monto = $monto;
        $this -> fecha = $date;
		$this -> producto = $producto;
		$this -> moneda = $moneda;


    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        // return $this->view('emails.order.deliveredstatusorder')
        return $this->markdown('emails.montoExcedido')
            ->with([
                'rif' => $this->rif,
                'nombreComercio' => $this->nombreComercio,
                'cedula' => $this->cedula,
                'nombreCliente' => $this->nombreCliente,
                'monto' => $this->monto,
                'fecha' => $this->fecha,
				'producto' => $this->producto,
				'moneda' => $this->moneda,

            ])
            ->from('noreply@mg.meritop.com','Meritop')
          ->subject('Saldo Insuficiente');
    // }

    }
}
