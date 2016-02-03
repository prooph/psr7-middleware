<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace Prooph\Psr7Middleware\Exception;

use RuntimeException as PhpRuntimeException;

/**
 * Runtime exception
 *
 * Use this exception if the code has not the capacity to handle the request.
 */
class RuntimeException extends PhpRuntimeException implements ExceptionInterface
{
}
