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
     */
    public function getFromRequest(ServerRequestInterface $request): array;
}
