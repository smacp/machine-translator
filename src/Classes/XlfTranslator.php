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

use Exception;
use smacp\MachineTranslator\Classes\SimpleXmlExtended;

/**
 * Translates xlf files
 *
 * @author Stuart MacPherson
 */
class XlfTranslator
{
    /** @var MachineTranslator */
    protected $translator;

    /**
     * The path to the source directory containing the Xlf files to translate.
     *
     * @var string
     */
    protected $dir;

    /**
     * Array of Xlf filepaths that have been processed.
     *
     * @var string[]
     */
    protected $parsed;

    /**
     * Whether to update Xlf files with translations.
     *
     * @var bool
     */
    protected $commit = true;

    /**
     * Array of locales that should be translated.
     *
     * @var string[]
     */
    protected $locales = [];

    /**
     * Array of locales that should be excluded.
     *
     * @var string[]
     */
    protected $excludedLocales = ['en_GB', 'en_US'];

    /**
     * The source locale of the Xlf files.
     *
     * @var string
     */
    protected $sourceLocale = 'en_GB';

    /**
     * Array of catalogues that should be translated e.g. messages, validators etc.
     *
     * @var string[]
     */
    protected $catalogues = [];

    /**
     * Whether to translate 'new' trans units only.
     *
     * @var bool
     */
    protected $newOnly = false;

    /**
     * The number of machine translation requests that have failed.
     *
     * @var int
     */
    protected $mtFailCount = 0;

    /**
     * The maximum number of failed machine translation requests before the process should exit.
     *
     * @var integer
     */
    protected $maxMtFailCount = 10;

    /**
     * Custom Xlf trans unit attributes.
     *
     * @var string[]
     */
    protected $attributes = [
        'mt'      => 'machinetranslated',
        'mt_date' => 'datemachinetranslated'
    ];

    /**
     * Whether to keep strings that have already been translated. Set to false to re-translate existing translations.
     *
     * @var bool
     */
    protected $memory = true;

    /**
     * Whether to write debug to STDOUT.
     *
     * @var bool
     */
    protected $output = true;

    /**
     * Whether to output translated strings during the process.
     *
     * @var bool
     */
    protected $outputTranslated = false;

    /**
     * XlfTranslator constructor.
     *
     * @param MachineTranslator $translator The MachineTranslator instance
     * @param string            $dir        The source directory to translate Xlf files in
     */
    public function __construct(MachineTranslator $translator, string $dir)
    {
        $this->translator = $translator;
        $this->dir = $dir;
    }

    /**
     * Set translator
     *
     * @param MachineTranslator $translator
     *
     * @return XlfTranslator
     */
    public function setTranslator(MachineTranslator $translator): XlfTranslator
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * Set locales
     *
     * @param array $locales
     *
     * @return XlfTranslator
     */
    public function setLocales(array $locales): XlfTranslator
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * Set excludedLocales
     *
     * @param array $locales
     *
     * @return XlfTranslator
     */
    public function setExcludedLocales(array $locales): XlfTranslator
    {
        $this->excludedLocales = $locales;

        return $this;
    }

    /**
     * Set dir
     *
     * @param string $dir
     *
     * @return XlfTranslator
     */
    public function setDir(string $dir): XlfTranslator
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * Set sourceLocale
     *
     * @param string $locale
     *
     * @return XlfTranslator
     */
    public function setSourceLocale($locale): XlfTranslator
    {
        $this->sourceLocale = $locale;

        return $this;
    }

    /**
     * Set catalogues
     *
     * @param array $catalogues
     *
     * @return XlfTranslator
     */
    public function setCatalogues(array $catalogues): XlfTranslator
    {
        $this->catalogues = $catalogues;

        return $this;
    }

    /**
     * Set newOnly
     *
     * @param bool $newOnly
     *
     * @return XlfTranslator
     */
    public function setNewOnly(bool $newOnly): XlfTranslator
    {
        $this->newOnly = $newOnly;

        return $this;
    }

    /**
     * Set commit
     *
     * @param bool $commit
     *
     * @return XlfTranslator
     */
    public function setCommit(bool $commit): XlfTranslator
    {
        $this->commit = $commit;

        return $this;
    }

