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

namespace smacp\MachineTranslator\MicrosoftTranslator;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use smacp\MachineTranslator\Exception\FileNotFoundException;
use smacp\MachineTranslator\Interfaces\MachineTranslatorInterface;

/**
 * Class MicrosoftTranslator
 *
 * Translate text via the Microsoft Translator API.
 *
 * @author Stuart MacPherson
 *
 * @package smacp\MachineTranslator\MicrosoftTranslator
 *
 * @link https://docs.microsoft.com/en-us/azure/cognitive-services/translator/
 */
class MicrosoftTranslator implements MachineTranslatorInterface
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
     * Array of Microsoft Translator locales that the API supports for translation.
     *
     * @var string[]
     */
    public const MICROSOFT_TRANSLATION_LOCALES = [
        'af' => 'Afrikaans',
        'am' => 'Amharic',
        'ar' => 'Arabic',
        'as' => 'Assamese',
        'az' => 'Azerbaijani',
        'bg' => 'Bulgarian',
        'bn' => 'Bangla',
        'bs' => 'Bosnian',
        'ca' => 'Catalan',
        'cs' => 'Czech',
        'cy' => 'Welsh',
        'da' => 'Danish',
        'de' => 'German',
        'el' => 'Greek',
        'en' => 'English',
        'es' => 'Spanish',
        'et' => 'Estonian',
        'fa' => 'Persian',
        'fi' => 'Finnish',
        'fil' => 'Filipino',
        'fj' => 'Fijian',
        'fr' => 'French',
        'fr-CA' => 'French (Canada)',
        'ga' => 'Irish',
        'gu' => 'Gujarati',
        'he' => 'Hebrew',
        'hi' => 'Hindi',
        'hr' => 'Croatian',
        'ht' => 'Haitian Creole',
        'hu' => 'Hungarian',
        'hy' => 'Armenian',
        'id' => 'Indonesian',
        'is' => 'Icelandic',
        'it' => 'Italian',
        'iu' => 'Inuktitut',
        'ja' => 'Japanese',
        'kk' => 'Kazakh',
        'km' => 'Khmer',
        'kmr' => 'Kurdish (Northern)',
        'kn' => 'Kannada',
        'ko' => 'Korean',
        'ku' => 'Kurdish (Central)',
        'lo' => 'Lao',
        'lt' => 'Lithuanian',
        'lv' => 'Latvian',
        'lzh' => 'Chinese (Literary)',
        'mg' => 'Malagasy',
        'mi' => 'Māori',
        'ml' => 'Malayalam',
        'mr' => 'Marathi',
        'ms' => 'Malay',
        'mt' => 'Maltese',
        'mww' => 'Hmong Daw',
        'my' => 'Myanmar (Burmese)',
        'nb' => 'Norwegian',
        'ne' => 'Nepali',
        'nl' => 'Dutch',
        'or' => 'Odia',
        'otq' => 'Querétaro Otomi',
        'pa' => 'Punjabi',
        'pl' => 'Polish',
        'prs' => 'Dari',
        'ps' => 'Pashto',
        'pt' => 'Portuguese (Brazil)',
        'pt-PT' => 'Portuguese (Portugal)',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'sm' => 'Samoan',
        'sq' => 'Albanian',
        'sr-Cyrl' => 'Serbian (Cyrillic)',
        'sr-Latn' => 'Serbian (Latin)',
        'sv' => 'Swedish',
        'sw' => 'Swahili',
        'ta' => 'Tamil',
        'te' => 'Telugu',
        'th' => 'Thai',
        'ti' => 'Tigrinya',
        'tlh-Latn' => 'Klingon (Latin)',
        'tlh-Piqd' => 'Klingon (pIqaD)',
        'to' => 'Tongan',
        'tr' => 'Turkish',
        'ty' => 'Tahitian',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'vi' => 'Vietnamese',
        'yua' => 'Yucatec Maya',
        'yue' => 'Cantonese (Traditional)',
        'zh-Hans' => 'Chinese Simplified',
        'zh-Hant' => 'Chinese Traditional',
    ];

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
     * The underlying Client instance used for API communication.
     *
     * @var Client
     */
    private $client;

    /**
     * The Client response from the Microsoft translation API request.
     *
     * @var ResponseInterface|null
     */
    private $response;

    /**
     * Array of local locale code mappings to Microsoft Translator locales e.g.
     *
     * [
     *     'es_LA' => 'es',
     *     'my_serbian_code' => 'sr-Cyrl',
     * ]
     *
     * @var string[]
     */
    private $localeMap = [];

    /**
     * Array of regular expression patterns to identify variable 'placeholders' in a string.
     *
     * A variable 'placeholder' is a string fragment that may be used in a word or phrase to perform
     * string replacement e.g.
     *
     * - 'Hello %name%'
     * - 'Hello {name}'
     * - 'Hello {$name}'
     *
     * @var string[]
     */
    private $placeholderPatterns = [
        '/%([^%\s]+)%/',
    ];

    /**
     * Array of words and or phrases to exclude from machine translation.
     *
     * e.g.
     *
     * [
     *     'Facebook',
     *     'LinkedIn',
     *     'Some sentence I don't want machine translated',
     *     'Some sentence with a %placeholder%',
     * ]
     *
     * @var string[]
     */
    private $excludedWords = [];

    /**
     * MicrosoftTranslator Constructor
     *
     * @param string $subscriptionKey   The Microsoft secret key for the Translator subscription
     * @param string $region            The Microsoft Translator region e.g. global, northeurope
     * @param string $baseUrl           The Microsoft Translator base URL e.g. api.cognitive.microsofttranslator.com
     *
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct(
        string $subscriptionKey,
        string $region = MicrosoftTranslatorRegion::GLOBAL,
        string $baseUrl = self::GLOBAL_BASE_URL
    ) {
        $this->subscriptionKey = $subscriptionKey;
        $this->region = $region;
        $this->baseUrl = $baseUrl;
        
        $this->client = new Client();
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
     * Set localeMap
     *
     * @param string[] $localeMap
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
     * @return string[]
     */
    public function getLocaleMap(): array
    {
        return $this->localeMap;
    }

    /**
     * Set client
     *
     * @param Client $client
     *
     * @return MicrosoftTranslator
     */
    public function setClient(Client $client): MicrosoftTranslator
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
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
     * Attempts to normalise the given language code to a Microsoft Translator language code.
     *
     * @param string $code
     *
     * @return string
     */
    public function normaliseLanguageCode(string $code): string
    {
        if (array_key_exists($code, self::MICROSOFT_TRANSLATION_LOCALES)) {
            return $code;
        }

        if ($this->localeMap) {
            return $this->localeMap[$code] ?? '';
        }

        $code = str_replace('_', '-', strtolower($code));
        $find = ['-cn', '-tw'];
        $replace = ['-hans', '-hant'];
        $code = str_replace($find, $replace, $code);

        foreach (array_keys(self::MICROSOFT_TRANSLATION_LOCALES) as $locale) {
            if ($code === strtolower($locale)) {
                return $locale;
            }
        }

        return '';
    }

    /**
     * Sets placeholder patterns.
     *
     * @param string[] $placeholderPatterns
     *
     * @return MicrosoftTranslator
     */
    public function setPlaceholderPatterns(array $placeholderPatterns): MicrosoftTranslator
    {
        $this->placeholderPatterns = $placeholderPatterns;

        return $this;
    }

    /**
     * Get placeholder patterns.
     *
     * @return string[]
     */
    public function getPlaceholderPatterns(): array
    {
        return $this->placeholderPatterns;
    }

    /**
     * Adds a placeholder pattern.
     *
     * @param string $placeholderPattern
     *
     * @return MicrosoftTranslator
     */
    public function addPlaceholderPattern(string $placeholderPattern): MicrosoftTranslator
    {
        $this->placeholderPatterns[] = $placeholderPattern;

        return $this;
    }

    /**
     * Sets excluded words that should not be machine translated.
     *
     * @return MicrosoftTranslator
     */
    public function setExcludedWords(array $excludedWords): MicrosoftTranslator
    {
        $this->excludedWords = $excludedWords;

        return $this;
    }

    /**
     * Sets excluded words from a given file JSON path.
     *
     * The JSON file should contain an array of source strings in any locale that should not be
     * machine translated e.g.
     *
     * [
     *     "Hello",
     *     "A sentence with a %placeholder%",
     *     "Un mot francais"
     * ]
     *
     * @param string $file  The path to the excluded words JSON file
     *
     * @return MicrosoftTranslator
     *
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function setExcludedWordsFromFile(string $file): MicrosoftTranslator
    {
        if (!is_file($file)) {
            throw new FileNotFoundException('Excluded words JSON file not found.');
        }

        $contents = (string) file_get_contents($file);

        $excludedWords = json_decode($contents, true);

        if (!is_array($excludedWords)) {
            throw new JsonException('Failed to parse excluded words JSON file to an array');
        }

        $this->excludedWords = $excludedWords;

        return $this;
    }

    /**
     * Translates a string from one language to another.
     *
     * @param string $word   The source string to translate
     * @param string $from   The locale code for the source string
     * @param string $to     The locale to translate into
     * @param mixed[] $options Array of Optional Parameters for the translation request e.g. category, profanityAction etc
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
        if (!trim($word)) {
            throw new InvalidArgumentException('No word was given for translation.');
        }

        if ($this->excludedWords && in_array($word, $this->excludedWords)) {
            return $word;
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

        $url = $this->createApiUrl('/translate');

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

        $this->response = $this->client->post($url, $config);
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
        $url = $this->createApiUrl('/detect');

        $config = [
            'headers' => $this->getDefaultRequestHeaders(),
            'query' => [
                'api-version' => self::API_VERSION,
            ],
            'body' => json_encode([['Text' => $str]]),
        ];

        $this->response = $this->client->post($url, $config);
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
     * Gets Microsoft Translator languages for the given translation scopes.
     *
     * @link https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-languages
     *
     * @param string[] $scopes Array of scopes to get languages for e.g. translation, transliteration, dictionary.
     *
     * @return array[]
     *
     * @throws GuzzleException
     */
    public function getLanguages(array $scopes = ['translation']): array
    {
        $response = $this->client->get(
            'https://' . self::GLOBAL_BASE_URL . '/languages?api-version=' . self::API_VERSION
        );

        $contents = json_decode($response->getBody()->getContents(), true);

        $result = [];

        foreach ($scopes as $scope) {
            if (array_key_exists($scope, $contents)) {
                $result[$scope] = $contents[$scope];
            }
        }

        return $result;
    }

    /**
     * Creates a Microsoft Translator API url.
     *
     * @param string $uri   The URI e.g. /translate
     *
     * @return string
     */
    private function createApiUrl(string $uri): string
    {
        return 'https://' . $this->baseUrl . $uri;
    }

    /**
     * Gets placeholders that may be found within a string.
     *
     * @param string $str   The string to get the placeholders from
     *
     * @return string[]
     */
    private function getPlaceholders(string $str): array
    {
        $placeholders = [];

        foreach ($this->placeholderPatterns as $placeholderPattern) {
            preg_match_all($placeholderPattern, $str, $matches);

            if (!empty($matches[0])) {
                $placeholders = array_merge($placeholders, $matches[0]);
            }
        }

        return $placeholders;
    }

    /**
     * Creates an array of placeholder keys for an array of placeholder strings
     *
     * @param string[] $placeholders  The array of placeholder strings to create the mapping for
     *
     * @return string[]
     */
    private function createPlaceholdersMap(array $placeholders): array
    {
        $result = [];

        foreach (array_values($placeholders) as $key => $value) {
            $result[$value] =  '%' . ($key+1) . '%';
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

    /**
     * Determines if a given string contains HTML.
     *
     * @param string $str
     *
     * @return bool
     */
    private function containsHtml(string $str): bool
    {
        return $str !== strip_tags($str);
    }
}
