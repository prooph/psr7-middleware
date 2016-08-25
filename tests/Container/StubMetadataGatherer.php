<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace ProophTest\Psr7Middleware\Container;

use Prooph\Psr7Middleware\MetadataGatherer;
use Psr\Http\Message\ServerRequestInterface;

class StubMetadataGatherer implements MetadataGatherer
{
    public function getFromRequest(ServerRequestInterface $request)
    {
        return [];
    }
}
