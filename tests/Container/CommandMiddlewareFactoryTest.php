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

namespace ProophTest\Psr7Middleware\Container;

use Interop\Config\Exception\MandatoryOptionNotFoundException;
use PHPUnit\Framework\TestCase;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\CommandMiddleware;
use Prooph\Psr7Middleware\Container\CommandMiddlewareFactory;
use Prooph\Psr7Middleware\Exception\InvalidArgumentException;
use Prooph\Psr7Middleware\Response\ResponseStrategy;
use Prooph\ServiceBus\CommandBus;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class CommandMiddlewareFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_implements_config_interop(): void
    {
        $factory = new CommandMiddlewareFactory();

        self::assertInstanceOf(\Interop\Config\RequiresConfigId::class, $factory);
        self::assertInstanceOf(\Interop\Config\RequiresMandatoryOptions::class, $factory);
        self::assertInstanceOf(\Interop\Config\ProvidesDefaultOptions::class, $factory);
    }

    /**
     * @test
     */
    public function it_creates_command_middleware(): void
    {
        $factory = new CommandMiddlewareFactory();
        $container = $this->getValidConfiguredContainer('command', null);

        self::assertInstanceOf(CommandMiddleware::class, $factory($container->reveal()));
    }

    /**
     * @test
     */
    public function it_throws_exception_if_option_is_missing(): void
    {
        $this->expectException(MandatoryOptionNotFoundException::class);

        $factory = new CommandMiddlewareFactory();
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'middleware' => [
                    'command' => [
                    ],
                ],
            ],
        ]);

        $factory($container->reveal());
    }

    /**
     * @test
     */
    public function it_creates_command_middleware_from_static_call(): void
    {
        $container = $this->getValidConfiguredContainer('other_config_id', null);

        $factory = [CommandMiddlewareFactory::class, 'other_config_id'];
        self::assertInstanceOf(CommandMiddleware::class, $factory($container->reveal()));
    }

    /**
     * @test
     */
    public function it_creates_command_middleware_incl_metadata_gatherer(): void
    {
        $container = $this->getValidConfiguredContainer('other_config_id', new StubMetadataGatherer());

        $factory = [CommandMiddlewareFactory::class, 'other_config_id'];
        self::assertInstanceOf(CommandMiddleware::class, $factory($container->reveal()));
    }

    /**
     * @test
     */
    public function it_throws_invalid_argument_exception_without_container_on_static_call(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The first argument must be of type Psr\Container\ContainerInterface');

        CommandMiddlewareFactory::other_config_id();
    }

    private function getValidConfiguredContainer(string $configId, ?StubMetadataGatherer $gatherer): ObjectProphecy
    {
        $container = $this->prophesize(ContainerInterface::class);
        $strategy = $this->prophesize(ResponseStrategy::class);
        $messageFactory = $this->prophesize(MessageFactory::class);

        $config = [
            'prooph' => [
                'middleware' => [
                    $configId => [
                        'message_factory' => 'custom_message_factory',
                        'response_strategy' => 'JsonResponseStrategy',
                    ],
                ],
            ],
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
        $container->has(CommandBus::class)->willReturn(true);
        $container->get(CommandBus::class)->willReturn($this->prophesize(CommandBus::class));

        return $container;
    }
}
