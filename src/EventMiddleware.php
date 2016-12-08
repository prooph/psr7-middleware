<?php
/**
 * This file is part of prooph/psr7-middleware.
 * (c) 2016-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Psr7Middleware;

use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\Exception\RuntimeException;
use Prooph\ServiceBus\EventBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Event messages describe things that happened while your model handled a command.
 *
 * The EventBus is able to dispatch a message to n listeners. Each listener can be a message handler or message
 * producer. Like commands the EventBus doesn't return anything.
 */
final class EventMiddleware implements Middleware
{
    /**
     * Identifier to execute specific event
     *
     * @var string
     */
    const NAME_ATTRIBUTE = 'prooph_event_name';

    /**
     * Dispatches event
     *
     * @var EventBus
     */
    private $eventBus;

    /**
     * Creates message depending on event name
     *
     * @var MessageFactory
     */
    private $eventFactory;

    /**
     * Gatherer of metadata from the request object
     *
     * @var MetadataGatherer
     */
    private $metadataGatherer;

    public function __construct(
        EventBus $eventBus,
        MessageFactory $eventFactory,
        MetadataGatherer $metadataGatherer
    ) {
        $this->eventBus = $eventBus;
        $this->eventFactory = $eventFactory;
        $this->metadataGatherer = $metadataGatherer;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $eventName = $request->getAttribute(self::NAME_ATTRIBUTE);

        if (null === $eventName) {
            return $next(
                $request,
                $response,
                new RuntimeException(
                    sprintf('Event name attribute ("%s") was not found in request.', self::NAME_ATTRIBUTE),
                    Middleware::STATUS_CODE_BAD_REQUEST
                )
            );
        }

        try {
            $event = $this->eventFactory->createMessageFromArray($eventName, [
                'payload' => $request->getParsedBody(),
                'metadata' => $this->metadataGatherer->getFromRequest($request),
            ]);

            $this->eventBus->dispatch($event);

            return $response->withStatus(Middleware::STATUS_CODE_ACCEPTED);
        } catch (\Exception $e) {
            return $next(
                $request,
                $response,
                new RuntimeException(
                    sprintf('An error occurred during dispatching of event "%s"', $eventName),
                    Middleware::STATUS_CODE_INTERNAL_SERVER_ERROR,
                    $e
                )
            );
        }
    }
}
