<?php

namespace Alten\BehatContext\AmqpAdapter;

use Symfony\Component\Messenger\Transport\AmqpExt\Connection as AmqpConnection;

/**
 * Class SymfonyMessengerAdapter
 */
class SymfonyMessengerAdapter implements AdapterInterface
{
    /** @var array */
    private $transports;

    /**
     * @param array $transports transports
     */
    public function __construct(array $transports)
    {
        $this->transports = $transports;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(string $transport, string $content, string $command = null): void
    {
        $this->getAmqpConnection($transport)->publish($content, [
            'content_type' => 'text/plain',
            'type' => $command,
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function countMessagesInTransport(string $transport): int
    {
        $queues = $this->getAmqpConnection($transport)->getQueueNames();
        if (count($queues) > 1) {
            throw new \Exception(sprintf('AMQP Connection `%s` has more than one queue', $transport));
        }
        return $this->getAmqpConnection($transport)->queue($queues[0])->declare();
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledgeAndGetNextMessageInTransport(string $transport): ?string
    {
        $queues = $this->getAmqpConnection($transport)->getQueueNames();
        if (count($queues) > 1) {
            throw new \Exception(sprintf('AMQP Connection `%s` has more than one queue', $transport));
        }

        $envelope = $this->getAmqpConnection($transport)->queue($queues[0])->get(AMQP_AUTOACK);

        if (null === $envelope) {
            return null;
        }

        return $envelope->getBody();
    }


    /**
     * {@inheritdoc}
     */
    public function purgeAllTransports(): void
    {
        foreach ($this->transports as $transportName => $dsn) {
            $this->purgeTransport($transportName);
        }
    }


    /**
     * {@inheritdoc}
     */
    public function purgeTransport(string $transport): void
    {
        $queues = $this->getAmqpConnection($transport)->getQueueNames();
        if (count($queues) > 1) {
            throw new \Exception(sprintf('AMQP Connection `%s` has more than one queue', $transport));
        }

        $this->getAmqpConnection($transport)->queue($queues[0])->purge();

    }


    /**
     * {@inheritdoc}
     */
    public function setupQueues(): void
    {
        foreach ($this->transports as $transportName => $dsn) {
            $this->getAmqpConnection($transportName)->setup();
        }
    }

    /**
     * @param string $transport
     *
     * @return AmqpConnection
     *
     * @throws \Exception
     */
    private function getAmqpConnection(string $transport): AmqpConnection
    {
        if (false === array_key_exists($transport, $this->transports)) {
            throw new \Exception(sprintf('AMQP Connection with name %s does not exist.', $transport));
        }

        $dsn = $this->transports[$transport];

        if (preg_match('/^env\((?P<env_var>.*)\)$/', $dsn, $matches)) {
            $dsn = getenv($matches['env_var']);
        }

        return AmqpConnection::fromDsn($dsn);
    }
}
