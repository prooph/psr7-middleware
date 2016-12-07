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

namespace ProophTest\Psr7Middleware\Container;

use Interop\Config\Exception\MandatoryOptionNotFoundException;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\Container\EventMiddlewareFactory;
use Prooph\Psr7Middleware\EventMiddleware;
use Prooph\Psr7Middleware\Exception\InvalidArgumentException;
use Prooph\ServiceBus\EventBus;
use Prophecy\Prophecy\ObjectProphecy;

class EventMiddlewareFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_implements_config_interop(): void
    {
        $factory = new EventMiddlewareFactory();

        self::assertInstanceOf(\Interop\Config\RequiresConfigId::class, $factory);
        self::assertInstanceOf(\Interop\Config\RequiresMandatoryOptions::class, $factory);
        self::assertInstanceOf(\Interop\Config\ProvidesDefaultOptions::class, $factory);
    }

    /**
     * @test
     */
    public function it_creates_event_middleware(): void
    {
        $factory = new EventMiddlewareFactory();
        $container = $this->getValidConfiguredContainer('event');

        $factory($container->reveal());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_option_is_missing(): void
    {
        $this->expectException(MandatoryOptionNotFoundException::class);

        $factory = new EventMiddlewareFactory();
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'middleware' => [
                    'event' => [
                    ]
                ]
            ]
        ]);

        $factory($container->reveal());
    }

    /**
     * @test
     */
    public function it_creates_event_middleware_from_static_call(): void
    {
        $container = $this->getValidConfiguredContainer('other_config_id');

        $factory = [EventMiddlewareFactory::class, 'other_config_id'];
        self::assertInstanceOf(EventMiddleware::class, $factory($container->reveal()));
    }

    /**
     * @test
     */
    public function it_throws_invalid_argument_exception_without_container_on_static_call(): void
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'The first argument must be of type Interop\Container\ContainerInterface'
        );
        EventMiddlewareFactory::other_config_id();
    }

    private function getValidConfiguredContainer(string $configId): ObjectProphecy
    {
        $container = $this->prophesize(ContainerInterface::class);
        $messageFactory = $this->prophesize(MessageFactory::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'middleware' => [
                    $configId => [
                        'message_factory' => 'custom_message_factory'
                    ]
                ]
            ]
        ]);

        $container->has('custom_message_factory')->willReturn(true);
        $container->get('custom_message_factory')->willReturn($messageFactory);
        $container->has(EventBus::class)->willReturn(true);
        $container->get(EventBus::class)->willReturn($this->prophesize(EventBus::class));

        return $container;
    }
}
