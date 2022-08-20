<?php
namespace HTNProtocol;

use pocketmine\scheduler\TaskHandler;

class Request {
    public function __construct(private TaskHandler $task, public \Closure $callback) {    }
    public function endTask(){
        $this->task->cancel();
    }
}