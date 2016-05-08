<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace Prooph\Psr7Middleware\Container;

use Interop\Config\ConfigurationTrait;
use Interop\Config\ProvidesDefaultOptions;
use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use Prooph\Psr7Middleware\EventMiddleware;
use Prooph\Psr7Middleware\NoopMetadataGatherer;
use Prooph\ServiceBus\EventBus;

final class EventMiddlewareFactory extends AbstractMiddlewareFactory
    implements ProvidesDefaultOptions, RequiresMandatoryOptions
{
    use ConfigurationTrait;

    /**
     * @param string $configId
     */
    public function __construct($configId = 'event')
    {
        parent::__construct($configId);
    }

    /**
     * Create service.
     *
     * @param ContainerInterface $container
     * @return EventMiddleware
     */
    public function __invoke(ContainerInterface $container)
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

    /**
     * @interitdoc
     */
    public function defaultOptions()
    {
        return ['event_bus' => EventBus::class];
    }

    /**
     * @interitdoc
     */
    public function mandatoryOptions()
    {
        return ['message_factory'];
    }
}
