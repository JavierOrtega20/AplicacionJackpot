<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AutorizacionFallidaFintech extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $referencia;
    public $mensaje;


    public function __construct($referencia, $mensaje)
    {
        $this->referencia = $referencia;
        $this->mensaje = $mensaje;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        // return $this->view('emails.order.deliveredstatusorder')
        return $this->markdown('emails.AutorizacionFallidaFintech')
            ->with([
                'referencia' => $this->referencia,
                'mensaje' => $this->mensaje,
            ])
            ->from('noreply@mg.meritop.com','Meritop')
			->subject('Transacción ' . $this->referencia .' rechazada en Fintech');
    // }

    }
}
