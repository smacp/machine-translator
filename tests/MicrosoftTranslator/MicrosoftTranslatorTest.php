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
use GuzzleHttp\Client;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use smacp\MachineTranslator\Exception\FileNotFoundException;
use smacp\MachineTranslator\MicrosoftTranslator\MicrosoftTranslator;
use smacp\MachineTranslator\MicrosoftTranslator\MicrosoftTranslatorCategory;

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
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testSettersAndGetters
     */
    public function testSettersAndGetters(): void
    {
        $translator = $this->getMicrosoftTranslatorInstance();

        $client = new Client();
        $localeMap = [
            'en-GB' => 'en',
            'zh-TW' => 'zh-Hant',
        ];
        $placeholderPatterns = [
            '/foo/',
            '/bar/'
        ];

        $translator->setClient($client)
            ->setLocaleMap($localeMap)
            ->setPlaceholderPatterns($placeholderPatterns);

        $this->assertSame('Microsoft', $translator->getProvider());
        $this->assertNull($translator->getResponse());
        $this->assertSame($localeMap, $translator->getLocaleMap());
        $this->assertSame($client, $translator->getClient());
        $this->assertSame($placeholderPatterns, $translator->getPlaceholderPatterns());
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testGetResponse
     */
    public function testGetResponse(): void
    {
        /** @var StreamInterface|MockObject $body */
        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')
            ->willReturn(json_encode([['translations' => [['text' => 'Foo']]]]));

        /** @var Client|MockObject $client */
        $client = $this->createMock(Client::class);

        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        $response->method('getBody')
            ->willReturn($body);

        $client->method('post')
            ->willReturn($response);

        $translator = $this->getMicrosoftTranslatorInstance();
        $translator->setClient($client);

        $translator->translate('Foo', 'en', 'es');

        $this->assertSame($response, $translator->getResponse());
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testTranslate
     *
     * @dataProvider translateDataProvider
     *
     * @param string $word
     * @param string $from
     * @param string $to
     * @param array  $options
     * @param string $expected
     */
    public function testTranslate(string $word, string $from, string $to, array $options, string $expected): void
    {
        $translator = $this->getMicrosoftTranslatorInstance();

        $result = $translator->translate($word, $from, $to, $options);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return Generator
     */
    public function translateDataProvider(): Generator
    {
        yield 'en -> es' => ['Hello', 'en', 'es', [], 'Hola'];
        yield 'en -> es' => ['Hello', 'en', 'es', ['category' => MicrosoftTranslatorCategory::TECHNOLOGY], 'Hola'];
        yield 'en -> es HTML' => ['<a href="#">Hello</a>', 'en', 'es', [],  '<a href="#">Hola</a>'];
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testTranslateWithInvalidArguments
     *
     * @dataProvider translateWithInvalidArgumentsDataProvider
     *
     * @param string $word
     * @param string $from
     * @param string $to
     * @param array  $options
     * @param string $expected
     */
    public function testTranslateWithInvalidArguments(
        string $word,
        string $from,
        string $to,
        array $options,
        string $expected
    ): void {
        $translator = $this->getMicrosoftTranslatorInstance();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);

        $translator->translate($word, $from, $to, $options);
    }

    /**
     * @return Generator
     */
    public function translateWithInvalidArgumentsDataProvider(): Generator
    {
        yield 'invalid word' => ['', 'foo', 'es', [], 'No word was given for translation.'];
        yield 'invalid word' => [' ', 'foo', 'es', [], 'No word was given for translation.'];
        yield 'invalid from' => ['Word', 'foo', 'es', [], 'No Microsoft locale code could be resolved for the from locale.'];
        yield 'invalid to' => ['Word', 'en', 'foo', [], 'No Microsoft locale code could be resolved for the to locale.'];
        yield 'from === to' => ['Word', 'es', 'es', [], 'Locales for from and to are the same.'];
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testTranslateWithPlaceHolders
     *
     * @dataProvider translateWithPlaceholdersDataProvider
     *
     * @param string $word
     * @param string $from
     * @param string $to
     * @param array  $options
     * @param string $expected
     */
    public function testTranslateWithPlaceHolders(
        string $word,
        string $from,
        string $to,
        array $options,
        string $expected
    ): void {
        $translator = $this->getMicrosoftTranslatorInstance();

        $result = $translator->translate($word, $from, $to, $options);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return Generator
     */
    public function translateWithPlaceholdersDataProvider(): Generator
    {
        yield 'en -> es' => ['Hello %name%', 'en', 'es', [], 'Hola %name%'];
        yield 'en -> es HTML' => ['<a href="%url%">Hello %name%</a>', 'en', 'es', [], '<a href="%url%">Hola %name%</a>'];
        yield 'en -> zh-Hans' => ['Hello %name%', 'en', 'zh-Hans', [], '你好 %name%'];
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testTranslateWithLocaleMap
     *
     * @dataProvider translateWithLocaleMapDataProvider
     *
     * @param string $word
     * @param string $from
     * @param string $to
     * @param array  $options
     * @param string $expected
     */
    public function testTranslateWithLocaleMap(
        string $word,
        string $from,
        string $to,
        array $options,
        string $expected
    ): void {
        $translator = $this->getMicrosoftTranslatorInstance();
        $translator->setLocaleMap($this->localeMap);

        $result = $translator->translate($word, $from, $to, $options);

        $this->assertSame($expected, $result);
    }

    /**
     * @return Generator
     */
    public function translateWithLocaleMapDataProvider(): Generator
    {
        yield 'en_GB > es_ES' => ['Hello', 'en_GB', 'es_ES', [], 'Hola'];
        yield 'en_GB > ca_ES' => ['Hello', 'en_GB', 'ca_ES', [], 'Hola'];
        yield 'en_GB > zh_TW' => ['Hello', 'en_GB', 'zh_TW', [], '你好'];
        yield 'ca_ES > en_GB' => ['Hello', 'ca_ES', 'en_GB', [], 'Hello'];
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testTranslateNormalisesChineseLanguageCodes
     *
     * @dataProvider translateNormalisesChineseLanguageCodesDataProvider
     */
    public function testTranslateNormalisesChineseLanguageCodes(
        string $word,
        string $from,
        string $to,
        array $options,
        string $expected
    ): void {
        $translator = $this->getMicrosoftTranslatorInstance();

        $result = $translator->translate($word, $from, $to, $options);

        $this->assertSame($expected, $result);
    }

    /**
     * @return Generator
     */
    public function translateNormalisesChineseLanguageCodesDataProvider(): Generator
    {
        yield 'zh-CN' => ['Hello', 'es', 'zh-CN', [], '你好'];
        yield 'zh-TW' => ['Hello', 'es', 'zh-TW', [], '你好'];
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testAddPlaceholderPattern
     *
     * @dataProvider addPlaceholderPatternDataProvider
     *
     * @param string $pattern
     * @param string $word
     * @param string $from
     * @param string $to
     * @param array  $options
     * @param string $expected
     */
    public function testAddPlaceholderPattern(
        string $pattern,
        string $word,
        string $from,
        string $to,
        array $options,
        string $expected
    ): void {
        $translator = $this->getMicrosoftTranslatorInstance();
        $translator->addPlaceholderPattern($pattern);

        $result = $translator->translate($word, $from, $to, $options);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return Generator
     */
    public function addPlaceholderPatternDataProvider(): Generator
    {
        yield 'en -> es #1' => ['/{(.*)?}/', 'Hello {name}', 'en', 'es', [], 'Hola {name}'];
        yield 'en -> es #2' => ['/{(.*)?}/', 'Hello {Name}', 'en', 'es', [], 'Hola {Name}'];
        yield 'en -> es #3' => ['/{(.*)?}/', 'Hello {$name}', 'en', 'es', [], 'Hola {$name}'];
        yield 'en -> es #3' => ['/$(.*)?\s/', 'Hello $name, how are you?', 'en', 'es', [], 'Hola $name, ¿cómo estás?'];
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testDetectLanguage
     */
    public function testDetectLanguage(): void
    {
        $translator = $this->getMicrosoftTranslatorInstance();

        $result = $translator->detectLanguage('Hola');

        $this->assertEquals('es', $result);
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testDetectLanguageReturnsMappedLocaleCodes
     */
    public function testDetectLanguageReturnsMappedLocaleCodes(): void
    {
        $translator = $this->getMicrosoftTranslatorInstance();
        $translator->setLocaleMap($this->localeMap);

        $result = $translator->detectLanguage('Hola', true);

        $this->assertEquals('es_ES', $result);
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testGetLanguages
     */
    public function testGetLanguages(): void
    {
        $translator = $this->getMicrosoftTranslatorInstance();

        $result = $translator->getLanguages();

        $this->assertArrayHasKey('translation', $result);
        $this->assertNotEmpty($result['translation']);
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testConstructWithInvalidExcludedWordsFilePath
     */
    public function testConstructWithInvalidExcludedWordsFilePath(): void
    {
        $this->expectException(FileNotFoundException::class);

        new MicrosoftTranslator(
            getenv('MICROSOFT_SUBSCRIPTION_KEY'),
            getenv('MICROSOFT_SUBSCRIPTION_REGION'),
            MicrosoftTranslator::GLOBAL_BASE_URL,
            __DIR__ . '/Resources/DoesNotExist.json'
        );
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testConstructWithInvalidExcludedWordsFileContent
     */
    public function testConstructWithInvalidExcludedWordsFileContent(): void
    {
        $this->expectException(JsonException::class);

        new MicrosoftTranslator(
            getenv('MICROSOFT_SUBSCRIPTION_KEY'),
            getenv('MICROSOFT_SUBSCRIPTION_REGION'),
            MicrosoftTranslator::GLOBAL_BASE_URL,
            __DIR__ . '/Resources/invalidExcluded.json'
        );
    }

    /**
     * vendor/bin/phpunit --filter MicrosoftTranslatorTest::testTranslateExcludesWords
     */
    public function testTranslateExcludesWords(): void
    {
        /** @var Client|MockObject $client */
        $client = $this->createMock(Client::class);

        $client->expects($this->never())
            ->method('post');

        $translator = new MicrosoftTranslator(
            getenv('MICROSOFT_SUBSCRIPTION_KEY'),
            getenv('MICROSOFT_SUBSCRIPTION_REGION'),
            MicrosoftTranslator::GLOBAL_BASE_URL,
            __DIR__ . '/Resources/excluded.json'
        );

        $translator->setClient($client);

        $word = 'This word or phrase is excluded';

        $result = $translator->translate($word, 'en', 'es');

        $this->assertSame($word, $result);
    }

    /**
     * @return MicrosoftTranslator
     */
    private function getMicrosoftTranslatorInstance(): MicrosoftTranslator
    {
        return new MicrosoftTranslator(
            getenv('MICROSOFT_SUBSCRIPTION_KEY'),
            getenv('MICROSOFT_SUBSCRIPTION_REGION')
        );
    }
}
