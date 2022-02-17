# MachineTranslator
MachineTranslator is a PHP component that uses the Microsoft Translator service to translate strings from one language to another. It is also able to machine translate xliff (.xlf) files. It currently supports the Microsoft service but other api providers may be also be implemented in future (e.g. Google Translate).

PHP
----
v5.4.0+

Installation
----
Add the following to composer.json to install via composer:
```composer
"require": {
    "smacp/machine-translator": "^1"
},
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/smacp/machine-translator.git"
    }
]
```
A client key and secret is required to use Microsoft's Translation service api. Free or paid accounts can be created at [Microsoft Azure](https://azure.microsoft.com).

MicrosoftTranslator
----
The MicrosoftTranslator requires a client key and client secret to access Microsoft's service. Example use for translating a string from English to Spanish:

```php
use SMACP\MachineTranslator\Classes\MicrosoftTranslator;

$translator = new MicrosoftTranslator($myMsTranslationClientId, $myMsTranslationClientSecret);
$translated = $translator->translate('Hello %name%', 'en', 'es');
```

It is also possible to detect the language of a given string
```php
$detected = $translator->detectLanguage('Hola');
```

XlfTranslator
----
The XlfTranslator machine translates xliff files found in a given directory. It machine translates files based on a naming convention of catalogue.locale.xlf (e.g. 'messages.ca_ES.xlf'). Example use:

```php
use SMACP\MachineTranslator\Classes\MicrosoftTranslator;
use SMACP\MachineTranslator\Classes\XlfTranslator;

$translator = new MicrosoftTranslator($myMsTranslationClientId, $myMsTranslationClientSecret);
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
    'zh_CN' => 'zh-CHS',
    'zh_TW' => 'zh-CHT',
]);

$xlfTranslator = new XlfTranslator();
$xlfTranslator->setTranslator($translator)
              ->setSourceLocale('en_GB')
              ->setDir('/home/me/xlf/')
              ->setOutput(true)
              ->translate();
```
Known issues
----
Microsoft's free or paid plans for their Translation service are currently subject to word quotas and rate limits. When these constraints are applied to an individual account then the Microsoft service may not honour translation requests.

License
----

MIT

Todo
----
Finish tests