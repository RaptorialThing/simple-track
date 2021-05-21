<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\Logistics;

use Longman\TelegramBot\Entities\Entity;
use Longman\TelegramBot\Commands\UserCommands\Logistics\WorkerDB;

/**
 * Class Logistics
 *
 * This object represents one worker.
 *
 *
 * @method string         getId()     worker id
 * @method string         getTitle()  worker name
 * @method string         getAddress() worker address
 * @method string         getContactPhone() worker contact phone number
 **/
class Worker 
{

    private string $id;

    private bool $statusIsFree;

    private string $name;

    private string $address;

    private string $phone;

    private $registrationDate;

    public function setter($prop,$val) {
        if (property_exists($this, $prop)) {

            $this->$prop = $val;
            return $this->getter($prop);
        }
            
        return ' get_object_vars will show object properties your prop is wrong ';
            
        }

    public function getter($prop) {
        return $this->$prop;
    }


    public function __construct(int $id, string $name, string $address, bool $statusIsFree, string $phone) {
        
        $this->id=$id;
        $this->name=$name;
        $this->address=$address;
        $this->statusIsFree=$statusIsFree;
        $this->phone=$phone;


    }

    public function insertWorker() {
        return WorkerDB::insertWorker($this->id,$this->name, $this->address, $this->statusIsFree, $this->phone);
    }

    public function load($phone) {
        return WorkerDB::selectWorkerByPhone($phone);
    }
   

}
