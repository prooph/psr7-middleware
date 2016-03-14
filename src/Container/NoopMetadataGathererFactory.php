<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace Prooph\Psr7Middleware\Container;

use Interop\Container\ContainerInterface;
use Prooph\Psr7Middleware\NoopMetadataGatherer;

final class NoopMetadataGathererFactory
{
    /**
     * Create service.
     *
     * @param ContainerInterface $container
     * @return NoopMetadataGatherer
     */
    public function __invoke(ContainerInterface $container)
    {
        return new NoopMetadataGatherer();
    }

}
