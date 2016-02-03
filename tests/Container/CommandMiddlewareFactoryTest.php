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
use Prooph\Psr7Middleware\CommandMiddleware;
use Prooph\Psr7Middleware\Container\CommandMiddlewareFactory;
use Prooph\ServiceBus\CommandBus;

class CommandMiddlewareFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_implements_config_interop()
    {
        $factory = new CommandMiddlewareFactory();

        self::assertInstanceOf(\Interop\Config\RequiresContainerId::class, $factory);
        self::assertInstanceOf(\Interop\Config\RequiresMandatoryOptions::class, $factory);
        self::assertInstanceOf(\Interop\Config\ProvidesDefaultOptions::class, $factory);
    }

    /**
     * @test
     */
    public function it_creates_command_middleware()
    {
        $factory = new CommandMiddlewareFactory();
        $container = $this->prophesize(ContainerInterface::class);
        $messageFactory = $this->prophesize(MessageFactory::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'middleware' => [
                    'command' => [
                        'message_factory' => 'custom_message_factory'
                    ]
                ]
            ]
        ]);

        $container->has('custom_message_factory')->willReturn(true);
        $container->get('custom_message_factory')->willReturn($messageFactory);
        $container->has(CommandBus::class)->willReturn(true);
        $container->get(CommandBus::class)->willReturn($this->prophesize(CommandBus::class));

        self::assertInstanceOf(CommandMiddleware::class, $factory($container->reveal()));
    }

    /**
     * @test
     * @expectedException \Interop\Config\Exception\MandatoryOptionNotFoundException
     */
    public function it_throws_exception_if_option_is_missing()
    {
        $factory = new CommandMiddlewareFactory();
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'middleware' => [
                    'command' => [
                    ]
                ]
            ]
        ]);

        $factory($container->reveal());
    }
}
