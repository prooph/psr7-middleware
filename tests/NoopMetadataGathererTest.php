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

namespace ProophTest\Psr7Middleware;

use PHPUnit\Framework\TestCase;
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
    public function it_implements_metadata_gatherer_interface(): void
    {
        $gatherer = new NoopMetadataGatherer();

        self::assertInstanceOf(MetadataGatherer::class, $gatherer);
    }

    /**
     * @test
     */
    public function it_return_array(): void
    {
        $gatherer = new NoopMetadataGatherer();
        $request = $this->prophesize(ServerRequestInterface::class);

        $this->assertInternalType('array', $gatherer->getFromRequest($request->reveal()));
        $this->assertEmpty($gatherer->getFromRequest($request->reveal()));
    }
}
