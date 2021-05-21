<?php
// Load composer
require __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Request;

$bot_api_key  = '1169529897:AAGkf8nmIikA1vMaVM234L2vAUCjwGqNTVk';
$bot_username = 'TalkedBot';
$hook_url     = 'https://simple-track.herokuapp.com/hook.php';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);


    $result = Request::sendMessage([
    'chat_id' => '-489273514',
    'text' => 'test'
]);
    if ($result->isOk()) {
        print_r($result);
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
     echo $e->getMessage();
}
