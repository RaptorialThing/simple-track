<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * User "/registerworker" command
 *
 * This command cancels the currently active conversation and
 * returns a message to let the user know which conversation it was.
 *
 * If no conversation is active, the returned message says so.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Logistics\Worker;
use Logistics\WorkerDB;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Request;

class RegisterWorkerCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'registerworker';

    /**
     * @var string
     */
    protected $description = 'Registration as worker';

    /**
     * @var string
     */
    protected $usage = '/registerworker';

    /**
     * @var string
     */
    protected $version = '0.3.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    protected $worker;

    /**
     * Main command execution if no DB connection is available
     *
     * @throws TelegramException
     */
    public function executeNoDb(): ServerResponse
    {
        return $this->replyToChat('Registration temporary unavailable (database connect error).');
    }

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
         $message = $this->getMessage();
         $chat    = $message->getChat();
         $user_id = $message->getFrom()->getId();
         $username = $message->getFrom()->getFirstName();
         $text    = trim($message->getText(true));
         $chat_id = $chat->getId();

        $data = [
            'chat_id'      => $chat_id,
            // Remove any keyboard by default
            'reply_markup' => Keyboard::remove(['selective' => true]),
        ];

        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            // Force reply is applied by default so it can work with privacy on
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());        
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        $state = $notes['state'] ?? 0;

        $result = Request::emptyResponse();

        switch ($state) {
            case 0:
                if ($text === '') {
                    $notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'Напишите код города :';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['address'] = $text;
                
            case 1:
                if ($message->getContact() === null) {
                    $notes['state'] = 1;
                    $this->conversation->update();

                    $data['reply_markup'] = (new Keyboard(
                        (new KeyboardButton('Share Contact'))->setRequestContact(true)
                    ))
                        ->setOneTimeKeyboard(true)
                        ->setResizeKeyboard(true)
                        ->setSelective(true);

                    $data['text'] = 'Поделитесь вашим номером:';

                    $result = Request::sendMessage($data);
                    break;
                }        
                    $notes['phone'] = $message->getContact()->getPhoneNumber();
            // No break!
            case 2:
                $this->worker = new Worker($user_id,$username,$notes['address'],true,$notes['phone']);

                $result = $this->worker->insert();

                if (!$result) {
                    $text = 'error saving to database';
                }

                $result = $this->worker->loadById($user_id);

                $text = $this->worker->arr2Str($result);

                foreach ($notes as $k => $v) {
                    $text .= PHP_EOL . ucfirst($k) . ': ' . $v;
                }
         

                if (!$result) {
                    $text = 'error fetching from database';
                } 

                $this->conversation->update();
                unset($notes['state']);
                $this->conversation->stop();

                break;
        }                

        /* $text = 'database connect test';

        $message = $this->getMessage();
        $userText    = $message->getText(true);

        if ($userText === '') {
            return $this->replyToChat('Command usage: ' . $this->getUsage());
        } 

        return $this->replyToChat($text);*/
        return $this->replyToChat($text);
    }


}
