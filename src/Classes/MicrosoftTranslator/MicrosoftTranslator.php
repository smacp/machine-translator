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

namespace smacp\MachineTranslator\Classes\MicrosoftTranslator;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use smacp\MachineTranslator\Classes\MachineTranslator;

/**
 * Class MicrosoftTranslator
 *
 * Provides API communication and methods for the Microsoft Translator API.
 *
 * @author Stuart MacPherson
 *
 * @package smacp\MachineTranslator\Classes\MicrosoftTranslator
 *
 * @link https://docs.microsoft.com/en-us/azure/cognitive-services/translator/
 */
class MicrosoftTranslator implements MachineTranslator
{
    /** @var string */
    public const PROVIDER = 'Microsoft';

    /**
     * The Microsoft Translator API version.
     *
     * @var string
     */
    public const API_VERSION = '3.0';

    /**
     * The Microsoft Translator base URL for global requests.
     *
     * @var string
     */
    public const GLOBAL_BASE_URL = 'api.cognitive.microsofttranslator.com';

    /**
     * The Microsoft Translator base URL for US requests.
     *
     * @var string
     */
    public const US_BASE_URL = 'api-nam.cognitive.microsofttranslator.com';

    /**
     * The Microsoft Translator base URL for European requests.
     *
     * @var string
     */
    public const EUROPE_BASE_URL = 'api-eur.cognitive.microsofttranslator.com';

    /**
     * The Microsoft Translator base URL for Asia and Pacific requests.
     *
     * @var string
     */
    public const ASIA_BASE_URL = 'api-apc.cognitive.microsofttranslator.com';

    /**
     * The Microsoft Translation subscription secret key.
     *
     * This value should be a valid secret key for the Translator API subscription NOT the subscription id itself.
     *
     * @var string
     */
    private $subscriptionKey;

    /**
     * The Microsoft Translation base URL e.g. api.cognitive.microsofttranslator.com.
     *
     * @var string
     */
    private $baseUrl;

    /**
     * The Microsoft Translator / subscription region e.g. europenorth.
     *
     * @see MicrosoftTranslatorRegion
     *
     * @var string
     */
    private $region;

    /**
     * The Client response from the Microsoft translation API request.
     *
     * @var ResponseInterface|null
     */
    private $response;

    /**
     * Array of Microsoft translator locales.
     *
     * @var string[]
     */
    private $locales = [
        'ar'       => 'Arabic',
        'bs-Latn'  => 'Bosnian (Latin)',
        'bg'       => 'Bulgarian',
        'ca'       => 'Catalan',
        'zh-CHS'   => 'Chinese Simplified',
        'zh-CHT'   => 'Chinese Traditional',
        'hr'       => 'Croatian',
        'cs'       => 'Czech',
        'da'       => 'Danish',
        'nl'       => 'Dutch',
        'en'       => 'English',
        'et'       => 'Estonian',
        'fi'       => 'Finnish',
        'fr'       => 'French',
        'de'       => 'German',
        'el'       => 'Greek',
        'ht'       => 'Haitian Creole',
        'he'       => 'Hebrew',
        'hi'       => 'Hindi',
        'mww'      => 'Hmong Daw',
        'hu'       => 'Hungarian',
        'id'       => 'Indonesian',
        'it'       => 'Italian',
        'ja'       => 'Japanese',
        'sw'       => 'Kiswahili',
        'tlh'      => 'Klingon',
        'tlh-Qaak' => 'Klingon (pIqaD)',
        'ko'       => 'Korean',
        'lv'       => 'Latvian',
        'lt'       => 'Lithuanian',
        'ms'       => 'Malay',
        'mt'       => 'Maltese',
        'no'       => 'Norwegian',
        'fa'       => 'Persian',
        'pl'       => 'Polish',
        'pt'       => 'Portuguese',
        'otq'      => 'Querétaro Otomi',
        'ro'       => 'Romanian',
        'ru'       => 'Russian',
        'sr-Cyrl'  => 'Serbian (Cyrillic)',
        'sr-Latn'  => 'Serbian (Latin)',
        'sk'       => 'Slovak',
        'sl'       => 'Slovenian',
        'es'       => 'Spanish',
        'sv'       => 'Swedish',
        'th'       => 'Thai',
        'tr'       => 'Turkish',
        'uk'       => 'Ukrainian',
        'ur'       => 'Urdu',
        'vi'       => 'Vietnamese',
        'cy'       => 'Welsh',
        'yua'      => 'Yucatec Maya',
    ];

    /**
     * Array of local locale code mappings to Microsoft Translator locales e.g.
     *
     * [
     *     'my_serbian_code' => 'sr-Cyrl',
     * ]
     *
     * @var string[]
     */
    private $localeMap = [];

