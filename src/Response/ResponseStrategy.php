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
    public function fromPromise(Promise $promise): ResponseInterface;
}
