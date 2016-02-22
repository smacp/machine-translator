<?php

namespace SMACP\MachineTranslator\Tests\Classes;

use SMACP\MachineTranslator\Tests\testConfig;
use SMACP\MachineTranslator\Classes\MicrosoftTranslator;
use SMACP\MachineTranslator\Classes\XlfTranslator;

class XlfTranslatorTest extends \PHPUnit_Framework_TestCase
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
        $translator = new MicrosoftTranslator(testConfig::MICROSOFT_KEY, testConfig::MICROSOFT_SECRET);
        $translator->setLocaleMap($this->localeMap);

        $xlfTranslator = new XlfTranslator();
        $xlfTranslator->setTranslator($translator)
                      ->setSourceLocale('en_GB')
                      ->setDir(dirname(__FILE__) . '/../xlf/')
                      ->setMemory(false)
                      ->setCommit(false)
                      ->setOutput(true);
        
        $parsed = $xlfTranslator->translate();
    }
}
