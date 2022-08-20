<?php
namespace HTNProtocol;

class TestThread extends \pocketmine\thread\Thread
{
    private $foo = null;
    public function __construct(
        private \pocketmine\snooze\SleeperNotifier $notifier,
        private \Threaded $buffer
    ) {
    }
    public function onRun(): void
    {
        $i = 0;
        while (true) {
            if ($i === 10) {
                $i = 0;
                $this->buffer[] = "I has reached 10";
                $this->notifier->wakeupSleeper();
            }
            $i++;
            sleep(2);
        }
    }
    public function setFoo($foo)
    {
        $this->foo;
    }
}
