<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace Prooph\Psr7Middleware;

use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageDataAssertion;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\Exception\RuntimeException;
use Prooph\Psr7Middleware\Response\ResponseStrategy;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\QueryBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * One middleware for all message types (event, command and query)
 *
 * This class handles event, command and query messages depending on given request body data.
 */
final class MessageMiddleware implements Middleware
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

    /**
     * @param CommandBus $commandBus
     * @param QueryBus $queryBus
     * @param EventBus $eventBus
     * @param MessageFactory $messageFactory
     * @param ResponseStrategy $responseStrategy
     */
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

    /**
     * @interitdoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $payload = null;
        $messageName = 'UNKNOWN';

        try {
            $payload = $request->getParsedBody();

            if (is_array($payload) && isset($payload['message_name'])) {
                $messageName = $payload['message_name'];
            }

            MessageDataAssertion::assert($payload);

            $message = $this->messageFactory->createMessageFromArray($payload['message_name'], $payload);

            switch ($message->messageType()) {
                case Message::TYPE_COMMAND:
                    $this->commandBus->dispatch($message);
                    return $response->withStatus(Middleware::STATUS_CODE_ACCEPTED);
                case Message::TYPE_EVENT:
                    $this->eventBus->dispatch($message);
                    return $response->withStatus(Middleware::STATUS_CODE_ACCEPTED);
                case Message::TYPE_QUERY:
                    return $this->responseStrategy->fromPromise(
                        $this->queryBus->dispatch($message)
                    );
                default:
                    return $next(
                        $request,
                        $response,
                        new RuntimeException(
                            sprintf(
                                'Invalid message type "%s" for message "%s".',
                                $message->messageType(),
                                $messageName
                            ),
                            Middleware::STATUS_CODE_BAD_REQUEST
                        ));
            }
        } catch (\Assert\InvalidArgumentException $e) {
            return $next(
                $request,
                $response,
                new RuntimeException(
                    $e->getMessage(),
                    Middleware::STATUS_CODE_BAD_REQUEST,
                    $e
                ));
        } catch (\Exception $e) {
            return $next(
                $request,
                $response,
                new RuntimeException(
                    sprintf('An error occurred during dispatching of message "%s"', $messageName),
                    Middleware::STATUS_CODE_INTERNAL_SERVER_ERROR,
                    $e
                )
            );
        }
    }
}
