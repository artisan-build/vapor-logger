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

### Configuration

We designed this package to work out of the box when included in any Vapor-deployed Laravel site that has a [vaporlog.co](https://vaporlog.co) API key in the deployment's environment. But if you want to get fancy, there are some things you can tweak:

```php
return [
    'api_key' => env('VAPOR_LOGGER_KEY', null),
    'api_url' => env('VAPOR_LOGGER_API', 'https://api.vaporlog.co/api/log'), // POST Url to capture logs
    'is_vapor' => env('VAPOR_LOGGER_IS_VAPOR', isset($_SERVER['VAPOR_SSM_PATH'])),
    'log_level' => env('VAPOR_LOGGER_LEVEL', env('LOG_LEVEL', 'debug')),
    'add_channels' => strlen(env('VAPOR_LOGGER_ADD_CHANNELS', '')) === 0 ? []
        : explode(',', env('VAPOR_LOGGER_ADD_CHANNELS')),
    'throttle' => [
        'identical' => env('VAPOR_LOGGER_IDENTICAL_THROTTLE', 1),
        'failure' => env('VAPOR_LOGGER_FAILURE_THROTTLE', 10),
    ],
    'heartbeat' => env('VAPOR_LOGGER_HEARTBEAT', false),
];
```

**VAPOR_LOGGER_KEY** - This is the API key that you get when you log in to vaporlog.co.

**VAPOR_LOGGER_API** - If you want to use this package to send logs to your own logging API, you can do that by setting the URL here. 

**VAPOR_LOGGER_IS_VAPOR** - By default this package only works for Vapor-deployed sites, but if you really want to use it outside of Vapor I suppose you can by setting this to true.

**VAPOR_LOGGER_LEVEL** - This is the minimum log level that will be sent to the API. We've found setting this to DEBUG gives us the most useful logs, but everyone's taste is different.

**VAPOR_LOG_ADD_CHANNELS** - A comma separated list of additional channels you want to use. By default, your logs will be sent to vaporlog.co as well as the Vapor-provided default (AWS CloudWatch). This variable only **adds** new log channels, it does not replace them.

**VAPOR_LOGGER_IDENTICAL_THROTTLE** - We only send the same message to the API once per second. You can change this, but you can only make it bigger. If you change it to 10, we'll only send identical messages once every 10 seconds.

**VAPOR_LOGGER_FAILURE_THROTTLE** - If the API returns an error status, we stop trying to log for 10 seconds to let things recover. There's no point in either of us spending money for something to not work. As soon as a success status is returned, logging returns to its normal pace.

**VAPOR_LOGGER_HEARTBEAT** - If you want some assurance that the package is working, you can set this to true and once an hour your site will send an info log to vaporlog saying that it's active.

### Contributing

See a bug? See room for improvement? We welcome your pull request. If you make a contribution that gets merged, we'll shoot you a credit for free logging at [VaporLog](https://vaporlog.co).

This package is open source. It provides everything you'd need to send your own logs from a Vapor-deployed Laravel app to an external API. You are welcome to use it with [VaporLog](https://vaporlog.co) or any other API that exists to work with it. You can even use it to build your own service to compete with [VaporLog](https://vaporlog.co). Doing so might be a great way to learn how to use Laravel Cashier and Laravel Sanctum. If you do build something cool, free or commercial, send us a link, and we'll add it to the README.

### Linting and Testing

```shell script
composer test:unit # Runs PHPUnit
composer lint # Runs php-cs-fixer to fix your coding style
composer test # Runs lint and then test:unit 
```

