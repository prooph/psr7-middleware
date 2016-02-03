<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

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
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next Is typically an instance of another middleware or a final handler
     * @return callable|ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next);
}
