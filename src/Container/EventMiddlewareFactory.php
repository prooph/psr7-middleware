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
use Interop\Config\RequiresContainerId;
use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use Prooph\Psr7Middleware\EventMiddleware;
use Prooph\Psr7Middleware\NoopMetadataGatherer;
use Prooph\ServiceBus\EventBus;

final class EventMiddlewareFactory implements RequiresContainerId, ProvidesDefaultOptions, RequiresMandatoryOptions
{
    use ConfigurationTrait;

    /**
     * Create service.
     *
     * @param ContainerInterface $container
     * @return EventMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        $options = $this->options($container->get('config'));

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
    public function vendorName()
    {
        return 'prooph';
    }

    /**
     * @interitdoc
     */
    public function packageName()
    {
        return 'middleware';
    }

    /**
     * @interitdoc
     */
    public function containerId()
    {
        return 'event';
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
