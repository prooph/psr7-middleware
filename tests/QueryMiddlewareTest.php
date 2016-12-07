<?php
/**
 * This file is part of the prooph/psr7-middleware.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\Psr7Middleware;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\MetadataGatherer;
use Prooph\Psr7Middleware\QueryMiddleware;
use Prooph\Psr7Middleware\Middleware;
use Prooph\Psr7Middleware\Response\ResponseStrategy;
use Prooph\ServiceBus\QueryBus;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\Promise;

/**
 * Test integrity of \Prooph\Psr7Middleware\QueryMiddleware
 */
class QueryMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_calls_next_with_exception_if_query_name_attribute_is_not_set(): void
    {
        $queryBus = $this->prophesize(QueryBus::class);
        $queryBus->dispatch(Argument::type(Message::class))->shouldNotBeCalled();

        $messageFactory = $this->prophesize(MessageFactory::class);

        $responseStrategy = $this->prophesize(ResponseStrategy::class);
        $responseStrategy->fromPromise(Argument::type(Promise::class))->shouldNotBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(QueryMiddleware::NAME_ATTRIBUTE)->willReturn(null)->shouldBeCalled();

        $response = $this->prophesize(ResponseInterface::class);
        $response->withStatus(Middleware::STATUS_CODE_BAD_REQUEST)->shouldBeCalled();

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request->reveal())->shouldNotBeCalled();

        $middleware = new QueryMiddleware($queryBus->reveal(), $messageFactory->reveal(), $responseStrategy->reveal(), $gatherer->reveal());

        $middleware($request->reveal(), $response->reveal(), Helper::callableWithExceptionResponse());
    }

    /**
     * @test
     */
    public function it_calls_next_with_exception_if_dispatch_failed(): void
    {
        $queryName = 'stdClass';
        $payload = ['user_id' => 123];

        $queryBus = $this->prophesize(QueryBus::class);
        $queryBus->dispatch(Argument::type(Message::class))->shouldBeCalled()->willThrow(
            new \Exception('Error')
        );

        $message = $this->prophesize(Message::class);

        $messageFactory = $this->prophesize(MessageFactory::class);
        $messageFactory
            ->createMessageFromArray(
                $queryName,
                ['payload' => $payload, 'metadata' => []]
            )
            ->willReturn($message->reveal())
            ->shouldBeCalled();

        $responseStrategy = $this->prophesize(ResponseStrategy::class);
        $responseStrategy->fromPromise(Argument::type(Promise::class))->shouldNotBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->shouldBeCalled();
        $request->getQueryParams()->willReturn($payload)->shouldBeCalled();
        $request->getAttribute(QueryMiddleware::NAME_ATTRIBUTE)->willReturn($queryName)->shouldBeCalled();

        $response = $this->prophesize(ResponseInterface::class);
        $response->withStatus(Middleware::STATUS_CODE_INTERNAL_SERVER_ERROR)->shouldBeCalled();

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request->reveal())->willReturn([])->shouldBeCalled();

        $middleware = new QueryMiddleware($queryBus->reveal(), $messageFactory->reveal(), $responseStrategy->reveal(), $gatherer->reveal());

        $middleware($request->reveal(), $response->reveal(), Helper::callableWithExceptionResponse());
    }

    /**
     * @test
     */
    public function it_dispatches_the_query(): void
    {
        $queryName = 'stdClass';
        $payload = ['user_id' => 123];

        $queryBus = $this->prophesize(QueryBus::class);
        $queryBus->dispatch(Argument::type(Message::class))->shouldBeCalled()->willReturn(
            $this->prophesize(Promise::class)->reveal()
        );

        $message = $this->prophesize(Message::class);

        $messageFactory = $this->prophesize(MessageFactory::class);
        $messageFactory
            ->createMessageFromArray(
                $queryName,
                ['payload' => $payload, 'metadata' => []]
            )
            ->willReturn($message->reveal())
            ->shouldBeCalled();

        $responseStrategy = $this->prophesize(ResponseStrategy::class);
        $responseStrategy->fromPromise(Argument::type(Promise::class))->shouldBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->shouldBeCalled();
        $request->getQueryParams()->willReturn($payload)->shouldBeCalled();
        $request->getAttribute(QueryMiddleware::NAME_ATTRIBUTE)->willReturn($queryName)->shouldBeCalled();

        $response = $this->prophesize(ResponseInterface::class);
        $response->withStatus(Argument::type('integer'))->shouldNotBeCalled();

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request)->shouldBeCalled();

        $middleware = new QueryMiddleware($queryBus->reveal(), $messageFactory->reveal(), $responseStrategy->reveal(), $gatherer->reveal());
        $middleware($request->reveal(), $response->reveal(), Helper::callableShouldNotBeCalledWithException($this));
    }

    /**
     * @test
     */
    public function it_dispatches_the_query_with_post_data(): void
    {
        $queryName = 'stdClass';
        $parsedBody = ['filter' => []];
        $payload = ['user_id' => 123];

        $queryBus = $this->prophesize(QueryBus::class);
        $queryBus->dispatch(Argument::type(Message::class))->shouldBeCalled()->willReturn(
            $this->prophesize(Promise::class)->reveal()
        );

        $message = $this->prophesize(Message::class);

        $messageFactory = $this->prophesize(MessageFactory::class);
        $messageFactory
            ->createMessageFromArray(
                $queryName,
                ['payload' => array_merge($payload, ['data' => $parsedBody]), 'metadata' => []]
            )
            ->willReturn($message->reveal())
            ->shouldBeCalled();

        $responseStrategy = $this->prophesize(ResponseStrategy::class);
        $responseStrategy->fromPromise(Argument::type(Promise::class))->shouldBeCalled();

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->willReturn('POST')->shouldBeCalled();
        $request->getParsedBody()->willReturn($parsedBody)->shouldBeCalled();
        $request->getQueryParams()->willReturn($payload)->shouldBeCalled();
        $request->getAttribute(QueryMiddleware::NAME_ATTRIBUTE)->willReturn($queryName)->shouldBeCalled();

        $response = $this->prophesize(ResponseInterface::class);
        $response->withStatus(Argument::type('integer'))->shouldNotBeCalled();

        $gatherer = $this->prophesize(MetadataGatherer::class);
        $gatherer->getFromRequest($request)->shouldBeCalled();

        $middleware = new QueryMiddleware($queryBus->reveal(), $messageFactory->reveal(), $responseStrategy->reveal(), $gatherer->reveal());
        $middleware($request->reveal(), $response->reveal(), Helper::callableShouldNotBeCalledWithException($this));
    }
}
