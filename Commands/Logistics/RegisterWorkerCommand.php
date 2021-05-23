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
        $user    = $message->getFrom();
        $text    = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        // Preparing response
        $data = [
            'chat_id'      => $chat_id,
            // Remove any keyboard by default
            'reply_markup' => Keyboard::remove(['selective' => true]),
        ];

        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            // Force reply is applied by default so it can work with privacy on
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }

        // Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        // Load any existing notes from this conversation
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        // Load the current state of the conversation
        $state = $notes['state'] ?? 0;

        $result = Request::emptyResponse();

        // State machine
        // Every time a step is achieved the state is updated
        switch ($state) {
            case 0:
                if ($text === '') {
                    $notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'Ваше имя:';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['name'] = $text;
                $text          = '';

            // No break!
            case 1:
                if ($message->getLocation() === null) {
                    $notes['state'] = 1;
                    $this->conversation->update();

                    $data['reply_markup'] = (new Keyboard(
                        (new KeyboardButton('Поделиться местоположением'))->setRequestLocation(true)
                    ))
                        ->setOneTimeKeyboard(true)
                        ->setResizeKeyboard(true)
                        ->setSelective(true);

                    $data['text'] = 'Поделиться местоположением для поиска заказов рядом:';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['longitude'] = $message->getLocation()->getLongitude();
                $notes['latitude']  = $message->getLocation()->getLatitude();
                $notes['address'] = ['longitude'=>$notes['longitude'],'latitude'=>$notes['latitude']];
            // No break!
            case 2:
                if ($message->getContact() === null) {
                    $notes['state'] = 2;
                    $this->conversation->update();

                    $data['reply_markup'] = (new Keyboard(
                        (new KeyboardButton('Поделиться телефоном'))->setRequestContact(true)
                    ))
                        ->setOneTimeKeyboard(true)
                        ->setResizeKeyboard(true)
                        ->setSelective(true);

                    $data['text'] = 'Поделитесь телефоном для поиска заказов:';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['phone_number'] = $message->getContact()->getPhoneNumber();

            // No break!
            case 3:
                $this->conversation->update();


                $this->worker = new Worker($user_id,$notes['name'],$notes['address'],true,$notes['phone_number']);
                unset($notes['state']);
                $this->conversation->update();

                $resultQuery = $this->worker->insert();

                if (!$resultquery) {
                    $out_text = 'error saving to database (insert)' . PHP_EOL;
                }

                $resultQuery = $this->worker->loadById($user_id);
 
                if (!$resultquery) {
                    $out_text .= 'error saving to database (select)' . PHP_EOL;
                }

                $out_text .= '/Спасибо за регистрацию. Заказы будут появляться в этом чате:' . PHP_EOL;
                unset($notes['state']);
                foreach ($notes as $k => $v) {
                    $out_text .= PHP_EOL . ucfirst($k) . ': ' . $v;
                }

                $data['text'] = $out_text;

                $this->conversation->stop();

                $result = Request::sendMessage($data);
                break;
        }

        return $result;
    }


}

