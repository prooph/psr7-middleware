<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace Prooph\Psr7Middleware\Exception;

use InvalidArgumentException as PhpInvalidArgumentException;

/**
 * Runtime exception
 *
 * Use this exception if a provided argument is invalid.
 */
class InvalidArgumentException extends PhpInvalidArgumentException implements ExceptionInterface
{
}
