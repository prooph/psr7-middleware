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

namespace Prooph\Psr7Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface Middleware
 *
 * A middleware is a callable which handles the requested message type.
 */
interface Middleware
{
    /**#@+
     * @const HTTP status code constant names
     */
    const STATUS_CODE_ACCEPTED = 202;
    const STATUS_CODE_BAD_REQUEST = 400;
    const STATUS_CODE_INTERNAL_SERVER_ERROR = 500;
    /**#@-*/

    /**
     * Handle requested message
     *
     * @return callable|ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next);
}
