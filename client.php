<?
Class PlassoBilling {
  var $plassoUserId; var $plassoToken;
  function __construct($plassoToken) {
    if(!$plassoToken || is_null($plassoToken) || $plassoToken == ''){ $this->errorPage(); }
    $this->plassoToken = $plassoToken;
    if($this->hasSession()) {
      $cookie = json_decode($_COOKIE['pl__'.$this->plassoToken], true);
      $this->plassoUserId = $cookie['plassoUserId'];
      return true;
    } else if(!$this->ping()){ $this->errorPage(); }
  }
  function ping() {
    $results = file_get_contents('https://plasso.com/api/billing_ping/'.$this->plassoToken);
    if(!$results){ return false; } else {
      $json = json_decode($results, true);
      $this->plassoUserId = $json['plasso_user_id'];
      $cookieData = json_encode(array('plassoUserId' => $this->plassoUserId));
      setcookie('pl__'.$this->plassoToken, $cookieData, time()+3600, '/', $_SERVER['HTTP_HOST'], true, true);
      return true;
    }
  }
  function hasSession() {
    return (isset($_COOKIE['pl__'.$this->plassoToken]) && $_COOKIE['pl__'.$this->plassoToken] != '');
  }
  function errorPage() {
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', true, 404); exit;
  }
}
$plassoBilling = new PlassoBilling($_GET['__plasso_token']);
?>
