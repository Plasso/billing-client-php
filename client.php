<?
Class PlassoBilling {
  var $plassoUserId; var $plassoToken;
  function __construct($plassoToken) {
    $this->plassoToken = $plassoToken;
    if(isset($plassoToken) && $plassoToken == 'logout') { $this->logout(); } else if(!$this->ping()){ $this->errorPage(); }
  }
  function ping() {
    if (!isset($this->plassoToken) && isset($_COOKIE['__pl_billing']) && $_COOKIE['__pl_billing'] != '') {
      $this->plassoToken = $_COOKIE['__plasso_billing'];
    }
    $results = file_get_contents('https://api.plasso.com/?query=%7Bmember(token%3A%22'.$this->plassoToken.'%22)%7Bid%7D%7D');
    if(!$results){ return false; } else {
      $json = json_decode($results, true);
      if(isset($json['errors']) && count($json['errors']) > 0){  $this->logout(); }
      $this->plassoUserId = $json['data']['member']['id'];
      setcookie('__pl__billing', $this->plassoToken, time() - 3600, '/', $_SERVER['HTTP_HOST'], true, true);

      return true;
    }
  }
  function logout() {
    unset($_COOKIE['__pl__billing']);
    setcookie('__pl__billing', '', time() - 3600, '/', $_SERVER['HTTP_HOST'], true, true);
    echo '<html><head><meta http-equiv="refresh" content="0; URL='.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')?'https':'http').'://'.$_SERVER['HTTP_HOST'].'" /></head><body></body></html>';
    exit;
  }
  function errorPage() {
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', true, 404); exit;
  }
}
$plassoBilling = new PlassoBilling((isset($_GET['__logout']))?'logout':$_GET['__plasso_token']);
// Access the Plasso User ID with: $plassoBilling->plassoUserId
?>
