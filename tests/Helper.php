<?php
/**
 * This file is part of the prooph/psr7-middleware.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\Psr7Middleware;

use PHPUnit\Framework\TestCase;
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
     */
    public static function callableWithExceptionResponse(): \Closure
    {
        return function (ServerRequestInterface $request, ResponseInterface $response, RuntimeException $ex) {
            return $response->withStatus($ex->getCode());
        };
    }

    /**
     * Ensures that $next is not called
     */
    public static function callableShouldNotBeCalledWithException(TestCase $phpunit): \Closure
    {
        return function (ServerRequestInterface $request, ResponseInterface $response, RuntimeException $ex) use (
            $phpunit
        ) {
            $phpunit::fail('$next should not be called' . PHP_EOL . $ex->getPrevious()->getMessage());
        };
    }
}
