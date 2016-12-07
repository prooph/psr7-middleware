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

namespace Prooph\Psr7Middleware;

/**
 * This file contains default configuration for prooph/psr7-middleware
 * It is meant to be used together with at least one of the container-aware factories
 * shipped with this package. Please refer to src/Container for the factories and
 * register them in your Interop\Container\ContainerInterface of choice
 * Then make this config available as service id `config` within your container
 * (possibly merged into your application configuration)
 */
return [
    //vendor key to avoid merge conflicts with other packages when merged into application config
    'prooph' => [
        //component key to avoid merge conflicts with other prooph components when merged into application config
        'middleware' => [
            //This section will be used by \Prooph\Psr7Middleware\Container\QueryMiddlewareFactory
            'query' => [
                // container/service id / instance to return the data
                // see \Prooph\Psr7Middleware\Response\ResponseStrategy for an example
                'response_strategy' => 'custom_response_strategy',
                // container/service id / instance, see \Prooph\Common\Messaging\MessageFactory for more details
                'message_factory' => 'custom_message_factory',
                // container/service id for the QueryBus instance, default QueryBus::class
                'query_bus' => 'custom_query_bus',
                // container/service id for the MetadataGatherer instance, default NoopMetadataGatherer::class
                // 'metadata_gatherer' => \Prooph\Psr7Middleware\MetadataGatherer::class
            ],
            //This section will be used by \Prooph\Psr7Middleware\Container\CommandMiddlewareFactory
            'command' => [
                // container/service id, see \Prooph\Common\Messaging\MessageFactory for more details
                'message_factory' => \Prooph\Common\Messaging\FQCNMessageFactory::class,
                // container/service id for the CommandBus instance, default CommandBus::class
                'command_bus' => 'custom_command_bus'
            ],
            //This section will be used by \Prooph\Psr7Middleware\Container\EventMiddlewareFactory
            'event' => [
                // container/service id, see \Prooph\Common\Messaging\MessageFactory for more details
                'message_factory' => \Prooph\Common\Messaging\FQCNMessageFactory::class,
                // container/service id for the EventBus instance, default EventBus::class
                'event_bus' => 'custom_event_bus'
            ],
            //This section will be used by \Prooph\Psr7Middleware\Container\MessageMiddlewareFactory
            'message' => [
                // container/service id to return the data
                // see \Prooph\Psr7Middleware\Response\ResponseStrategy for an example, only used for query bus
                'response_strategy' => 'custom_response_strategy',
                // container/service id, see \Prooph\Common\Messaging\MessageFactory for more details
                // must have support for all bus types e.g. command, event and query
                'message_factory' => 'custom_message_factory',
                // container/service id for the MessageBus instance, default MessageBus::class
                'message_bus' => 'custom_message_bus'
            ],
        ],
    ],
];
