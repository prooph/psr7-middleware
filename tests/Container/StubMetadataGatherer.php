<?php

/**
 * This file is part of prooph/psr7-middleware.
 * (c) 2016-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\Psr7Middleware\Container;

use Prooph\Psr7Middleware\MetadataGatherer;
use Psr\Http\Message\ServerRequestInterface;

final class StubMetadataGatherer implements MetadataGatherer
{
    public function getFromRequest(ServerRequestInterface $request): array
    {
        return [];
    }
}
