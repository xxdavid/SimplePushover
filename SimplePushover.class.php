<?php

/**
* Pushover API class
* API Documentation: https://pushover.net/api
* Class Documentation: https://github.com/xxdavid/SimplePushover/blob/master/README.md
*
* @author David Pavlík
* @since 6.5.2013
* @copyright David Pavlík
* @version 1.0
* @license BSD http://www.opensource.org/licenses/bsd-license.php
*/

class SimplePushover{
  
  
  /**
  * The Pushover application's API token
  *
  * @var string
  */
  private $_token;
  
  
  /**
  * The Pushover user key
  *
  * @var string
  */
  private $_user;
  
  /**
  * Array of devices paired with account
  *
  * @var array
  */
  private $_devices;
  
  /**
  * Array of error in last message
  *
  * @var array
  */
  private $errorMessage;
  
  /**
  * Default constructor
  *
  * @param string $token      your application's API token 
  * @param string $user       the user key (not e-mail address) of your user (or you), viewable when logged into Pushover dashboard
  * @return void
  */
  public function __construct($token,$user) {
    if ($this->verifyUser($token,$user) == 0){
      throw new Exception("Error: combination of user key and application's token is invalid.");
    } else {
      $this->_token = $token;
      $this->_user = $user;  
    }
  }
  
  
  /**
  * Verify user key and token
  *
  * @param string $token        your application's API token
  * @param string $user       the user key (not e-mail address) of your user (or you), viewable when logged into Pushover dashboard      
  * @return integer
  */
  private function verifyUser($token,$user){
     $params = array(
      "token" => $token,
      "user" => $user);
    $json = $this->curl("https://api.pushover.net/1/users/validate.json",$params); 
    $response = json_decode($json,true);
    $this->_devices = $response["devices"];
    return $response["status"];  
  }
  
  
  /**
  * Execute cURL extesion and return result
  *
  * @param string $url        URL to set to "CURLOPT_URL" 
  * @param array $params      Post parameters    
  * @return mixed
  */
  
  protected function curl($url,$params){
    curl_setopt_array($ch = curl_init(), array(
      CURLOPT_URL => $url,
      CURLOPT_POSTFIELDS => $params,
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_CAINFO, getcwd() . "/GeoTrustGlobalCA.crt");
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;  
  }
  
 
  /**
  * Sends Pushover message with only message parameter
  *
  * @param string $message    message to send 
  * @return integer
  */
  public function simpleMessage($message){
    $params = array(
      "token" => $this->_token,
      "user" => $this->_user,
      "message" => $message,
      );
    $json = $this->curl("https://api.pushover.net/1/messages.json",$params);
    $response = json_decode($json,true);
    //print_r ($response);
    $this->errorMessage = $response["errors"];
    return $response["status"]; 
  } 
  
  
  
  /**
  * Sends Pushover message with all (or less) parameters
  *
  * @param string $message    message to send 
  * @param string $device     device name to send the message directly to that device
  * @param string $title      your message's title, otherwise your app's name is used
  * @param string $url        a supplementary URL to show with your message 
  * @param string $url_title  a title for your supplementary URL, otherwise just the URL is shown 
  * @param integer $priority  send as -1 to always send as a quiet notification, 1 to display as high-priority and bypass the user's quiet hours, or 2 to also require confirmation from the user
  * @param integer $retry     use only if priority == 2 - how often (in seconds) the Pushover servers will send the same notification to the user
  * @param integer $expire    use only if priority == 2 - how many seconds your notification will continue to be retried for  
  * @param integer $timestamp a Unix timestamp of your message's date and time to display to the user, rather than the time your message is received by our API  
  * @param string $sound      the name of one of the sounds supported by device clients to override the user's default sound choice             
  * @return integer
  *
  */  
  public function advancedMessage($message,$device,$title,$url,$url_title,$priority,$retry,$expire,$timestamp,$sound){
    $params = array(
      "token" => $this->_token,
      "user" => $this->_user,
      "message" => $message,
      "device" => $device,
      "title" => $title,
      "url" => $url,
      "url_title" => $url_title,
      "priority" => $priority,
      "timestamp" => $timestamp,
      "retry" => $retry,
      "expire" => $expire,
      "sound" => $sound,
      );
    $json = $this->curl("https://api.pushover.net/1/messages.json",$params);
    $response = json_decode($json,true);
    //print_r ($response);
    $this->errorMessage = $response["errors"];
    return $response["status"]; 
  }
 
  
 
  
  
  /**
  * Return error message of last message
  *
  * @return string
  */
  public function getError(){
    if ($this->errorMessage){
      foreach ($this->errorMessage as $error){
        $errorsString .= $error;
        if ($i > 0) {
          $errorString .- ", ";
        }
        $i++;
      }
      return $errorsString;
    } else {
      return "No errors";
    }
  } 
  

}



?>