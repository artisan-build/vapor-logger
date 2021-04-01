# Vapor Logger
Connect your Vapor-deployed Laravel site to [VaporLog](https://vaporlog.co) for simple, affordable application logging. Or, if you're feeling ambitious, spin up your own app to capture your logs yourself.
### How To Use

This package works out of the box with an account at [VaporLog](https://vaporlog.co). When enabled with a valid API key, any logged events debug or higher will be posted to the VaporLog API, with some limitations:

* We only post one of the same event per second (can be configured but must be at least 1 second). So if `Log::debug('This can only happen once in a second')` is called several times in a row very quickly, it will only be posted to the api once per second. This is to keep your cost (and yours) as low as possible.

* If you are using [VaporLog](https://vaporlog.co) to capture your logs, you have to have an account in good standing. Your API key must be valid, and you must have an active subscription.

* Without regard to either of these other conditions, your logs will continue to be written to AWS CloudWatch exactly as they are right now. Though we recommend bumping your `LOG_LEVEL` down to debug because with the default Vapor settings, it's possible for un-logged problems to cause your users to see error pages.

### What This Is and What It Is Not

[VaporLog](https://vaporlog.co) and this package are not designed to be a monitoring service. We just needed a way of doing simple application logging in Vapor and found the CloudWatch logs to be too cluttered to be truly useful. We created it for ourselves in a day and thought "maybe someone else out there will like this enough to throw a few bucks at us". Literally a business built in a day, born of our own frustration with one little aspect of an otherwise wonderful ecosystem. So you are welcome to suggest features, but we're not anxious to make this anything bigger than a simple, super affordable logging solution for Vapor-deployed Laravel apps.

### Installation

```shell
composer require artisan-build/vapor-logger
```

Then go to [VaporLog](https://vaporlog.co), create an account, and follow the instructions there to activate your project.

### Contributing

See a bug? See room for improvement? We welcome your pull request. If you make a contribution that gets merged, we'll shoot you a credit for free logging at [VaporLog](https://vaporlog.co).

This package is open source. It provides everything you'd need to send your own logs from a Vapor-deployed Laravel app to an external API. You are welcome to use it with [VaporLog](https://vaporlog.co) or any other API that exists to work with it. You can even use it to build your own service to compete with [VaporLog](https://vaporlog.co). Doing so might be a great way to learn how to use Laravel Cashier and Laravel Sanctum. If you do build something cool, free or commercial, send us a link, and we'll add it to the README.

### Linting and Testing

```shell script
composer test:unit # Runs PHPUnit
composer lint # Runs php-cs-fixer to fix your coding style
composer test # Runs lint and then test:unit 
```

