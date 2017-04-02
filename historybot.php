<?php

/*/////////////////////////////////////////////////////////////////////
//
//	HistoryBot v0.1 by V.exeR
//
//	1. Create new bot with @BotFather
//	2. Insert bot's token to $access_token
//	3. Insert path to hook to $hook_url
//	4. Upload this file on your hosting with SSL
//	5. Create directory 'tgbot' in same dir
// 	6. Create subdirs 'audio', 'document', 'photo' and 'voice' in 'tgbot'
//	7. Visit $hook_url.'/?mode=install' to set webhook
//	8. PROFIT!
//
/////////////////////////////////////////////////////////////////////*/

  $access_token='';			// Insert ACCESS_TOKEN here
  $hook_url='https://';		// Insert hook url here
  
  $api='https://api.telegram.org/bot'.$access_token;
  $api_file='https://api.telegram.org/file/bot'.$access_token;
  
  $history_path="tgbot/";								// History path
  $history_name=$logpath.strftime("%Y-%m-%d",time());	// History file

  $_month = Array("00"=>"","01"=>"января","02"=>"февраля","03"=>"марта","04"=>"апреля","05"=>"мая","06"=>"июня","07"=>"июля","08"=>"августа","09"=>"сентября","10"=>"октября","11"=>"ноября","12"=>"декабря");

function sendMessage($chat_id, $message) {
  global $api;
  global $history_name;
  $err=file_get_contents($api.'/sendMessage?chat_id='.$chat_id.'&text='.urlencode($message));
  
  $msg=Array("t"=>time(),"f"=>'bot',"m"=>$message);
  $ff=fopen($history_name,"a");
  fputs($ff,serialize($msg)."\n");
  fclose($ff);
};

function getFile_info($file_id) {
  global $api;
  $err=file_get_contents($api.'/getFile?file_id='.$file_id);
  return $err;
};

function getFile($file_path) {
  global $api_file;
  $err=file_get_contents($api_file.'/'.$file_path);
  return $err;
};

  if (isset($_GET["mode"])) {
	if ($_GET["mode"]=="install") {		// Set hook
      $err=file_get_contents($api.'/setWebhook?url='.$hook_url);
      echo $err;
	  exit;
	};  
  };

  $chat_id=""; $AnswerText="";

  $output = json_decode(file_get_contents('php://input'), TRUE);	// Get input message

  $chat_id = $output['message']['chat']['id'];
  $from_id = $output['message']['from']['id'];
  $first_name = $output['message']['chat']['first_name'];
  $message = $output['message']['text'];
  $ff=fopen("tg-callback","a");
  fputs($ff,time().'|'.serialize($output)."\n");
  fclose($ff);

  // Stickers
  if (isset($output['message']['sticker'])) {
    $sticker_id=json_decode(getFile_info($output['message']['sticker']['file_id']),true);
    $message=$message.'[sticker]'.$sticker_id['result']['file_path'];
    $sticker=getFile($sticker_id['result']['file_path']);

    $ff=fopen($history_path.$sticker_id['result']['file_path'],"w");
    fputs($ff,$sticker);
    fclose($ff);
  };

  // Audio
  if (isset($output['message']['audio'])) {
    $audio_id=json_decode(getFile_info($output['message']['audio']['file_id']),true);
    $audio_type=$output['message']['audio']['mime_type'];
    if (isset($output['message']['audio']['title'])) {
      $audio_title=$output['message']['audio']['title'];
      $audio_performer=$output['message']['audio']['performer'];
      $audio_name=$audio_performer.' - '.$audio_title;
    };
    $message=$message.'[audio]'.$audio_id['result']['file_path'].'|'.$audio_name.'|'.$audio_type;
    $audio=getFile($audio_id['result']['file_path']);

    $ff=fopen($history_path.$audio_id['result']['file_path'],"w");
    fputs($ff,$audio);
    fclose($ff);
  };

  // Voice messages
  if (isset($output['message']['voice'])) {
    $voice_id=json_decode(getFile_info($output['message']['voice']['file_id']),true);
    $voice_type=$output['message']['voice']['mime_type'];
	$voice_duration=$output['message']['voice']['duration'];
    $message=$message.'[voice]'.$voice_id['result']['file_path'].'|'.$voice_duration.'|'.$voice_type;
    $voice=getFile($voice_id['result']['file_path']);

    $ff=fopen($history_path.$voice_id['result']['file_path'],"w");
    fputs($ff,$voice);
    fclose($ff);
  };
  
  // Documents
  if (isset($output['message']['document'])) {
    $document_id=json_decode(getFile_info($output['message']['document']['file_id']),true);
    $document_name=$output['message']['document']['file_name'];
    $document_type=$output['message']['document']['mime_type'];
    $message=$message.'[document]'.$document_id['result']['file_path'].'|'.$document_name.'|'.$document_type;
    $document=getFile($document_id['result']['file_path']);

    $ff=fopen($history_path.$document_id['result']['file_path'],"w");
    fputs($ff,$document);
    fclose($ff);
  };

  // Photos
  if (isset($output['message']['photo'])) {
    $photo_id=json_decode(getFile_info($output['message']['photo']["2"]['file_id']),true);
    $message=$message.'[photo]'.$photo_id['result']['file_path'];
    $photo=getFile($photo_id['result']['file_path']);

    $ff=fopen($history_path.$photo_id['result']['file_path'],"w");
    fputs($ff,$photo);
    fclose($ff);
  };

  // Write history
  $msg=Array("t"=>time(),"f"=>$from_id,"m"=>$message);
  $ff=fopen($history_name,"a");
  fputs($ff,serialize($msg)."\n");
  fclose($ff);

  if ($message=='/start'){
	$AnswerText="Здравствуйте!\n";
	$AnswerText=$AnswerText."Это бот-летописец\n";
  };

  if ($AnswerText!="") {
	sendMessage($chat_id,$AnswerText);
  };
  
?>