<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CedulaInvalida extends Mailable
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
    public $date;



    public function __construct($rif, $nombreComercio, $cedula, $date)
    {
        $this -> rif = $rif;
        $this -> nombreComercio = $nombreComercio;
        $this -> cedula = $cedula;
        $this -> fecha = $date;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        // return $this->view('emails.order.deliveredstatusorder')
        return $this->markdown('emails.cedulaInvalida')
            ->with([
                'rif' => $this->rif,
                'nombreComercio' => $this->nombreComercio,
                'cedula' => $this->cedula,
                'fecha' => $this->fecha,
            ])
            ->from('noreply@mg.meritop.com','Meritop')
          ->subject('Cédula Inválida del Cliente');
    // }

    }
}
