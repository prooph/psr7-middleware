<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace Prooph\Psr7Middleware\Response;

use Psr\Http\Message\ResponseInterface;
use React\Promise\Promise;

/**
 * Generate HTTP response depending on Promise result data
 *
 * This is an example how to generate a JsonResponse from a React\Promise\Promise
 *
 * <code>
 * final class JsonResponse implements ResponseStrategy
 * {
 *     public function fromPromise(\React\Promise\Promise $promise)
 *     {
 *         $data = null;
 *
 *         $promise->done(function($result) use (&$data) {
 *             $data = $result;
 *         });
 *
 *         return new \Zend\Diactoros\Response\JsonResponse($data);
 *     }
 * }
 * </code>
 */
interface ResponseStrategy
{
    /**
     * Generates a valid HTTP response with result data from Promise object
     *
     * @param Promise $promise
     * @return ResponseInterface
     */
    public function fromPromise(Promise $promise);
}