    /**
     * MicrosoftTranslator Constructor
     *
     * @param string $subscriptionKey The Microsoft secret key for the Translator subscription
     * @param string $region          The Microsoft Translator region
     * @param string $baseUrl         The Microsoft Translator base URL e.g. api.cognitive.microsofttranslator.com
     */
    public function __construct(
        string $subscriptionKey,
        string $region,
        string $baseUrl = self::GLOBAL_BASE_URL
    ) {
        $this->subscriptionKey = $subscriptionKey;
        $this->region = $region;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Get provider
     *
     * @return string
     */
    public function getProvider(): string
    {
        return self::PROVIDER;
    }

    /**
     * Gets locales
     *
     * @return array
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * Set localeMap
     *
     * @param array $localeMap
     *
     * @return MicrosoftTranslator
     */
    public function setLocaleMap(array $localeMap): MicrosoftTranslator
    {
        $this->localeMap = $localeMap;

        return $this;
    }

	/**
     * Get localeMap
     *
     * @return array
     */
    public function getLocaleMap(): array
    {
        return $this->localeMap;
    }

    /**
     * Attempts to normalise the given language code to a Microsoft translation code.
     *
     * @param string $code
     *
     * @return string
     */
    public function normaliseLanguageCode(string $code): string
    {
        if (isset($this->locales[$code])) {
            return $code;
        }

        $locales = array_keys($this->getLocales());

        $localeMap = $this->localeMap;

        if (count($localeMap) > 0) {
            return $localeMap[$code] ?? '';
        }

        $code = str_replace('_', '-', strtolower($code));
        $find = ['-cn', '-tw'];
        $replace = ['-chs', '-cht'];
        $code = str_replace($find, $replace, $code);

        foreach ($locales as $locale) {
            if ($code === strtolower($locale)) {
                return $locale;
            }
        }

        return '';
    }

    /**
     * Translates a string from one language to another.
     *
     * @param string $word   The source string to translate
     * @param string $from   The locale code for the source string
     * @param string $to     The locale to translate into
     * @param array $options Array of Optional Parameters for the translation request e.g. category, profanityAction etc
     *
     * @return string
     *
     * @link https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-translate
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function translate(string $word, string $from, string $to, array $options = []): string
    {
        if (!$word) {
            throw new InvalidArgumentException('No word was given for translation.');
        }

        $from = $this->normaliseLanguageCode($from);
        $to = $this->normaliseLanguageCode($to);

        if (!$from) {
            throw new InvalidArgumentException('No Microsoft locale code could be resolved for the from locale.');
        }

        if (!$to) {
            throw new InvalidArgumentException('No Microsoft locale code could be resolved for the to locale.');
        }

        if ($to === $from) {
            throw new InvalidArgumentException('Locales for from and to are the same.');
        }

        // extract and preserve placeholders
        $placeholders = $this->getPlaceholders($word);

        if ($placeholders) {
            $placeholders = $this->createPlaceholdersMap($placeholders);
            $word = str_replace(array_keys($placeholders), array_values($placeholders), $word);
        }

        $url = 'https://' . $this->baseUrl . '/translate';

        $queryParameters = [
            'api-version' => self::API_VERSION,
            'from' => $from,
            'to' => $to,
        ];

        if ($this->containsHtml($word)) {
            $queryParameters['textType'] = 'html';
        }

        $queryParameters = array_merge($queryParameters, $options);

        $config = [
            'headers' => $this->getDefaultRequestHeaders(),
            'query' => $queryParameters,
            'body' => json_encode([['Text' => $word]]),
        ];

        $client = new Client($config);
        $this->response = $client->post($url);
        $contents = json_decode($this->response->getBody()->getContents(), true);

        $translated = $contents[0]['translations'][0]['text'];

        if ($placeholders) {
            $translated = str_replace(array_values($placeholders), array_keys($placeholders), $translated);
        }

        return $translated;
    }

    /**
     * Detects the language for the given string.
     *
     * @param string $str The string to detect the language for
     * @param bool $normaliseLocaleCode Whether to return a normalised local code for the Microsoft locale code
     *
     * @link https://docs.microsoft.com/en-gb/azure/cognitive-services/translator/reference/v3-0-detect
     *
     * @return string
     *
     * @throws GuzzleException
     */
    public function detectLanguage(string $str, bool $normaliseLocaleCode = false): string
    {
        $url = 'https://' . $this->baseUrl . '/detect';

        $config = [
            'headers' => $this->getDefaultRequestHeaders(),
            'query' => [
                'api-version' => self::API_VERSION,
            ],
            'body' => json_encode([['Text' => $str]]),
        ];

        $client = new Client($config);

        $this->response = $client->post($url);
        $contents = json_decode($this->response->getBody()->getContents(), true);

        $language = $contents[0]['language'];

        if ($normaliseLocaleCode && $this->localeMap) {
            $map = array_flip($this->localeMap);
            if (array_key_exists($language, $map)) {
                $language = $map[$language];
            }
        }

        return $language;
    }

    /**
     * Gets response.
     *
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * Determines whether a string contains HTML tags.
     *
     * @param string $str
     *
     * @return bool
     */
    public function containsHtml(string $str): bool
    {
        return $str !== strip_tags($str);
    }

    /**
     * Matches placeholders within a string.
     *
     * @param string $str
     *
     * @return array
     */
    private function getPlaceholders(string $str): array
    {
        preg_match_all('/%([^%\s]+)%/', $str, $matches);

        return $matches[0] ??  [];
    }

    /**
     * Creates array of placeholder keys and values
     *
     * @param array $array
     *
     * @return array
     */
    private function createPlaceholdersMap($array): array
    {
        $result = [];

        foreach ($array as $key => $val) {
            $result[$val] = '[[' . ($key+1) . ']]';
        }

        return $result;
    }

    /**
     * Gets default headers for a request.
     *
     * @return string[]
     */
    private function getDefaultRequestHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Region' => $this->region,
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
        ];
    }
}
