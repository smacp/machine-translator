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

namespace smacp\MachineTranslator\Classes;

use GuzzleHttp\Client;
use Exception;

/**
 * Provides methods to translate words via Microsoft translation service
 *
 * @author Stuart MacPherson
 */

class MicrosoftTranslator implements MachineTranslator
{
    /** @var string */
    private const HTTP_STATUS_CODE_OK = 200;

    /** @var string */
    public const PROVIDER = 'Microsoft';

    /**
     * The Microsoft client subscription key.
     *
     * @var string
     */
    private $clientKey;

    /**
     * The response from the Microsoft translation API.
     *
     * @var string
     */
    private $response;

    /**
     * The Microsoft JWT access token obtained for the Microsoft subscription key.
     *
     * @var string
     */
    private $accessToken;

    /**
     * Whether to decode HTML entities in translated responses.
     *
     * @var bool
     */
    private $decodeHtmlEntities;

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
     *     'my_serbain_code' => 'sr-Cyrl',
     * ]
     *
     * @var string[]
     */
    private $localeMap = [];

    /**
     * MicrosoftTranslator Constructor
     *
     * @param string $clientKey
     * @param bool $decodeHtmlEntities
     *
	 * @return MicrosoftTranslator
     */
    public function __construct(string $clientKey, bool $decodeHtmlEntities = true)
    {
        $this->clientKey = $clientKey;
        $this->decodeHtmlEntities = $decodeHtmlEntities;
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
     * Attempts to normalise the given language code to a Microsoft translation code
     *
     * @param string $code
     *
     * @return string
     */
    public function normaliseLanguageCode($code): string
    {
        if (isset($this->locales[$code])) {
            return $code;
        }

        $locales = array_keys($this->getLocales());

        $localeMap = $this->localeMap;

        if (count($localeMap) > 0) {
            return isset($localeMap[$code]) ? $localeMap[$code] : '';
        }

        $code = str_replace('_', '-', strtolower($code));
        $find = ['-cn', '-tw'];
        $replace = ['-chs', '-cht'];
        $code = str_replace($find, $replace, $code);

        foreach ($locales as $mLocale) {
            if ($code === strtolower($mLocale)) {
                return $mLocale;
            }
        }

        return '';
    }

    /**
     * Translates a string
     *
     * @param string $word The source string to translate
     * @param string $from The locale code for the source string
     * @param string $to The locale to translate into
     *
     * @return string
     */
    public function translate(string $word, string $from, string $to): string
    {
        if (!$word) {
            return '';
        }

        $from = $this->normaliseLanguageCode($from);
        $to = $this->normaliseLanguageCode($to);

        if (!$from || !$to) {
            return '';
        }

        if ($to === $from) {
            return $word;
        }

        // extract and preserve placeholders
        $extracted = $this->getPlaceholders($word);
        $placeholders = [];

        if (count($extracted) > 0) {
            $placeholders = $this->createPlaceholdersMap($extracted);
        }

        if (count($placeholders) > 0) {
            $word = str_replace(array_keys($placeholders), array_values($placeholders), $word);
        }

        $url = 'http://api.microsofttranslator.com/V2/Http.svc/Translate?text=' . urlencode($word) . '&from=' . $from . '&to=' . $to;
        $accessToken = $this->getAccessToken();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization:bearer ' . $accessToken]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->response = curl_exec($ch);

        preg_match_all('/<string (.*?)>(.*?)<\/string>/s', $this->response, $matches);

        if (!isset($matches[2][0])) {
            return '';
        }

        $translated = $matches[2][0];
        $translated = count($placeholders) > 0 ? str_replace(array_values($placeholders), array_keys($placeholders), $translated) : $translated;

        // fix any html entity conversion that may have been applied
        if ($this->decodeHtmlEntities === true) {
            $translated = $this->decodeEntities($translated);
        }

        return $translated;
    }

    /**
     * Detects the language for the given string
     *
     * @param string $str
     * @param bool $normaliseLocaleCode
     *
     * @return string
     */
    public function detectLanguage($str, $normaliseLocaleCode = false): string
    {
        $accessToken = $this->getAccessToken();

        $url = 'http://api.microsofttranslator.com/V2/Http.svc/Detect?text=' . urlencode($str);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization:bearer ' . $accessToken]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->response = curl_exec($ch);

        preg_match_all('/<string (.*?)>(.*?)<\/string>/s', $this->response, $matches);

        $result = '';

        if (isset($matches[2][0]) && $matches[2][0]) {
            $result = $matches[2][0];
            if ($normaliseLocaleCode && $this->localeMap) {
                $map = array_flip($this->localeMap);
                if (isset($map[$result])) {
                    $result = $map[$result];
                }
            }
        }

        return $result;
    }

    /**
     * Gets response
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets decodeHtmlEntities
     *
     * @param bool $decodeHtmlEntities
     *
     * @return MicrosoftTranslator
     */
    public function setDecodeHtmlEntities($decodeHtmlEntities): MicrosoftTranslator
    {
        $this->decodeHtmlEntities = $decodeHtmlEntities;

        return $this;
    }

    /**
     * Determines whether string contains HTML tags
     *
     * @param string $str
     *
     * @return bool
     */
    public function containsHtml($str): bool
    {
        return $str !== strip_tags($str) ? true : false;
    }

    /**
     * Gets an access token for the Microsoft Translator service
     *
     * @throws Exception
     *
     * @return string
     */
    private function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $url = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken?Subscription-Key=' . urlencode($this->clientKey);

        // Get a JWT for the Microsoft Translator API.
        $client = new Client();

        $response = $client->post($url);

        $statusCode = $response->getStatusCode();

        if ($statusCode !== self::HTTP_STATUS_CODE_OK) {
            throw new Exception('No access token could be obtained.');
        }

        $this->accessToken = $response->getBody()->getContents();

        return $this->accessToken;
    }

    /**
     * Fixes html encoding anomolies returned by the Microsoft translator service and decodes html entities
     *
     * @param string $str
     *
     * @return string
     */
    private function decodeEntities($str, $applyCdataTags = false): string
    {
        // fix any odd html entity conversion
        $find = ['&amp;lt;', '&amp;gt;'];
        $replace = ['&lt;', '&gt;'];
        $str = str_replace($find, $replace, $str);

        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');

        if ($applyCdataTags === true && !$this->isCdata($str)) {
            // wrap in CDATA tags
            $str = '<![CDATA[' . $str . ']]>';
        } else {
            // fix any trailing whitespace in CDATA tags
            $str = preg_replace('/<!\[CDATA\[(.*)\]\][\s]{1,}>/', "<![CDATA[$1]]>", $str);
        }

        return $str;
    }

    /**
     * Matches placeholders within a string
     *
     * @param string $str
     *
     * @return array
     */
    private function getPlaceholders($str): array
    {
        preg_match_all('/%([^%\s]+)%/', $str, $matches);

        return isset($matches[0]) ? $matches[0] : [];
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
     * Determines whether string is a CDATA string
     *
     * @param string $str
     *
     * @return bool
     */
    private function isCdata($str): bool
    {
        return strpos($str, '<![CDATA[') > -1;
    }
}
