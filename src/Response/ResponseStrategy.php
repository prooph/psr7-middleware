<?php
/**
 * This file is part of prooph/psr7-middleware.
 * (c) 2016-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Psr7Middleware\Response;

use Psr\Http\Message\ResponseInterface;
use React\Promise\PromiseInterface;

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
    public function fromPromise(PromiseInterface $promise): ResponseInterface;
}
