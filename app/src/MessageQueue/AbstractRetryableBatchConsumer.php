<?php

declare(strict_types=1);

namespace App\MessageQueue;

use OldSound\RabbitMqBundle\RabbitMq\BatchConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

abstract class AbstractRetryableBatchConsumer implements BatchConsumerInterface
{
    private const RETRY_ATTEMPTS_COUNT_HEADER_NAME = 'retry-attempts-count';

    private const APPLICATION_HEADERS_KEY = 'application_headers';

    private const APPLICATION_HEADER_INT_TYPE = 'I';

    private ProducerInterface $retryQueueProducer;

    private LoggerInterface $logger;

    /** @var AMQPMessage[] */
    private array $notProcessedMessages = [];

    abstract protected function processMessages(array $messages): void;

    /**
     * @return int
     */
    protected static function getRetryMaxAttemptsCount(): int
    {
        return 24;
    }

    /**
     * @return int
     */
    protected static function getRetryDelay(): int
    {
        return 300;
    }

    /**
     * @param ProducerInterface $retryQueueProducer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProducerInterface $retryQueueProducer,
        LoggerInterface $logger
    ) {
        $this->retryQueueProducer = $retryQueueProducer;
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage[] $messages
     * @return bool
     */
    public function batchExecute(array $messages): bool
    {
        $this->logger->info('Start processing messages.');

        $this->notProcessedMessages = $messages;

        try {
            // AMQPMessage-s can be replaced with internal messages with specific data
            $this->processMessages($messages);
        } catch (\Throwable) {
            $this->logger->info('Messages processing error.');

            foreach ($this->notProcessedMessages as $notProcessedMessage) {
                $this->logger->error('Retrying not processed message.', [
                    'message' => $notProcessedMessage->body
                ]);

                $this->retryMessage($notProcessedMessage);
            }
        }

        $this->logger->info('Finish processing messages.');

        return true;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param int $index
     * @return void
     */
    protected function markMessageAsProcessed(int $index): void
    {
        unset($this->notProcessedMessages[$index]);
    }

    /**
     * todo functionality can be moved to special message retrier
     *
     * @param AMQPMessage $message
     * @param $routingKey
     * @return void
     */
    private function retryMessage(AMQPMessage $message, $routingKey = ''): void
    {
        $messageRetryAttemptsCount = $this->getMessageRetryAttemptsCount($message);

        if ($messageRetryAttemptsCount >= static::getRetryMaxAttemptsCount()) {
            $this->logger->critical('Message retries count exceeded.', [
                'message' => $message->body,
            ]);

            return;
        }

        $this->retryQueueProducer->publish(
            $message->getBody(),
            $routingKey,
            [
                'expiration' => static::getRetryDelay() * 1000,
                self::APPLICATION_HEADERS_KEY => [
                    self::RETRY_ATTEMPTS_COUNT_HEADER_NAME => [
                        self::APPLICATION_HEADER_INT_TYPE,
                        $messageRetryAttemptsCount + 1
                    ]
                ],
            ]
        );
    }

    /**
     * @param AMQPMessage $message
     * @return int
     */
    private function getMessageRetryAttemptsCount(AMQPMessage $message): int
    {
        return (int) ($this->getApplicationHeaderValue($message, self::RETRY_ATTEMPTS_COUNT_HEADER_NAME) ?? 0);
    }

    /**
     * @param AMQPMessage $message
     * @param string $key
     * @return mixed|null
     */
    private function getApplicationHeaderValue(AMQPMessage $message, string $key): mixed
    {
        $headerCustomerValue = null;

        if ($message->has(self::APPLICATION_HEADERS_KEY)) {
            $messageHeaders = $message->get(self::APPLICATION_HEADERS_KEY)->getNativeData();

            $headerCustomerValue = $messageHeaders[$key] ?? null;
        }

        return $headerCustomerValue;
    }
}
