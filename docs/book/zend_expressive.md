# Zend Expressive integration
The [Zend Expressive](https://github.com/zendframework/zend-expressive) integration is very easy, because you can use 
the predefined factories and configuration examples of the specific prooph component.

> Take a look at the Zend Expressive [prooph components in action)(https://github.com/prooph/proophessor-do "proophessor-do example app") 
example app.

## Routes
Here is an example for the `AuraRouter` to call the `CommandMiddleware` for the `register-user` command.

```php
return [
    'routes' => [
        [
            'name' => 'command::register-user',
            'path' => '/api/commands/register-user',
            'middleware' => \Prooph\Psr7Middleware\CommandMiddleware::class,
            'allowed_methods' => ['POST'],
            'options' => [
                'values' => [
                    // \Prooph\Common\Messaging\FQCNMessageFactory is used here
                    \Prooph\Psr7Middleware\CommandMiddleware::NAME_ATTRIBUTE => \Prooph\ProophessorDo\Model\User\Command\RegisterUser::class,
                ]
            ]
        ],
    ],
];
```
