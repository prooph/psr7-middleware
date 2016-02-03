<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace ProophTest\Psr7Middleware;

use Prooph\Psr7Middleware\Exception\RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Test helper class
 *
 * Contains methods to simplify tests
 */
class Helper
{
    /**
     * Returns the response with the exception code as HTTP status code
     *
     * @return \Closure
     */
    public static function callableWithExceptionResponse()
    {
        return function (ServerRequestInterface $request, ResponseInterface $response, RuntimeException $ex) {
            return $response->withStatus($ex->getCode());
        };
    }

    /**
     * Ensures that $next is not called
     *
     * @param \PHPUnit_Framework_TestCase $phpunit
     * @return \Closure
     */
    public static function callableShouldNotBeCalledWithException(\PHPUnit_Framework_TestCase $phpunit)
    {
        return function (ServerRequestInterface $request, ResponseInterface $response, RuntimeException $ex) use (
            $phpunit
        ) {
            $phpunit::fail('$next should not be called' . PHP_EOL . $ex->getPrevious()->getMessage());
        };
    }
}
