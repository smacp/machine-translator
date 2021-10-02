<?php

namespace smacp\MachineTranslator\Tests\Classes;

use PHPUnit\Framework\TestCase;
use smacp\MachineTranslator\Tests\testConfig;
use smacp\MachineTranslator\Classes\MicrosoftTranslator;

class MicrosoftTranslatorTest extends TestCase
{
    /** @var array */
    protected $localeMap = [
	'ar_SY' => 'ar',
        'ca_ES' => 'ca',
        'cs_CZ' => 'cs',
        'en_GB' => 'en',
        'en_US' => 'en',
        'es_ES' => 'es',
        'he_HE' => 'he',
        'zh_CN' => 'zh-CHS',
        'zh_TW' => 'zh-CHT',
    ];

    public function testSetLocaleMap()
    {
        $translator = new MicrosoftTranslator(testConfig::MICROSOFT_KEY);
        $translator->setLocaleMap($this->localeMap);

        $localeMap = $translator->getLocaleMap();

        $this->assertEquals($this->localeMap, $localeMap);
    }

    public function testTranslate()
    {
        $translator = new MicrosoftTranslator(testConfig::MICROSOFT_KEY);
        $translator->setLocaleMap($this->localeMap);

        $result = $translator->translate('Hello', 'en_GB', 'es_ES');

        $this->assertEquals('Hola', $result);
    }

    public function testTranslateRetainPlaceHolders()
    {
        $translator = new MicrosoftTranslator(testConfig::MICROSOFT_KEY);
        $translator->setLocaleMap($this->localeMap);

        $result = $translator->translate('Hello %name%', 'en_GB', 'es_ES');

        $this->assertEquals('Hola %name%', $result);
    }

    public function testDetectLanguage()
    {
        $translator = new MicrosoftTranslator(testConfig::MICROSOFT_KEY);
        $translator->setLocaleMap($this->localeMap);

        $result = $translator->detectLanguage('Hola');

        $this->assertEquals('es', $result);
    }

    public function testDetectLanguageAndReturnMyLanguageCode()
    {
        $translator = new MicrosoftTranslator(testConfig::MICROSOFT_KEY);
        $translator->setLocaleMap($this->localeMap);

        $result = $translator->detectLanguage('Hola', true);

        $this->assertEquals('es_ES', $result);
    }
}
