<?php
// Load composer
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = '1169529897:AAGkf8nmIikA1vMaVM234L2vAUCjwGqNTVk';
$bot_username = 'TalkedBot';
$hook_url     = 'https://simple-track.herokuapp.com/hook.php';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Set webhook
    $result = $telegram->setWebhook($hook_url,['certificate' => 'certificate/certificates']);
    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
     echo $e->getMessage();
}
