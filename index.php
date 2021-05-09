<?php


// $request = file_get_contents( 'php://input' );
// #          ↑↑↑↑ 
// $request = json_decode( $request, TRUE );

// if( !$request )
// {
//     // Some Error output (request is not valid JSON)
// }
// elseif( !isset($request['update_id']) || !isset($request['message']) )
// {
//     // Some Error output (request has not message)
// }
// else
// {
//     $chatId  = $request['message']['chat']['id'];
//     $message = $request['message']['text'];

//     switch( $message )
//     {
//         case "/test":
//           sendMessage($chatId, "test");
//           break;
//         case "/hi":
//           sendMessage($chatId, "hi there!");
//           break;
//         default:
//           sendMessage($chatId, "default");
//     }

    
//     function sendMessage ($chatId, $message) {
//       $url = $GLOBALS[website]."/sendMessage?chat_id=".$chatId."&text=".urlencode($message);
//       url_get_contents($url);
//     }
// }

























// $botToken = "1751083459:AAFzN0lAPsm5RUgxChmRvWuaQN9IcsIzOL0";
// $website = "https://api.telegram.org/bot".$botToken;

// #$update = url_get_contents('php://input');
// $update = file_get_contents('php://input');
// $update = json_decode($update, TRUE);

// $chatId = $update["message"]["chat"]["id"];
// $message = $update["message"]["text"];

// switch($message) {
//     case "/test":
//         sendMessage($chatId, "test");
//         break;
//     case "/hi":
//         sendMessage($chatId, "hi there!");
//         break;
//     default:
//         sendMessage($chatId, "default");
// }

// function sendMessage ($chatId, $message) {
//     $url = $GLOBALS[website]."/sendMessage?chat_id=".$chatId."&text=".urlencode($message);
//     url_get_contents($url);

// }

// function url_get_contents($Url) {
//     if(!function_exists('curl_init')) {
//         die('CURL is not installed!');
//     }
//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $Url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     $output = curl_exec($ch);
//     curl_close($ch);
//     return $output;
// }



















$BOT_TOKEN ="1751083459:AAFzN0lAPsm5RUgxChmRvWuaQN9IcsIzOL0";

// $update = file_get_contents('php://input');
// $update = json_decode($update, true);
 

$parameters = array(
   "chat_id" => 1745530343,
   "text" => "Hello User"

);

send("sendMessage", $parameters);
print_r($parameters);exit;

function send($method, $data)
{
  global $BOT_TOKEN;
  $url = "https://api.telegram.org/bot$BOT_TOKEN/$method";
  $curld = null;
  if(!$curld){
    $curld = curl_init();
  }
  curl_setopt($curld, CURLOPT_POST, true);
  curl_setopt($curld, CURLOPT_POSTFIELDS,$data);
  curl_setopt($curld, CURLOPT_URL,$url);
  curl_setopt($curld, CURLOPT_RETURNTRANSFER,true);
  $output = curl_exec($curld);
  curl_close($curld);
  return $output;
}

?>