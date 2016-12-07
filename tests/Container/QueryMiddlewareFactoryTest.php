<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace ProophTest\Psr7Middleware\Container;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\Container\QueryMiddlewareFactory;
use Prooph\Psr7Middleware\Exception\InvalidArgumentException;
use Prooph\Psr7Middleware\QueryMiddleware;
use Prooph\ServiceBus\QueryBus;
use Prophecy\Prophecy\ObjectProphecy;

class QueryMiddlewareFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_implements_config_interop()
    {
        $factory = new QueryMiddlewareFactory();

        self::assertInstanceOf(\Interop\Config\RequiresConfigId::class, $factory);
        self::assertInstanceOf(\Interop\Config\RequiresMandatoryOptions::class, $factory);
        self::assertInstanceOf(\Interop\Config\ProvidesDefaultOptions::class, $factory);
    }

    /**
     * @test
     */
    public function it_creates_query_middleware()
    {
        $factory = new QueryMiddlewareFactory();
        $container = $this->getValidConfiguredContainer('query', null);

        $factory($container->reveal());
    }

    /**
     * @test
     */
    public function it_creates_query_middleware_with_another_gatherer()
    {
        $factory = new QueryMiddlewareFactory();
        $container = $this->getValidConfiguredContainer('query', new StubMetadataGatherer());

        $factory($container->reveal());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_option_is_missing(): void
    {
        $this->expectException(MandatoryOptionNotFoundException::class);

        $factory = new QueryMiddlewareFactory();
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'middleware' => [
                    'query' => [
                        'message_factory' => 'custom_message_factory',
                    ],
                ],
            ],
        ]);

        $factory($container->reveal());
    }

    /**
     * @test
     */
    public function it_creates_query_middleware_from_static_call(): void
    {
        $container = $this->getValidConfiguredContainer('other_config_id', null);

        $factory = [QueryMiddlewareFactory::class, 'other_config_id'];
        self::assertInstanceOf(QueryMiddleware::class, $factory($container->reveal()));
    }

    /**
     * @test
     */
    public function it_throws_invalid_argument_exception_without_container_on_static_call()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The first argument must be of type Interop\Container\ContainerInterface');

        QueryMiddlewareFactory::other_config_id();
    }

    private function getValidConfiguredContainer(string $configId, ?StubMetadataGatherer $gatherer): ObjectProphecy
    {
        $container = $this->prophesize(ContainerInterface::class);
        $strategy = $this->prophesize(\Prooph\Psr7Middleware\Response\ResponseStrategy::class);
        $messageFactory = $this->prophesize(MessageFactory::class);

        $config = [
            'prooph' => [
                'middleware' => [
                    $configId => [
                        'message_factory' => 'custom_message_factory',
                        'response_strategy' => 'JsonResponseStrategy',
                    ]
                ]
            ]
        ];

        if (null !== $gatherer) {
            $config['prooph']['middleware'][$configId]['metadata_gatherer'] = get_class($gatherer);
            $container->get(get_class($gatherer))->willReturn($gatherer);
        }

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);

        $container->has('custom_message_factory')->willReturn(true);
        $container->get('custom_message_factory')->willReturn($messageFactory);
        $container->has('JsonResponseStrategy')->willReturn(true);
        $container->get('JsonResponseStrategy')->willReturn($strategy);
        $container->has(QueryBus::class)->willReturn(true);
        $container->get(QueryBus::class)->willReturn($this->prophesize(QueryBus::class));

        return $container;
    }
}
