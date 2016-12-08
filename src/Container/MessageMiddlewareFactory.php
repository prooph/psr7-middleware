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

namespace Prooph\Psr7Middleware\Container;

use Interop\Config\ConfigurationTrait;
use Interop\Config\ProvidesDefaultOptions;
use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use Prooph\Psr7Middleware\MessageMiddleware;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\QueryBus;

final class MessageMiddlewareFactory extends AbstractMiddlewareFactory implements ProvidesDefaultOptions, RequiresMandatoryOptions
{
    use ConfigurationTrait;

    public function __construct(string $configId = 'message')
    {
        parent::__construct($configId);
    }

    public function __invoke(ContainerInterface $container): MessageMiddleware
    {
        $options = $this->options($container->get('config'), $this->configId);

        return new MessageMiddleware(
            $container->get($options['command_bus']),
            $container->get($options['query_bus']),
            $container->get($options['event_bus']),
            $container->get($options['message_factory']),
            $container->get($options['response_strategy'])
        );
    }

    public function defaultOptions(): array
    {
        return [
            'command_bus' => CommandBus::class,
            'event_bus' => EventBus::class,
            'query_bus' => QueryBus::class,
        ];
    }

    public function mandatoryOptions(): iterable
    {
        return ['message_factory', 'response_strategy'];
    }
}
