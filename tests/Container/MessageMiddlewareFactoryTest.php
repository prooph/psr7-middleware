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
use Prooph\Psr7Middleware\Container\MessageMiddlewareFactory;
use Prooph\Psr7Middleware\Exception\InvalidArgumentException;
use Prooph\Psr7Middleware\MessageMiddleware;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\QueryBus;

class MessageMiddlewareFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_implements_config_interop()
    {
        $factory = new MessageMiddlewareFactory();

        self::assertInstanceOf(\Interop\Config\RequiresConfigId::class, $factory);
        self::assertInstanceOf(\Interop\Config\RequiresMandatoryOptions::class, $factory);
        self::assertInstanceOf(\Interop\Config\ProvidesDefaultOptions::class, $factory);
    }

    /**
     * @test
     */
    public function it_creates_message_middleware()
    {
        $factory = new MessageMiddlewareFactory();
        $container = $this->getValidConfiguredContainer('message');

        $factory($container->reveal());
    }

    /**
     * @test
     * @expectedException \Interop\Config\Exception\MandatoryOptionNotFoundException
     */
    public function it_throws_exception_if_option_is_missing()
    {
        $factory = new MessageMiddlewareFactory();
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'middleware' => [
                    'message' => [
                        'message_factory' => 'custom_message_factory',
                    ]
                ]
            ]
        ]);

        $factory($container->reveal());
    }

    /**
     * @test
     */
    public function it_creates_message_middleware_from_static_call()
    {
        $container = $this->getValidConfiguredContainer('other_config_id');

        $factory = [MessageMiddlewareFactory::class, 'other_config_id'];
        self::assertInstanceOf(MessageMiddleware::class, $factory($container->reveal()));
    }

    /**
     * @test
     * @expectedException \Prooph\Psr7Middleware\Exception\InvalidArgumentException
     * @expectedExceptionMessage The first argument must be of type Interop\Container\ContainerInterface
     */
    public function it_throws_invalid_argument_exception_without_container_on_static_call()
    {
        MessageMiddlewareFactory::other_config_id();
    }

    /**
     * @param string $configId
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    private function getValidConfiguredContainer($configId)
    {
        $container = $this->prophesize(ContainerInterface::class);
        $strategy = $this->prophesize(\Prooph\Psr7Middleware\Response\ResponseStrategy::class);
        $messageFactory = $this->prophesize(MessageFactory::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'middleware' => [
                    $configId => [
                        'message_factory' => 'custom_message_factory',
                        'response_strategy' => 'JsonResponseStrategy',
                    ]
                ]
            ]
        ]);

        $container->has('custom_message_factory')->willReturn(true);
        $container->get('custom_message_factory')->willReturn($messageFactory);
        $container->has('JsonResponseStrategy')->willReturn(true);
        $container->get('JsonResponseStrategy')->willReturn($strategy);

        $container->has(CommandBus::class)->willReturn(true);
        $container->get(CommandBus::class)->willReturn($this->prophesize(CommandBus::class));
        $container->has(EventBus::class)->willReturn(true);
        $container->get(EventBus::class)->willReturn($this->prophesize(EventBus::class));
        $container->has(QueryBus::class)->willReturn(true);
        $container->get(QueryBus::class)->willReturn($this->prophesize(QueryBus::class));

        return $container;
    }
}
