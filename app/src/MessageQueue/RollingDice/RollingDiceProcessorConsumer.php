<?php

declare(strict_types=1);

namespace App\MessageQueue\RollingDice;

use App\MessageQueue\AbstractRetryableBatchConsumer;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class RollingDiceProcessorConsumer extends AbstractRetryableBatchConsumer
{
    /**
     * @return int
     */
    protected static function getRetryMaxAttemptsCount(): int
    {
        return 10;
    }

    /**
     * @return int
     */
    protected static function getRetryDelay(): int
    {
        return 2;
    }

    /**
     * @param ProducerInterface $rollingDiceTriggerRetryProducer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProducerInterface $rollingDiceTriggerRetryProducer,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $rollingDiceTriggerRetryProducer,
            $logger
        );
    }

    /**
     * @param AMQPMessage[] $messages
     * @return void
     */
    protected function processMessages(array $messages): void
    {
        foreach ($messages as $index => $message) {
            $rollingDiceResult = $this->rollDice();

            if ($rollingDiceResult < 5) {
                $this->getLogger()->info('Too low result.', [
                    'rollingDiceResult' => $rollingDiceResult,
                ]);

                throw new \InvalidArgumentException('Too low result.');
            }

            $this->getLogger()->info('Rolling dice successful!', [
                'rollingDiceResult' => $rollingDiceResult,
            ]);

            $this->markMessageAsProcessed($index);
        }
    }

    /**
     * @return int
     */
    private function rollDice(): int
    {
        return random_int(1, 6);
    }
}
