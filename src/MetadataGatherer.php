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
 * Interface MetadataGatherer
 *
 * Gatherer of metadata from the request object
 */
interface MetadataGatherer
{
    /**
     * Gets metadata from the request
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    public function getFromRequest(ServerRequestInterface $request);
}
