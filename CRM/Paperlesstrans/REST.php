<?php

class CRM_Paperlesstrans_REST {
  public $ch;
  public $url;
  public $mode;
  public $production = 0;
  protected $paymentProcessor;

  public function __construct($paymentProcessor, $mode = 'live') {
    $this->mode = $mode;
    if (!$this->production) {
      // only for dev/test
      $this->mode = 'test';
    }
    $this->paymentProcessor = $paymentProcessor;
  }  
  public function __destruct() {
    curl_close($this->ch);
  }

  public function init($url) {
    if ($this->ch) {
      // close if already exist to create new obj
      curl_close($this->ch);
    }
    $this->ch  = curl_init();
    $this->url = rtrim($this->paymentProcessor["url_site"], '/') . $url;
  }

  public function call($params) {
    $curlParams = array(
      CURLOPT_URL            => $this->url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING       => "",
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 30,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => "POST",
      CURLOPT_POSTFIELDS     => json_encode($params),
      CURLOPT_HTTPHEADER     => array(
        "Cache-Control: no-cache",
        "Content-Type: application/json",
        "TerminalKey: {$this->paymentProcessor['password']}",
      ),
    );
    if ($this->mode == 'test') {
      $curlParams[CURLOPT_HTTPHEADER][] = "TestFlag: true";
    }
    curl_setopt_array($this->ch, $curlParams);
    $response = curl_exec($this->ch);
    if(curl_error($this->ch)) {
      throw new Exception("API call to {$this->url} failed: " . curl_error($this->ch));
    }
    $result = json_decode($response, true);
    CRM_Core_Error::debug_var('$curlParams', $curlParams);
    CRM_Core_Error::debug_var('curl post $params', $params);
    CRM_Core_Error::debug_var('curl result', $result);
    return $result;
  }

  public function createProfile($params) {
    $this->init("/profiles/create");
    return $this->call($params);
  }

  public function captureTransaction($params) {
    $this->init("/transactions/capture");
    return $this->call($params);
  }
  //public function isError() {
  //  return curl_error($this->ch);
  //}
}


