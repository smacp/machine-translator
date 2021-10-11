# MachineTranslator
MachineTranslator is a PHP component that uses the Microsoft Translator API service to translate strings from one language to another. 
It is also able to machine translate xliff (.xlf) files. It currently supports the Microsoft service but other api providers 
may also be implemented in future (e.g. Google Translate).

## Dependencies

- PHP v7.3+

## Installation

Add the following to composer.json to install via composer:
```composer
"require": {
    "smacp/machine-translator": "dev-master"
},
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/smacp/machine-translator.git"
    }
]
```
A client key and secret is required to use Microsoft's Translation service API. Free or paid accounts can be created at [Microsoft Azure](https://azure.microsoft.com).

## MicrosoftTranslator

The MicrosoftTranslator requires a Microsoft Cognitive Services subscription key to access Microsoft's service. Example use for translating a string from English to Spanish:

```php
use smacp\MachineTranslator\MicrosoftTranslator\MicrosoftTranslator;

$translator = new MicrosoftTranslator($myMsTranslatorSecretKey, $myMsTranslatorRegion);
$translated = $translator->translate('Hello %name%', 'en', 'es');
```

It is also possible to detect the language of a given string
```php
$detected = $translator->detectLanguage('Hola');
```

### Configuration

#### Locale mapping
Custom or non Microsoft locales can be mapped to Microsoft equivalents via `setLocaleMap` e.g.

```php
$translator = new MicrosoftTranslator($myMsTranslatorSecretKey, $myMsTranslatorRegion);
$translator->setLocaleMap([
    'en_GB' => 'en',
    'myChineseSimplifiedCode_CN' => 'zh-Hans',
]);

$translated = $translator->translate('Hello', 'en_GB', 'myChineseSimplifiedCode_CN');

// 您好
```

#### Preserving string injection placeholders in source words and phrases

By default the MicrosoftTranslator will preserve string injection placeholders in words and phrases for words encapsulated 
by `%` e.g.

```
Hello %name%
```

Additional regex patterns to preserve string injection placeholders can be added by calling `addPlaceholderPattern` e.g.

```php
$translator
    ->addPlaceholderPattern('/{(.*)?}/')
    ->addPlaceholderPattern('/$(.*)?\s/');
    
$translated1 = $translator->translate('Hello {name}', 'en', 'es');
$translated2 = $translator->translate('Hello $name, how are you?', 'en', 'es');

// Hola {name}
// Hola $name, ¿cómo estás?
```

XlfTranslator
----
The XlfTranslator machine translates xliff files found in a given directory. It machine translates files based on a naming convention of catalogue.locale.xlf (e.g. 'messages.ca_ES.xlf'). Example use:

```php
use smacp\MachineTranslator\MicrosoftTranslator\MicrosoftTranslator;
use smacp\MachineTranslator\XlfTranslator\XlfTranslator;

$translator = new MicrosoftTranslator($myMsTranslatorSecretKey, $myMsTranslatorRegion);
// map my xlf file language codes to Microsoft's :)
$translator->setLocaleMap([
    'ar_SY' => 'ar',
    'ca_ES' => 'ca',
    'cs_CZ' => 'cs',
    'en_GB' => 'en',
    'en_US' => 'en',
    'es_ES' => 'es',
    'no_NO' => 'no',
    'he_HE' => 'he',
    'zh_CN' => 'zh-Hans',
    'zh_TW' => 'zh-Hant',
]);

$xlfTranslator = new XlfTranslator($translator, '/home/me/xlf/');
$xlfTranslator->translate();
```
## Troubleshooting

### Microsoft Translator API authentication

The authentication with the Microsoft Translator API requires

- A valid Translator subscription secret key 
- A valid region that the Translator subscription is registered in e.g. global, northeurope etc

These values can be obtained from the Microsoft Azure Portal (https://portal.azure.com). An invalid combination will 
result in a HTTP 401 Unauthorized response e.g.

```json
{
  "error": {
    "code":401000,
    "message":"The request is not authorized because credentials are missing or invalid."
  }
}
```

## Known issues

Microsoft's free or paid plans for their Translation service are currently subject to word quotas and rate limits. When these constraints are applied to an individual account then the Microsoft service may not honour translation requests.

## License

MIT

## Testing

Copy .env.test.example to .env.test and set environment variables. Note this package runs integration level tests with 
the Microsoft Translator API. Unit tests can be excuted by running:

```bash
vendor/bin/phpunit
```

Static code analysis can be executed via PHPStan:

```bash
vendor/bin/phpstan analyze src
```
## Todo

- Develop XlfTranslator unit tests