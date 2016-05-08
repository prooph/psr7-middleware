<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/prooph/psr7-middleware for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/prooph/psr7-middleware/blob/master/LICENSE New BSD License
 */

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
        if (!isset($arguments[0]) || !$arguments[0] instanceof ContainerInterface) {
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
