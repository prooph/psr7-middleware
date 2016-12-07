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

namespace Prooph\Psr7Middleware\Container;

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfigId;
use Interop\Container\ContainerInterface;
use Prooph\Psr7Middleware\Exception\InvalidArgumentException;

/**
 * Base class for factories
 */
abstract class AbstractMiddlewareFactory implements RequiresConfigId
{
    use ConfigurationTrait;

    /**
     * @var string
     */
    protected $configId;

    /**
     * Creates a new instance from a specified config, specifically meant to be used as static factory.
     *
     * In case you want to use another config key than provided by the factories, you can add the following factory to
     * your config:
     *
     * <code>
     * <?php
     * return [
     *     'prooph.middleware.other' => [CommandMiddlewareFactory::class, 'other'],
     * ];
     * </code>
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function __callStatic($name, array $arguments)
    {
        if (! isset($arguments[0]) || ! $arguments[0] instanceof ContainerInterface) {
            throw new InvalidArgumentException(
                sprintf('The first argument must be of type %s', ContainerInterface::class)
            );
        }

        return (new static($name))->__invoke($arguments[0]);
    }

    /**
     * @param string $configId
     */
    public function __construct($configId)
    {
        // ensure BC
        $this->configId = method_exists($this, 'containerId') ? $this->containerId() : $configId;
    }

    /**
     * @interitdoc
     */
    public function dimensions()
    {
        return ['prooph', 'middleware'];
    }
}
