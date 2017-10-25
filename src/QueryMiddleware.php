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

namespace Prooph\Psr7Middleware;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Webimpress\HttpMiddlewareCompatibility\HandlerInterface;
use Webimpress\HttpMiddlewareCompatibility\MiddlewareInterface;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\Exception\RuntimeException;
use Prooph\Psr7Middleware\Response\ResponseStrategy;
use Prooph\ServiceBus\QueryBus;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Query messages describe available information that can be fetched from your (read) model.
 *
 * The QueryBus also dispatches a message to only one finder (special query message handler) but it returns a
 * `React\Promise\Promise`. The QueryBus hands over the query message to a finder but also a `React\Promise\Deferred`
 * which needs to be resolved by the finder. We use promises to allow finders to handle queries asynchronous for
 * example using curl_multi_exec.
 */
final class QueryMiddleware implements MiddlewareInterface
{
    /**
     * Identifier to execute specific query
     *
     * @var string
     */
    public const NAME_ATTRIBUTE = 'prooph_query_name';

    /**
     * Dispatches query
     *
     * @var QueryBus
     */
    private $queryBus;

    /**
     * Creates message depending on query name
     *
     * @var MessageFactory
     */
    private $queryFactory;

    /**
     * Generate HTTP response with result from Promise
     *
     * @var ResponseStrategy
     */
    private $responseStrategy;

    /**
     * Gatherer of metadata from the request object
     *
     * @var MetadataGatherer
     */
    private $metadataGatherer;

    public function __construct(
        QueryBus $queryBus,
        MessageFactory $queryFactory,
        ResponseStrategy $responseStrategy,
        MetadataGatherer $metadataGatherer
    ) {
        $this->queryBus = $queryBus;
        $this->queryFactory = $queryFactory;
        $this->responseStrategy = $responseStrategy;
        $this->metadataGatherer = $metadataGatherer;
    }

    public function process(ServerRequestInterface $request, HandlerInterface $handler)
    {
        $queryName = $request->getAttribute(self::NAME_ATTRIBUTE);

        if (null === $queryName) {
            throw new RuntimeException(
                sprintf('Query name attribute ("%s") was not found in request.', self::NAME_ATTRIBUTE),
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }
        $payload = $request->getQueryParams();

        if ($request->getMethod() === RequestMethodInterface::METHOD_POST) {
            $payload['data'] = $request->getParsedBody();
        }

        try {
            $query = $this->queryFactory->createMessageFromArray($queryName, [
                'payload' => $payload,
                'metadata' => $this->metadataGatherer->getFromRequest($request),
            ]);

            return $this->responseStrategy->fromPromise(
                $this->queryBus->dispatch($query)
            );
        } catch (\Throwable $e) {
            throw new RuntimeException(
                sprintf('An error occurred during dispatching of query "%s"', $queryName),
                StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
                $e
            );
        }
    }
}
