<?
Class PlassoBilling {
  var $plassoUserId; var $plassoToken;
  function __construct($plassoToken) {
    session_set_cookie_params(3600, '/', $_SERVER['HTTP_HOST'], true, true);
    session_start();
    $this->plassoToken = $plassoToken;
    if(isset($plassoToken) && $plassoToken == 'logout') { $this->logout();
    } else if($this->hasSession()) {
      $this->plassoUserId = $_SESSION['__pl__billing']['plassoUserId'];
      return true;
    } else if(!$this->ping()){ $this->errorPage(); }
  }
  function ping() {
    $results = file_get_contents('https://plasso.com/api/billing_ping/'.$this->plassoToken);
    if(!$results){ return false; } else {
      $json = json_decode($results, true);
      if(isset($json['logout']) && $json['logout']){  $this->logout(); }
      $this->plassoUserId = $json['plasso_user_id'];
      $_SESSION['__pl__billing'] = array('plassoUserId' => $this->plassoUserId);
      return true;
    }
  }
  function hasSession() {
    return (isset($_SESSION['__pl__billing']) && isset($_SESSION['__pl__billing']['plassoUserId']));
  }
  function logout() {
    unset($_SESSION['__pl__billing']);
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
