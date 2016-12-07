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
use Prooph\Psr7Middleware\EventMiddleware;
use Prooph\Psr7Middleware\NoopMetadataGatherer;
use Prooph\ServiceBus\EventBus;

final class EventMiddlewareFactory extends AbstractMiddlewareFactory implements ProvidesDefaultOptions, RequiresMandatoryOptions
{
    use ConfigurationTrait;

    public function __construct(string $configId = 'event')
    {
        parent::__construct($configId);
    }

    public function __invoke(ContainerInterface $container): EventMiddleware
    {
        $options = $this->options($container->get('config'), $this->configId);

        if (isset($options['metadata_gatherer'])) {
            $gatherer = $container->get($options['metadata_gatherer']);
        } else {
            $gatherer = new NoopMetadataGatherer();
        }

        return new EventMiddleware(
            $container->get($options['event_bus']),
            $container->get($options['message_factory']),
            $gatherer
        );
    }

    public function defaultOptions(): array
    {
        return ['event_bus' => EventBus::class];
    }

    public function mandatoryOptions(): iterable
    {
        return ['message_factory'];
    }
}
