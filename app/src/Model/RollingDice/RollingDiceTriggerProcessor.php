<?php

declare(strict_types=1);

namespace App\Model\RollingDice;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class RollingDiceTriggerProcessor
{
    private ProducerInterface $rollingDiceTriggerProducer;

    /**
     * @param ProducerInterface $rollingDiceTriggerProducer
     */
    public function __construct(
        ProducerInterface $rollingDiceTriggerProducer
    ) {
        $this->rollingDiceTriggerProducer = $rollingDiceTriggerProducer;
    }

    // can be improved by publishing serialized message in another service (like with a custom producer)
    public function process(): void
    {
        $this->rollingDiceTriggerProducer->publish('Test meassage');
    }
}
