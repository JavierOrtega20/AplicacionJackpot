<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompraGiftCardEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $montos;
    public $moneda;
    public $desc_comercio;
    public $de;
	public $paraCedula;
	public $paraEmail;
	public $paraTelefono;
    public $para;
	public $imagen;
	public $vencimiento;

    public function __construct($montos, $moneda, $desc_comercio, $de, $para, $imagen,$paraCedula,$paraEmail,$paraTelefono, $vencimiento)
    {
        $this->montos = $montos;
        $this->moneda = $moneda;
        $this->desc_comercio = $desc_comercio;
        $this->de = $de;
        $this->para = $para;
		$this->imagen = $imagen;
		$this->paraCedula = $paraCedula;
		$this->paraEmail = $paraEmail;
		$this->paraTelefono = $paraTelefono;
		$this->vencimiento = $vencimiento;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        return $this->markdown('emails.CompraGiftCardEmail')
            ->with([
                'montos' => $this->montos,
                'moneda'       => $this->moneda,
                'desc_comercio' => $this->desc_comercio,
                'de'      => $this->de,
                'para'       => $this->para,
				'imagen'       => $this->imagen,
				'paraCedula'       => $this->paraCedula,
				'paraEmail'       => $this->paraEmail,
				'paraTelefono'       => $this->paraTelefono,
				'vencimiento'       => $this->vencimiento,
            ])
            ->from('noreply@mg.meritop.com','Meritop')
            ->subject('Tarjeta de regalo');
        
    }
}
