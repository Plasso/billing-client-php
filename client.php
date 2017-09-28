<?php

Class PlassoBilling {
  var $plassoUserId; var $plassoToken; var $plassoPlanId; var $stripeCustomerId;
  function __construct($plassoToken, $runProtect = true) {
    $this->plassoToken = $plassoToken;
    if($plassoToken == 'logout'){ $this->authFail(); $this->logout(); return; }
    $this->authenticate();
    if($runProtect){ $this->protect(); }
  }
  function authenticate() {
    if (!isset($this->plassoToken) && isset($_COOKIE['__plasso_billing']) && $_COOKIE['__plasso_billing'] != '') {
      $cookieJson = json_decode($_COOKIE['__plasso_billing'], true);
      if(isset($cookieJson['token']) && !empty($cookieJson['token'])){ $this->plassoToken = $cookieJson['token']; }
    }
    if(empty($this->plassoToken)) { $this->authFail(); return; }
    $results = file_get_contents('https://api.plasso.com/?query=%7Bmember(token%3A%22'.$this->plassoToken.'%22)%7Bid%2CstripeCustomerId%2Cspace%7BlogoutUrl%7D%7D%7D');
    if(!$results){ $this->authError(); return; } else {
      $json = json_decode($results, true);
      if(isset($json['errors']) && count($json['errors']) > 0){ $this->authFail(); return; }
      $this->plassoUserId = $json['data']['member']['id'];
      $this->plassoPlanId = $json['data']['member']['planId'];
      $this->stripeCustomerId = $json['data']['member']['stripeCustomerId'];
      $cookieValue = json_encode(array('token' => $this->plassoToken, 'logout_url' => $json['data']['member']['space']['logout_url']));
      setcookie('__plasso_billing', $cookieValue, time() + 3600, '/', $_SERVER['SERVER_NAME'], false, true);
      $_COOKIE['__plasso_billing'] = $cookieValue;
    }
  }
  function logout() {
    $logoutUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')?'https':'http').'://'.$_SERVER['HTTP_HOST'];
    if(isset($_COOKIE['__plasso_billing']) && $_COOKIE['__plasso_billing'] != ''){
      $cookieJson = json_decode($_COOKIE['__plasso_billing'], true);
      if(isset($cookieJson['logout_url']) && !empty($cookieJson['logout_url'])){ $logoutUrl = $cookieJson['logout_url']; }
    }
    echo '<html><head><meta http-equiv="refresh" content="0; URL='.$logoutUrl.'" /></head><body></body></html>'; exit;
  }
  function authFail() {
    unset($_COOKIE['__plasso_billing']);
    setcookie('__plasso_billing', '', time() - 3600, '/', $_SERVER['SERVER_NAME'], false, true);
    $this->plassoToken = 'logout';
  }
  function authError() {
    $this->plassoToken = 'error';
  }
  function errorPage() {
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', true, 404); exit;
  }
  function protect() {
    if(isset($this->plassoToken) && $this->plassoToken == 'logout') { $this->logout(); } else if($this->plassoToken == 'error'){ $this->errorPage(); }
  }
}

// To initalize, uncomment the next line:
// $plassoBilling = new PlassoBilling((isset($_GET['__logout']))?'logout':(isset($_GET['__plasso_token'])?$_GET['__plasso_token']:NULL));

// Access the Plasso User ID with: $plassoBilling->plassoUserId
// Access the Plasso User Plan ID with: $plassoBilling->plassoPlanId
// Access the Plasso User Stripe Customer ID with: $plassoBilling->stripeCustomerId

?>
