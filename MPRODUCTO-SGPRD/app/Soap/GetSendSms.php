<?php

namespace App\Soap;

class GetSendSms
{
  /**
   * @var string
   */
  protected $passport;

  /**
   * @var string
   */
  protected $password;

  /**
   * @var string
   */
  protected $number;

  /**
   * @var string
   */
  protected $text;

  /**
   * GetConversionAmount constructor.
   *
   * @param string $passport
   * @param string $CurrencyTo
   * @param string $RateDate
   * @param string $Amount
   */
  public function __construct($passport, $password, $number, $text)
  {
    $this->passport   = $passport;
    $this->password   = $password;
    $this->number     = $number;
    $this->text       = $text;
  }

  /**
   * @return string
   */
  public function getPassport()
  {
    return $this->passport;
  }

  /**
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * @return string
   */
  public function getNumber()
  {
    return $this->number;
  }

  /**
   * @return string
   */
  public function getText()
  {
    return $this->text;
  }
}