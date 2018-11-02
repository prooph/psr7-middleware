<?php

/**
 * This file is part of prooph/psr7-middleware.
 * (c) 2016-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\Psr7Middleware;

use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\EventMiddleware;
use Prooph\Psr7Middleware\Exception\RuntimeException;
use Prooph\Psr7Middleware\MetadataGatherer;
use Prooph\Psr7Middleware\Response\ResponseStrategy;
use Prooph\ServiceBus\EventBus;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webimpress\HttpMiddlewareCompatibility\HandlerInterface;

/**
 * Test integrity of \Prooph\Psr7Middleware\EventMiddleware
 */
class EventMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_if_event_name_attribute_is_not_set(): void
    {
        $eventBus = $this->prophesize(EventBus::class);
        $eventBus->dispatch(Argument::type(Message::class))->shouldNotBeCalled();

        $messageFactory = $this->prophesize(MessageFactory::class);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(EventMiddleware::NAME_ATTRIBUTE)->willReturn(null)->shouldBeCalled();

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request)->shouldNotBeCalled();

        $responseStrategy = $this->prophesize(ResponseStrategy::class);
        $responseStrategy->withStatus()->shouldNotBeCalled();

        $handler = $this->prophesize(HandlerInterface::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('Event name attribute ("%s") was not found in request.', EventMiddleware::NAME_ATTRIBUTE));

        $middleware = new EventMiddleware($eventBus->reveal(), $messageFactory->reveal(), $gatherer->reveal(), $responseStrategy->reveal());

        $middleware->process($request->reveal(), $handler->reveal());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_dispatch_failed(): void
    {
        $eventName = 'stdClass';
        $payload = ['user_id' => 123];

        $eventBus = $this->prophesize(EventBus::class);
        $eventBus->dispatch(Argument::type(Message::class))->willThrow(
            new \Exception('Error')
        );

        $message = $this->prophesize(\Prooph\Common\Messaging\Message::class);

        $messageFactory = $this->prophesize(MessageFactory::class);
        $messageFactory
            ->createMessageFromArray(
                $eventName,
                ['payload' => $payload, 'metadata' => []]
            )
            ->willReturn($message->reveal());

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getParsedBody()->willReturn($payload)->shouldBeCalled();
        $request->getAttribute(EventMiddleware::NAME_ATTRIBUTE)->willReturn($eventName)->shouldBeCalled();

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request)->shouldBeCalled();

        $responseStrategy = $this->prophesize(ResponseStrategy::class);
        $responseStrategy->withStatus()->shouldNotBeCalled();

        $handler = $this->prophesize(HandlerInterface::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        $this->expectExceptionMessage('An error occurred during dispatching of event "stdClass"');

        $middleware = new EventMiddleware($eventBus->reveal(), $messageFactory->reveal(), $gatherer->reveal(), $responseStrategy->reveal());

        $middleware->process($request->reveal(), $handler->reveal());
    }

    /**
     * @test
     */
    public function it_dispatches_the_event(): void
    {
        $eventName = 'stdClass';
        $payload = ['user_id' => 123];

        $eventBus = $this->prophesize(EventBus::class);
        $eventBus->dispatch(Argument::type(Message::class))->shouldBeCalled();

        $message = $this->prophesize(\Prooph\Common\Messaging\Message::class);

        $messageFactory = $this->prophesize(MessageFactory::class);
        $messageFactory
            ->createMessageFromArray(
                $eventName,
                ['payload' => $payload, 'metadata' => []]
            )
            ->willReturn($message->reveal())
            ->shouldBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getParsedBody()->willReturn($payload)->shouldBeCalled();
        $request->getAttribute(EventMiddleware::NAME_ATTRIBUTE)->willReturn($eventName)->shouldBeCalled();

        $response = $this->prophesize(ResponseInterface::class);

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request->reveal())->willReturn([])->shouldBeCalled();

        $responseStrategy = $this->prophesize(ResponseStrategy::class);
        $responseStrategy->withStatus(StatusCodeInterface::STATUS_ACCEPTED)->willReturn($response);

        $handler = $this->prophesize(HandlerInterface::class);

        $middleware = new EventMiddleware($eventBus->reveal(), $messageFactory->reveal(), $gatherer->reveal(), $responseStrategy->reveal());
        $this->assertSame($response->reveal(), $middleware->process($request->reveal(), $handler->reveal()));
    }
}
