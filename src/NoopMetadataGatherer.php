<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace Prooph\Psr7Middleware;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class NoopMetadataGatherer
 *
 * A noop gatherer of metadata from the request object
 *
 * @package Prooph\Psr7Middleware
 */
final class NoopMetadataGatherer implements MetadataGatherer
{
    /**
     * @inheritdoc
     */
    public function getFromRequest(ServerRequestInterface $request) {
        return [];
    }
}
