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

namespace smacp\MachineTranslator\Tests\XlfTranslator;

use PHPUnit\Framework\TestCase;
use smacp\MachineTranslator\MicrosoftTranslator\MicrosoftTranslator;
use smacp\MachineTranslator\XlfTranslator\XlfTranslator;

/**
 * Class XlfTranslatorTest
 *
 * vendor/bin/phpunit --filter MicrosoftTranslatorTest
 *
 * @package smacp\MachineTranslator\Tests\XlfTranslator
 */
class XlfTranslatorTest extends TestCase
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
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testTranslate
     */
    public function testTranslate(): void
    {
        $this->markTestIncomplete();

        $translator = new MicrosoftTranslator(
            getenv('MICROSOFT_SUBSCRIPTION_KEY'),
            getenv('MICROSOFT_SUBSCRIPTION_REGION')
        );

        $translator->setLocaleMap($this->localeMap);

        $xlfTranslator = new XlfTranslator($translator, dirname(__FILE__) . '/../xlf/');
        $xlfTranslator->setCommit(false);
        $xlfTranslator->translate();
    }
}
