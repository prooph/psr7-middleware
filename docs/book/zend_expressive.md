# Zend Expressive integration
The [Zend Expressive](https://github.com/zendframework/zend-expressive) integration is very easy, because you can use 
the predefined factories and configuration examples of the specific prooph component.

> Take a look at the Zend Expressive [prooph components in action](https://github.com/prooph/proophessor-do "proophessor-do example app") 
example app.

## Routes
Here is an example for the `AuraRouter` to call the `CommandMiddleware` for the `register-user` command.

```php
// routes.php

/** @var \Zend\Expressive\Application $app */
$app->post('/api/commands/register-user', [
    \Prooph\Psr7Middleware\CommandMiddleware::class,
], 'command::register-user')
    ->setOptions([
        'values' => [
            \Prooph\Psr7Middleware\CommandMiddleware::NAME_ATTRIBUTE => \Prooph\ProophessorDo\Model\User\Command\RegisterUser::class,
        ],
    ]);
```

## Metadata Gatherer

QueryMiddleware, CommandMiddleware and EventMiddleware have a MetadataGatherer injected that is capable of retrieving attributes derived from the ServerRequestInterface and pass those with messages as metadata.

By default a Noop (returns an empty array) instance is used, but it is very easy to change that.

First write an implementation of MetadataGatherer;

```php
namespace My\Psr7Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Prooph\Psr7Middleware\MetadataGatherer;

final class MyMetadataGatherer implements MetadataGatherer
{
    /**
     * @inheritdoc
     */
    public function getFromRequest(ServerRequestInterface $request) {
    	return [
    		'identity' => $request->getAttribute('identity'),
    		'request_uuid' => $request->getAttribute('request_uuid')
    	];
    }
}

```

Then define it in container and prooph configuration;

```php
return [
    'dependencies' => [
    	'factories' => [
    		\My\Psr7Middleware\MyMetadataGatherer::class => \Zend\ServiceManager\Factory\InvokableFactory::class
        ],
    ],
    'prooph' => [
        'middleware' => [
            'query' => [
                'metadata_gatherer' => \My\Psr7Middleware\MyMetadataGatherer::class
            ],
        ],
    ],
    ...
```

