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

namespace ProophTest\Psr7Middleware;

use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\CommandMiddleware;
use Prooph\Psr7Middleware\Exception\RuntimeException;
use Prooph\Psr7Middleware\MetadataGatherer;
use Prooph\Psr7Middleware\Response\ResponseStrategy;
use Prooph\ServiceBus\CommandBus;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webimpress\HttpMiddlewareCompatibility\HandlerInterface;

/**
 * Test integrity of \Prooph\Psr7Middleware\CommandMiddleware
 */
class CommandMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_if_command_name_attribute_is_not_set(): void
    {
        $commandBus = $this->prophesize(CommandBus::class);
        $commandBus->dispatch(Argument::type(Message::class))->shouldNotBeCalled();

        $messageFactory = $this->prophesize(MessageFactory::class);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(CommandMiddleware::NAME_ATTRIBUTE)->willReturn(null)->shouldBeCalled();

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request)->shouldNotBeCalled();

        $responseStrategy = $this->prophesize(ResponseStrategy::class);
        $responseStrategy->withStatus()->shouldNotBeCalled();

        $handler = $this->prophesize(HandlerInterface::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Command name attribute ("%s") was not found in request.', CommandMiddleware::NAME_ATTRIBUTE));

        $middleware = new CommandMiddleware($commandBus->reveal(), $messageFactory->reveal(), $gatherer->reveal(), $responseStrategy->reveal());

        $middleware->process($request->reveal(), $handler->reveal());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_dispatch_failed(): void
    {
        $commandName = 'stdClass';
        $payload = ['user_id' => 123];

        $commandBus = $this->prophesize(CommandBus::class);
        $commandBus->dispatch(Argument::type(Message::class))->willThrow(
            new \Exception('Error')
        );

        $message = $this->prophesize(Message::class);

        $messageFactory = $this->prophesize(MessageFactory::class);
        $messageFactory
            ->createMessageFromArray(
                $commandName,
                ['payload' => $payload, 'metadata' => []]
            )
            ->willReturn($message->reveal());

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(CommandMiddleware::NAME_ATTRIBUTE)->willReturn($commandName);

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request)->shouldNotBeCalled();

        $responseStrategy = $this->prophesize(ResponseStrategy::class);
        $responseStrategy->withStatus()->shouldNotBeCalled();

        $handler = $this->prophesize(HandlerInterface::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        $this->expectExceptionMessage('An error occurred during dispatching of command "stdClass"');

        $middleware = new CommandMiddleware($commandBus->reveal(), $messageFactory->reveal(), $gatherer->reveal(), $responseStrategy->reveal());

        $middleware->process($request->reveal(), $handler->reveal());
    }

    /**
     * @test
     */
    public function it_dispatches_the_command(): void
    {
        $commandName = 'stdClass';
        $payload = ['user_id' => 123];

        $commandBus = $this->prophesize(CommandBus::class);
        $commandBus->dispatch(Argument::type(Message::class))->shouldBeCalled();

        $message = $this->prophesize(Message::class);

        $messageFactory = $this->prophesize(MessageFactory::class);
        $messageFactory
            ->createMessageFromArray(
                $commandName,
                ['payload' => $payload, 'metadata' => []]
            )
            ->willReturn($message->reveal())
            ->shouldBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getParsedBody()->willReturn($payload)->shouldBeCalled();
        $request->getAttribute(CommandMiddleware::NAME_ATTRIBUTE)->willReturn($commandName)->shouldBeCalled();

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request->reveal())->willReturn([])->shouldBeCalled();

        $response = $this->prophesize(ResponseInterface::class);

        $responseStrategy = $this->prophesize(ResponseStrategy::class);
        $responseStrategy->withStatus(StatusCodeInterface::STATUS_ACCEPTED)->willReturn($response);

        $handler = $this->prophesize(HandlerInterface::class);

        $middleware = new CommandMiddleware($commandBus->reveal(), $messageFactory->reveal(), $gatherer->reveal(), $responseStrategy->reveal());
        $this->assertSame($response->reveal(), $middleware->process($request->reveal(), $handler->reveal()));
    }
}
