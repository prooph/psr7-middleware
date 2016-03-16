<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace ProophTest\Psr7Middleware;

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\EventMiddleware;
use Prooph\Psr7Middleware\MetadataGatherer;
use Prooph\Psr7Middleware\Middleware;
use Prooph\ServiceBus\EventBus;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Test integrity of \Prooph\Psr7Middleware\EventMiddleware
 */
class EventMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_calls_next_with_exception_if_event_name_attribute_is_not_set()
    {
        $eventBus = $this->prophesize(EventBus::class);
        $eventBus->dispatch(Argument::type(Message::class))->shouldNotBeCalled();

        $messageFactory = $this->prophesize(MessageFactory::class);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(EventMiddleware::NAME_ATTRIBUTE)->willReturn(null)->shouldBeCalled();

        $response = $this->prophesize(ResponseInterface::class);
        $response->withStatus(Middleware::STATUS_CODE_BAD_REQUEST)->shouldBeCalled();

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request)->shouldBeCalled();

        $middleware = new EventMiddleware($eventBus->reveal(), $messageFactory->reveal(), $gatherer->reveal());

        $middleware($request->reveal(), $response->reveal(), Helper::callableWithExceptionResponse());
    }

    /**
     * @test
     */
    public function it_calls_next_with_exception_if_dispatch_failed()
    {
        $eventName = 'stdClass';
        $payload = ['user_id' => 123];

        $eventBus = $this->prophesize(EventBus::class);
        $eventBus->dispatch(Argument::type(Message::class))->shouldBeCalled()->willThrow(
            new \Exception('Error')
        );

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
        $response->withStatus(Middleware::STATUS_CODE_INTERNAL_SERVER_ERROR)->shouldBeCalled();

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request)->shouldBeCalled();

        $middleware = new EventMiddleware($eventBus->reveal(), $messageFactory->reveal(), $gatherer->reveal());

        $middleware($request->reveal(), $response->reveal(), Helper::callableWithExceptionResponse());
    }

    /**
     * @test
     */
    public function it_dispatches_the_event()
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
        $response->withStatus(Middleware::STATUS_CODE_ACCEPTED)->shouldBeCalled();

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request)->shouldBeCalled();

        $middleware = new EventMiddleware($eventBus->reveal(), $messageFactory->reveal(), $gatherer->reveal());
        $middleware($request->reveal(), $response->reveal(), Helper::callableShouldNotBeCalledWithException($this));
    }
}