    /**
     * Set output
     *
     * @param bool $output
     *
     * @return XlfTranslator
     */
    public function setOutput(bool $output): XlfTranslator
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Set outputTranslated
     *
     * @param bool $outputTranslated
     *
     * @return XlfTranslator
     */
    public function setOutputTranslated(bool $outputTranslated): XlfTranslator
    {
        $this->outputTranslated = $outputTranslated;

        return $this;
    }

    /**
     * Set memory
     *
     * @param bool $memory
     *
     * @return XlfTranslator
     */
    public function setMemory(bool $memory): XlfTranslator
    {
        $this->memory = $memory;

        return $this;
    }

    /**
     * Machine translates
     *
     * @return XlfTranslator
     *
     * @throws Exception
     */
    public function translate(): XlfTranslator
    {
        $this->parsed = [];
        $this->mtFailCount = 0;

        $provider = $this->translator->getProvider();
        $cataloguesTranslated = [];
        $cataloguesSkipped = [];
        $localesTranslated = [];
        $localesSkipped = [];
        $strRequested = 0;
        $strTranslated = 0;
        $filesWritten = 0;

        if ($this->output) {
            echo PHP_EOL;
            echo '-----------------------------------------' . PHP_EOL;
            echo 'XlfTranslator' . PHP_EOL;
            echo '-----------------------------------------' . PHP_EOL;
            echo 'MT provider: ' . $provider . PHP_EOL;
            echo PHP_EOL;
        }

        if ($dh = opendir($this->dir)) {
            if ($this->output) {
                echo 'Translating xlf in: ' . $this->dir . PHP_EOL;
                echo PHP_EOL;
            }

            while (false !== ($filename = readdir($dh))) {
                $filePath = $this->dir . $filename;
                if (is_file($filePath)) {
                    $parts = explode('.', $filename);

                    if (strpos($filePath, '.xlf') === false) {
                        if ($this->output) {
                            echo 'Not an xlf file. Skipping ' . $filePath . PHP_EOL;
                        }
                        continue;
                    }

                    if (count($parts) !== 3) {
                        throw new Exception('Cannot parse file. Expected file in format catalogue.locale.xlf.');
                    }

                    $catalogue = $parts[0];
                    $locale = $parts[1];

                    $i = 0;

                    if (!$this->shouldParseCatalogue($catalogue)) {
                        if (!in_array($catalogue, $cataloguesSkipped)) {
                            $cataloguesSkipped[] = $catalogue;
                        }
                        continue;
                    }

                    if (!$this->shouldParseLocale($locale)) {
                        if (!in_array($locale, $localesSkipped)) {
                            $localesSkipped[] = $locale;
                        }
                        continue;
                    }

                    if ($this->output) {
                        echo 'File: ' . $filename . PHP_EOL;
                        echo 'Catalogue: ' . $catalogue . PHP_EOL;
                        echo 'Locale: ' . $locale . PHP_EOL;
                        echo 'MT locale: ' . $this->translator->normaliseLanguageCode($locale) . PHP_EOL;
                        echo PHP_EOL;
                        echo 'P: ';
                    }

                    $contents = file_get_contents($filePath);
                    $xlfData = new SimpleXMLExtended($contents);
                    $new = [];

                    $this->mtFailCount = 0;

                    foreach ($xlfData->file->body as $bItem) {
                        $xlfStrTranslated = 0;

                        foreach ($bItem as $bValue) {
                            if ($this->mtFailCount >= $this->maxMtFailCount) {
                                // skip to the end as we may have hit the flood limit
                                continue;
                            }

                            $targetAttributes = $bValue->target->attributes();

                            if ($this->newOnly === true && (!isset($targetAttributes['state']) || (string) $targetAttributes['state'] !== 'new')) {
                                continue;
                            }

                            $source = (string) $bValue->source;
                            $target = (string) $bValue->target;
                            $attributes = $bValue->attributes();

                            if ($source && $target) {
                                if ($source !== $target) {
                                    continue;
                                }

                                if ($this->memory && isset($attributes[$this->attributes['mt']])) {
                                    continue;
                                }

                                $strRequested++;

                                $translated = $this->translator->translate($source, $this->sourceLocale, $locale);

                                if ($translated) {
                                    $new[$i]['source'] = $source;
                                    $new[$i]['target'] = $translated;

                                    if (!isset($attributes[$this->attributes['mt']])) {
                                        $bValue->addAttribute($this->attributes['mt'], 1);
                                        $bValue->addAttribute($this->attributes['mt_date'], date('Y-m-d H:i:s'));
                                    }

                                    $bValue->attributes()->{$this->attributes['mt']} = 1;

                                    if ($this->translator->containsHtml($translated)) {
                                        $bValue->target = null;
                                        $bValue->target->addCData($translated);
                                    } else {
                                        $bValue->target = $translated;
                                    }

                                    $i++;
                                    $xlfStrTranslated++;
                                    $strTranslated++;

                                    if ($this->output) {
                                        echo '.';
                                    }
                                } else {
                                    $this->mtFailCount++;
                                }
                            }
                        }

                        if ($this->output) {
                            if ($xlfStrTranslated === 0) {
                                echo 'No strings translated';
                            }
                            echo PHP_EOL;
                            echo 'T: ' . $xlfStrTranslated . PHP_EOL;
                            echo PHP_EOL;
                        }
                    }

                    if (count($new) > 0) {
                        if ($this->output && $this->outputTranslated) {
                            foreach ($new as $key => $row) {
                                echo '[#' . ($key+1) . '] Source: ' . $row['source'] . PHP_EOL;
                                echo '[#' . ($key+1) . '] Translated: ' . $row['target'] . PHP_EOL;
                            }
                            echo PHP_EOL;
                        }

                        if ($this->commit === true) {
                            $this->write($xlfData, $filePath);
                            $filesWritten++;
                        }

                        if (!in_array($catalogue, $cataloguesTranslated)) {
                            $cataloguesTranslated[] = $catalogue;
                        }

                        $localesTranslated[] = $locale;

                        $this->parsed[] = $filename;
                    }
                }
            }

            closedir($dh);
        }

        if ($this->output) {
            echo PHP_EOL;
            echo 'Done' . PHP_EOL;
            echo '-----------------------------------------' . PHP_EOL;
            echo 'Total locales translated: ' . count($localesTranslated) . PHP_EOL;
            echo 'Total strings requested: ' . $strRequested . PHP_EOL;
            echo 'Total strings translated: ' . $strTranslated . PHP_EOL;
            echo 'Catalogues translated: ' . (count($cataloguesTranslated) === 0 ? '0' : implode(', ', $cataloguesTranslated)) . PHP_EOL;

            if ($cataloguesSkipped) {
                echo 'Catalogues skipped: ' . implode(', ', $cataloguesSkipped) . PHP_EOL;
            }

            if ($localesSkipped) {
                echo 'Locales skipped: ' .implode(', ', $localesSkipped) . PHP_EOL;
            }

            echo 'xlf updated: ' . $filesWritten . PHP_EOL;
        }

        return $this;
    }

    /**
     * Determines whether catalogue should be parsed
     *
     * @param string $catalogue
     *
     * @return bool
     */
    protected function shouldParseCatalogue(string $catalogue): bool
    {
        if (count($this->catalogues) > 0 && !in_array($catalogue, $this->catalogues)) {
            return false;
        }

        return true;
    }

    /**
     * Determines whether locale should be parsed
     *
     * @param string $locale
     *
     * @return bool
     */
    protected function shouldParseLocale(string $locale): bool
    {
        if (!$this->translator->normaliseLanguageCode($locale)) {
            return false;
        }

        if ($this->locales && !in_array($locale, $this->locales)) {
            return false;
        }

        if ($this->excludedLocales && in_array($locale, $this->excludedLocales)) {
            return false;
        }

        return $this->sourceLocale !== $locale;
    }

    /**
     * Writes to file
     *
     * @param SimpleXMLExtended $xmlData
     * @param string $file
     *
     * @return XlfTranslator
     */
    protected function write(SimpleXMLExtended $xmlData, string $file): XlfTranslator
    {
        $xml = $xmlData->asXML();
        $fwh = fopen($file, 'w');
        fwrite($fwh, $xml);
        fclose($fwh);

        return $this;
    }
}
