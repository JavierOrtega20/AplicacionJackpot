<?php

namespace App\Soap;

class GetSendSmsResponse
{
  /**
   * @var string
   */
  protected $sendSMSResponse;

  /**
   * GetConversionAmountResponse constructor.
   *
   * @param string
   */
  public function __construct($sendSMSResponse)
  {
    $this->sendSMSResponse = $sendSMSResponse;
  }

  /**
   * @return string
   */
  public function getSendSMSResponse()
  {
    return $this->sendSMSResponse;
  }
}