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

namespace Prooph\Psr7Middleware;

use Prooph\Common\Messaging\MessageFactory;
use Prooph\Psr7Middleware\Exception\RuntimeException;
use Prooph\ServiceBus\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Command messages describe actions your model can handle.
 *
 * The CommandBus is designed to dispatch a message to only one handler or message producer. It does not return a
 * result. Sending a command means fire and forget and enforces the Tell-Don't-Ask principle.
 */
final class CommandMiddleware implements Middleware
{
    /**
     * Identifier to execute specific command
     *
     * @var string
     */
    const NAME_ATTRIBUTE = 'prooph_command_name';

    /**
     * Dispatches command
     *
     * @var CommandBus
     */
    private $commandBus;

    /**
     * Creates message depending on command name
     *
     * @var MessageFactory
     */
    private $commandFactory;

    /**
     * Gatherer of metadata from the request object
     *
     * @var MetadataGatherer
     */
    private $metadataGatherer;

    public function __construct(
        CommandBus $commandBus,
        MessageFactory $commandFactory,
        MetadataGatherer $metadataGatherer
    ) {
        $this->commandBus = $commandBus;
        $this->commandFactory = $commandFactory;
        $this->metadataGatherer = $metadataGatherer;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $commandName = $request->getAttribute(self::NAME_ATTRIBUTE);

        if (null === $commandName) {
            return $next(
                $request,
                $response,
                new RuntimeException(
                    sprintf('Command name attribute ("%s") was not found in request.', self::NAME_ATTRIBUTE),
                    Middleware::STATUS_CODE_BAD_REQUEST
                )
            );
        }

        try {
            $command = $this->commandFactory->createMessageFromArray($commandName, [
                'payload' => $request->getParsedBody(),
                'metadata' => $this->metadataGatherer->getFromRequest($request),
            ]);

            $this->commandBus->dispatch($command);

            return $response->withStatus(Middleware::STATUS_CODE_ACCEPTED);
        } catch (\Exception $e) {
            return $next(
                $request,
                $response,
                new RuntimeException(
                    sprintf('An error occurred during dispatching of command "%s"', $commandName),
                    Middleware::STATUS_CODE_INTERNAL_SERVER_ERROR,
                    $e
                )
            );
        }
    }
}
