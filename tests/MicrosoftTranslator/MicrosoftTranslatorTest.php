<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Stuart MacPherson
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

declare(strict_types=1);

namespace smacp\MachineTranslator\Tests\MicrosoftTranslator;

use Generator;
use PHPUnit\Framework\TestCase;
use smacp\MachineTranslator\MicrosoftTranslator\MicrosoftTranslatorCategory;
use smacp\MachineTranslator\MicrosoftTranslator\MicrosoftTranslator;

/**
 * Class MicrosoftTranslatorTest
 *
 * vendor/bin/phpunit --filter MicrosoftTranslatorTest
 *
 * @package smacp\MachineTranslator\Tests\MicrosoftTranslator
 */
class MicrosoftTranslatorTest extends TestCase
{
    /** @var string[] */
    private $localeMap = [
	    'ar_SY' => 'ar',
        'ca_ES' => 'ca',
        'cs_CZ' => 'cs',
        'en_GB' => 'en',
        'en_US' => 'en',
        'es_ES' => 'es',
        'he_HE' => 'he',
        'zh_CN' => 'zh-Hans',
        'zh_TW' => 'zh-Hant',
    ];

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testSetLocaleMap
     */
    public function testSetLocaleMap()
    {
        $translator = $this->getMicrosoftTranslatorInstance();
        $translator->setLocaleMap($this->localeMap);

        $localeMap = $translator->getLocaleMap();

        $this->assertEquals($this->localeMap, $localeMap);
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testTranslate
     *
     * @dataProvider translateDataProvider
     *
     * @param string $from
     * @param string $to
     * @param string $word
     * @param array  $options
     * @param string $expected
     */
    public function testTranslate(string $from, string $to, string $word, array $options, string $expected)
    {
        $translator = $this->getMicrosoftTranslatorInstance();
        $translator->setLocaleMap($this->localeMap);

        $result = $translator->translate($word, $from, $to, $options);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return Generator
     */
    public function translateDataProvider(): Generator
    {
        yield 'en -> es' => ['en_GB', 'es_ES', 'Hello', [], 'Hola'];
        yield 'en -> es' => ['en_GB', 'es_ES', 'Hello', ['category' => MicrosoftTranslatorCategory::TECHNOLOGY], 'Hola'];
        yield 'en -> es HTML' => ['en_GB', 'es_ES', '<a href="#">Hello</a>', [],  '<a href="#">Hola</a>'];
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testTranslateRetainPlaceHolders
     */
    public function testTranslateRetainPlaceHolders()
    {
        $translator = $this->getMicrosoftTranslatorInstance();
        $translator->setLocaleMap($this->localeMap);

        $result = $translator->translate('Hello %name%', 'en_GB', 'es_ES');

        $this->assertEquals('Hola %name%', $result);
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testDetectLanguage
     */
    public function testDetectLanguage()
    {
        $translator = $this->getMicrosoftTranslatorInstance();
        $translator->setLocaleMap($this->localeMap);

        $result = $translator->detectLanguage('Hola');

        $this->assertEquals('es', $result);
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testDetectLanguageReturnsMappedLocaleCodes
     */
    public function testDetectLanguageReturnsMappedLocaleCodes()
    {
        $translator = $this->getMicrosoftTranslatorInstance();
        $translator->setLocaleMap($this->localeMap);

        $result = $translator->detectLanguage('Hola', true);

        $this->assertEquals('es_ES', $result);
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testGetLanguages
     */
    public function testGetLanguages()
    {
        $translator = $this->getMicrosoftTranslatorInstance();

        $result = $translator->getLanguages();

        $this->assertArrayHasKey('translation', $result);
        $this->assertNotEmpty($result['translation']);
    }

    /**
     * @return MicrosoftTranslator
     */
    private function getMicrosoftTranslatorInstance(): MicrosoftTranslator
    {
        return new MicrosoftTranslator(getenv('MICROSOFT_SUBSCRIPTION_KEY'), getenv('MICROSOFT_SUBSCRIPTION_REGION'));
    }
}
