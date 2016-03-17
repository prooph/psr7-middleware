<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace ProophTest\Psr7Middleware;

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\Psr7Middleware\MetadataGatherer;
use Prooph\Psr7Middleware\NoopMetadataGatherer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Test integrity of \Prooph\Psr7Middleware\NoopMetadataGathererTest
 */
class NoopMetadataGathererTest extends TestCase
{

    /**
     * @test
     */
    public function it_implements_metadata_gatherer_interface()
    {
        $gatherer = new NoopMetadataGatherer();

        self::assertInstanceOf(MetadataGatherer::class, $gatherer);
    }

    /**
     * @test
     */
    public function it_return_array()
    {
        $gatherer = new NoopMetadataGatherer();
        $request  = $this->prophesize(ServerRequestInterface::class);

        $this->assertInternalType('array', $gatherer->getFromRequest($request->reveal()));
        $this->assertEmpty($gatherer->getFromRequest($request->reveal()));
    }
}
