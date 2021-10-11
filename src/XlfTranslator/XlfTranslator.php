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

namespace smacp\MachineTranslator\XlfTranslator;

use Exception;
use Psr\Log\LoggerInterface;
use smacp\MachineTranslator\Interfaces\MachineTranslatorInterface;
use smacp\MachineTranslator\lib\SimpleXmlExtended;
use smacp\MachineTranslator\Logger\Logger;

/**
 * Class XlfTranslator
 *
 * Translates xlf files in a given directory using a MachineTranslatorInterface instance. Xlf file names are
 * expected in the format:
 *
 * domain.locale.xlf
 *
 * e.g.
 *
 * messages.de.xlf
 * validators.de.xlf
 *
 * @author Stuart MacPherson
 *
 * @package smacp\MachineTranslator\XlfTranslator
 */
class XlfTranslator
{
    /** @var string */
    private const XLIFF_FILE_EXTENSION = '.xlf';

    /** @var MachineTranslatorInterface */
    private $translator;

    /** @var LoggerInterface */
    private $logger;

    /**
     * The path to the source directory containing the Xlf files to translate.
     *
     * @var string
     */
    private $dir;

    /**
     * Array of Xlf file paths that have been processed.
     *
     * @var string[]
     */
    private $parsed;

    /**
     * Whether to update Xlf files with translations.
     *
     * @var bool
     */
    private $commit = true;

    /**
     * Array of locales that should be translated.
     *
     * @var string[]
     */
    private $locales = [];

    /**
     * Array of locales that should be excluded.
     *
     * @var string[]
     */
    private $excludedLocales = ['en_GB', 'en_US'];

    /**
     * The source locale of the Xlf files.
     *
     * @var string
     */
    private $sourceLocale = 'en_GB';

    /**
     * Array of catalogues that should be translated e.g. messages, validators etc.
     *
     * @var string[]
     */
    private $catalogues = [];

    /**
     * Whether to translate 'new' trans units only.
     *
     * @var bool
     */
    private $newOnly = false;

    /**
     * The number of machine translation requests that have failed.
     *
     * @var int
     */
    private $mtFailCount = 0;

    /**
     * The maximum number of failed machine translation requests before the process should exit.
     *
     * @var integer
     */
    private $maxMtFailCount = 10;

    /**
     * Custom Xlf trans-unit attributes used and written by the process.
     *
     * @var string[]
     */
    private $attributes = [
        'machineTranslated' => 'machinetranslated',
        'machineTranslatedDate' => 'datemachinetranslated'
    ];

    /**
     * Whether to output translated strings to the log during the process.
     *
     * @var bool
     */
    private $outputTranslated = false;

    /**
     * XlfTranslator constructor.
     *
     * @param MachineTranslatorInterface $translator The MachineTranslator instance
     * @param string $dir                            The source directory to translate Xlf files in
     * @param LoggerInterface|null $logger           LoggerInterface instance to log process output
     *
     */
    public function __construct(MachineTranslatorInterface $translator, string $dir, ?LoggerInterface $logger = null)
    {
        $this->translator = $translator;
        $this->dir = $dir;

        if (!$logger instanceof LoggerInterface) {
            $logger = new Logger();
        }

        $this->logger = $logger;
    }

