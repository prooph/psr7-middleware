<?php
/**
 * This file is part of prooph/psr7-middleware.
 * (c) 2016-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Psr7Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Webimpress\HttpMiddlewareCompatibility\HandlerInterface;
use Webimpress\HttpMiddlewareCompatibility\MiddlewareInterface;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageDataAssertion;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\Exception\RuntimeException;
use Prooph\Psr7Middleware\Response\ResponseStrategy;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\QueryBus;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * One middleware for all message types (event, command and query)
 *
 * This class handles event, command and query messages depending on given request body data.
 */
final class MessageMiddleware implements MiddlewareInterface
{
    /**
     * Dispatches command
     *
     * @var CommandBus
     */
    private $commandBus;

    /**
     * Dispatches query
     *
     * @var QueryBus
     */
    private $queryBus;

    /**
     * Dispatches event
     *
     * @var EventBus
     */
    private $eventBus;

    /**
     * Creates message depending on name
     *
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * Generate HTTP response with result from Promise
     *
     * @var ResponseStrategy
     */
    private $responseStrategy;

    public function __construct(
        CommandBus $commandBus,
        QueryBus $queryBus,
        EventBus $eventBus,
        MessageFactory $messageFactory,
        ResponseStrategy $responseStrategy
    ) {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->eventBus = $eventBus;
        $this->messageFactory = $messageFactory;
        $this->responseStrategy = $responseStrategy;
    }

    public function process(ServerRequestInterface $request, HandlerInterface $handler)
    {
        $payload = null;
        $messageName = 'UNKNOWN';

        try {
            $payload = $request->getParsedBody();

            if (is_array($payload) && isset($payload['message_name'])) {
                $messageName = $payload['message_name'];
            }

            $messageName = $request->getAttribute('message_name', $messageName);

            $payload['message_name'] = $messageName;

            if (! isset($payload['uuid'])) {
                $payload['uuid'] = Uuid::uuid4();
            }

            if (! isset($payload['created_at'])) {
                $payload['created_at'] = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
            }

            if (! isset($payload['metadata'])) {
                $payload['metadata'] = [];
            }

            MessageDataAssertion::assert($payload);

            $message = $this->messageFactory->createMessageFromArray($messageName, $payload);

            switch ($message->messageType()) {
                case Message::TYPE_COMMAND:
                    $this->commandBus->dispatch($message);

                    return $this->responseStrategy->withStatus(StatusCodeInterface::STATUS_ACCEPTED);
                case Message::TYPE_EVENT:
                    $this->eventBus->dispatch($message);

                    return $this->responseStrategy->withStatus(StatusCodeInterface::STATUS_ACCEPTED);
                case Message::TYPE_QUERY:
                    return $this->responseStrategy->fromPromise(
                        $this->queryBus->dispatch($message)
                    );
                default:
                    throw new RuntimeException(
                        sprintf(
                            'Invalid message type "%s" for message "%s".',
                            $message->messageType(),
                            $messageName
                        ),
                        StatusCodeInterface::STATUS_BAD_REQUEST
                    );
            }
        } catch (\Assert\InvalidArgumentException $e) {
            throw new RuntimeException(
                $e->getMessage(),
                StatusCodeInterface::STATUS_BAD_REQUEST,
                $e
            );
        } catch (\Throwable $e) {
            throw new RuntimeException(
                sprintf('An error occurred during dispatching of message "%s"', $messageName),
                StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
                $e
            );
        }
    }
}
