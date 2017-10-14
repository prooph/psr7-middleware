# PSR-7 middleware for prooph components
Consume prooph messages (commands, queries and events) with a PSR-7 middleware. Please refer to the
[service-bus component documentation](https://github.com/prooph/service-bus) to see how to configure the different bus
types.

[![Build Status](https://travis-ci.org/prooph/psr7-middleware.svg?branch=master)](https://travis-ci.org/prooph/psr7-middleware)
[![Coverage Status](https://coveralls.io/repos/github/prooph/psr7-middleware/badge.svg?branch=master)](https://coveralls.io/github/prooph/psr7-middleware?branch=master)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/prooph/improoph)

## Middleware
For every bus system a middleware exists and one Middleware to rule them all.

* `CommandMiddleware`: Dispatches the message data to the command bus system 
* `QueryMiddleware`: Dispatches the message data to the query bus system 
* `EventMiddleware`: Dispatches the message data to the event bus system 
* `MessageMiddleware`: Dispatches the message data to the appropriated bus system depending on message type

## Installation
You can install `prooph/psr7-middleware` via Composer by adding `"prooph/psr7-middleware": "^0.1"` 
as requirement to your composer.json. 

## Documentation

Documentation is [in the docs tree](docs/book/), and can be compiled using [bookdown](http://bookdown.io).

```console
$ php ./vendor/bin/bookdown docs/bookdown.json
$ php -S 0.0.0.0:8080 -t docs/html/
```

Then browse to [http://localhost:8080/](http://localhost:8080/)

## Support

- Ask questions on Stack Overflow tagged with [#prooph](https://stackoverflow.com/questions/tagged/prooph).
- File issues at [https://github.com/prooph/psr7-middleware/issues](https://github.com/prooph/psr7-middleware/issues).
- Say hello in the [prooph gitter](https://gitter.im/prooph/improoph) chat.

## Contribute

Please feel free to fork and extend existing or add new plugins and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and may adapt the documentation.

## License

Released under the [New BSD License](LICENSE).
