<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

namespace Prooph\Psr7Middleware;

use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\Exception\RuntimeException;
use Prooph\Psr7Middleware\Response\ResponseStrategy;
use Prooph\ServiceBus\QueryBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Query messages describe available information that can be fetched from your (read) model.
 *
 * The QueryBus also dispatches a message to only one finder (special query message handler) but it returns a
 * `React\Promise\Promise`. The QueryBus hands over the query message to a finder but also a `React\Promise\Deferred`
 * which needs to be resolved by the finder. We use promises to allow finders to handle queries asynchronous for
 * example using curl_multi_exec.
 */
final class QueryMiddleware implements Middleware
{
    /**
     * Identifier to execute specific query
     *
     * @var string
     */
    const NAME_ATTRIBUTE = 'prooph_query_name';

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
     * @param QueryBus $queryBus Dispatches query
     * @param MessageFactory $queryFactory Creates message depending on query name
     * @param ResponseStrategy $responseStrategy
     */
    public function __construct(QueryBus $queryBus, MessageFactory $queryFactory, ResponseStrategy $responseStrategy)
    {
        $this->queryBus = $queryBus;
        $this->queryFactory = $queryFactory;
        $this->responseStrategy = $responseStrategy;
    }

    /**
     * @interitdoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $queryName = $request->getAttribute(self::NAME_ATTRIBUTE);

        if (null === $queryName) {
            return $next(
                $request,
                $response,
                new RuntimeException(
                    sprintf('Query name attribute ("%s") was not found in request.', self::NAME_ATTRIBUTE),
                    Middleware::STATUS_CODE_BAD_REQUEST
                )
            );
        }
        $payload = $request->getQueryParams();

        if ($request->getMethod() === 'POST') {
            $payload['data'] = $request->getParsedBody();
        }

        try {
            $query = $this->queryFactory->createMessageFromArray($queryName, ['payload' => $payload]);

            return $this->responseStrategy->fromPromise(
                $this->queryBus->dispatch($query)
            );
        } catch (\Exception $e) {
            return $next(
                $request,
                $response,
                new RuntimeException(
                    sprintf('An error occurred during dispatching of query "%s"', $queryName),
                    Middleware::STATUS_CODE_INTERNAL_SERVER_ERROR,
                    $e
                )
            );
        }
    }
}
