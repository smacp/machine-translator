<?php

namespace smacp\MachineTranslator\Tests\Classes;

use PHPUnit\Framework\TestCase;
use smacp\MachineTranslator\Classes\Logger\NullLogger;
use smacp\MachineTranslator\Tests\testConfig;
use smacp\MachineTranslator\Classes\MicrosoftTranslator;
use smacp\MachineTranslator\Classes\XlfTranslator;

class XlfTranslatorTest extends TestCase
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

    public function testTranslate()
    {
        $this->markTestIncomplete();

        $translator = new MicrosoftTranslator(testConfig::MICROSOFT_KEY);
        $translator->setLocaleMap($this->localeMap);

        $xlfTranslator = new XlfTranslator($translator, dirname(__FILE__) . '/../xlf/');
        $xlfTranslator->setCommit(false);
        $xlfTranslator->translate();
    }
}