    /**
     * Set translator
     *
     * @param MachineTranslatorInterface $translator
     *
     * @return XlfTranslator
     */
    public function setTranslator(MachineTranslatorInterface $translator): XlfTranslator
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * Set logger
     *
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger): XlfTranslator
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set locales
     *
     * @param string[] $locales
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
     * @param string[] $locales
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
    public function setSourceLocale(string $locale): XlfTranslator
    {
        $this->sourceLocale = $locale;

        return $this;
    }

    /**
     * Set catalogues
     *
     * @param string[] $catalogues
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

        $this->logger->info('-----------------------------------------');
        $this->logger->info('XlfTranslator');
        $this->logger->info('-----------------------------------------');
        $this->logger->info('MT provider: ' . $provider);
        $this->logger->info('');

        if ($dh = opendir($this->dir)) {
            $this->logger->info('Translating xlf in: ' . $this->dir);
            $this->logger->info('');

            while (false !== ($filename = readdir($dh))) {
                $filePath = $this->dir . $filename;

                if (is_file($filePath)) {
                    if (strpos($filePath, self::XLIFF_FILE_EXTENSION) === false) {
                        $this->logger->warning('Not an xlf file. Skipping ' . $filePath);
                        $this->logger->info('');
                        continue;
                    }

                    $parts = explode('.', $filename);

                    if (count($parts) !== 3) {
                        throw new Exception('Cannot parse file. Expected file in format catalogue.locale.xlf.');
                    }

                    $catalogue = $parts[0];
                    $locale = $parts[1];

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

                    $this->logger->info('File: ' . $filename);
                    $this->logger->info('Catalogue: ' . $catalogue);
                    $this->logger->info('Locale: ' . $locale);

                    $fileContents = file_get_contents($filePath);

                    if (!$fileContents) {
                        $this->logger->info('Failed to load file contents for ' . $filePath);

                        continue;
                    }

                    $xlfData = new SimpleXMLExtended($fileContents);

                    $new = [];
                    $i = 0;
                    $this->mtFailCount = 0;

                    foreach ($xlfData->file->body as $element) {
                        $xlfStrTranslated = 0;

                        foreach ($element as $transUnit) {
                            if ($this->mtFailCount >= $this->maxMtFailCount) {
                                // skip to the end as we may have hit the rate limit
                                continue;
                            }

                            $attributes = $transUnit->attributes();
                            $targetAttributes = $transUnit->target->attributes();

                            if ($this->newOnly === true &&
                                (!isset($targetAttributes['state']) || (string) $targetAttributes['state'] !== 'new')
                            ) {
                                // target string is not a 'new' translation
                                continue;
                            }

                            $source = (string) $transUnit->source;
                            $target = (string) $transUnit->target;

                            if ($source && $target) {
                                if ($source !== $target) {
                                    // target is already translated
                                    continue;
                                }

                                $strRequested++;

                                $translated = $this->translator->translate($source, $this->sourceLocale, $locale);

                                if ($translated) {
                                    $new[$i]['source'] = $source;
                                    $new[$i]['target'] = $translated;

                                    /** @var SimpleXmlExtended $targetNode */
                                    $targetNode = $transUnit->target;
                                    $mtAttr = $this->attributes['machineTranslated'];
                                    $mtDateAttr = $this->attributes['machineTranslatedDate'];
                                    $mtDate = date('Y-m-d H:i:s');

                                    if (!isset($attributes[$mtAttr])) {
                                        $transUnit->addAttribute($mtAttr, '1');
                                    } else {
                                        $transUnit->attributes()->{$mtAttr} = 1;
                                    }

                                    if (!isset($attributes[$mtDateAttr])) {
                                        $transUnit->addAttribute($mtDateAttr, $mtDate);
                                    } else {
                                        $transUnit->attributes()->{$mtDateAttr} = $mtDate;
                                    }

                                    if ($this->containsHtml($translated)) {
                                        //$targetNode = null;
                                        $targetNode->addCData($translated);
                                    } else {
                                        $transUnit->target = $translated;
                                    }

                                    $i++;
                                    $xlfStrTranslated++;
                                    $strTranslated++;
                                } else {
                                    $this->mtFailCount++;
                                }
                            }
                        }

                        if ($xlfStrTranslated === 0) {
                            $this->logger->warning('No strings translated');
                        } else {
                            $this->logger->info('Strings translated: ' . $xlfStrTranslated);
                        }

                        $this->logger->info('');
                    }

                    if (count($new) > 0) {
                        if ($this->outputTranslated) {
                            foreach ($new as $key => $row) {
                                $this->logger->info('[#' . ($key+1) . '] Source: ' . $row['source']);
                                $this->logger->info('[#' . ($key+1) . '] Translated: ' . $row['target']);
                            }
                            $this->logger->info('');
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

        $this->logger->info('');
        $this->logger->info('Summary');
        $this->logger->info('-----------------------------------------');
        $this->logger->info('Total locales translated: ' . count($localesTranslated));
        $this->logger->info('Total strings requested: ' . $strRequested);
        $this->logger->info('Total strings translated: ' . $strTranslated);
        $this->logger->info('Catalogues translated: ' . (count($cataloguesTranslated) === 0 ? '0' : implode(', ', $cataloguesTranslated)));

        if ($cataloguesSkipped) {
            $this->logger->info('Catalogues skipped: ' . implode(', ', $cataloguesSkipped));
        }

        if ($localesSkipped) {
            $this->logger->info('Locales skipped: ' .implode(', ', $localesSkipped));
        }

        $this->logger->info('xlf updated: ' . $filesWritten);
        $this->logger->info('');
        $this->logger->info('Done');

        return $this;
    }

    /**
     * Determines whether catalogue should be parsed
     *
     * @param string $catalogue
     *
     * @return bool
     */
    private function shouldParseCatalogue(string $catalogue): bool
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
    private function shouldParseLocale(string $locale): bool
    {
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
     * @throws Exception
     */
    private function write(SimpleXMLExtended $xmlData, string $file): void
    {
        $fwh = fopen($file, 'w');

        if (!$fwh) {
            throw new Exception('Failed to create file resource.');
        }

        $data = $xmlData->asXML();

        if (!$data) {
            throw new Exception('Failed to generate file data from XML.');
        }

        fwrite($fwh, $data);
        fclose($fwh);
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
