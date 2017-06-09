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

namespace SMACP\MachineTranslator\Classes;

use SMACP\MachineTranslator\Classes\SimpleXmlExtended;

/**
 * Translates xlf files
 *
 * @author Stuart MacPherson
 */
class XlfTranslator
{
    /** @var MachineTranslator */
    protected $translator;
    
    /** @var string */
    protected $dir;
    
    /** @var array */
    protected $parsed;
    
    /** @var boolean */
    protected $commit = true;
    
    /** @var array */
    protected $locales = [];
    
    /** @var array */
    protected $excludeLocales = ['en_GB', 'en_US'];
    
    /** @var string */
    protected $sourceLocale = 'en_GB';
    
    /** @var array */
    protected $catalogues = [];
    
    /** @var boolean */
    protected $newOnly = false;
    
    /** @var integer */
    protected $mtFailCount = 0;
    
    /** @var integer */
    protected $maxMtFailCount = 10;
    
    /** @var array */
    protected $attributes = [
        'mt'      => 'machinetranslated',
        'mt_date' => 'datemachinetranslated'
    ];
    
    /** @var boolean */
    protected $memory = true;
    
    /** @var boolean */
    protected $output = true;
    
    /** @var boolean */
    protected $outputTranslated = false;
    
    /**
     * Get translator
     *
     * @return MachineTranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }
    
    /**
     * Set translator
     *
     * @param MachineTranslator $translator
     * @return XlfTranslator
     */
    public function setTranslator(MachineTranslator $translator)
    {
        $this->translator = $translator;
        
        return $this;
    }
    
    /**
     * Get locales
     *
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }
    
    /**
     * Set locales
     *
     * @param array $locales
     * @return XlfTranslator
     */
    public function setLocales(array $locales)
    {
        $this->locales = $locales;
        
        return $this;
    }
    
    /**
     * Get excluded locales
     *
     * @return array
     */
    public function getExcludedLocales()
    {
        return $this->excludedLocales;
    }
    
    /**
     * Set excludedLocales
     *
     * @param array $locales
     * @return XlfTranslator
     */
    public function setExcludedLocales(array $locales)
    {
        $this->excludedLocales = $locales;
        
        return $this;
    }
    
    /**
     * Get dir
     *
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }
    
    /**
     * Set dir
     *
     * @param string $dir
     * @return XlfTranslator
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
        
        return $this;
    }
    
    /**
     * Get sourceLocale
     *
     * @return string
     */
    public function getSourceLocale()
    {
        return $this->sourceLocale;
    }
    
    /**
     * Set sourceLocale
     *
     * @param string $locale
     * @return XlfTranslator
     */
    public function setSourceLocale($locale)
    {
        $this->sourceLocale = $locale;
        
        return $this;
    }
    
    /**
     * Get catalogues
     *
     * @return string
     */
    public function getCatalogues()
    {
        return $this->catalogues;
    }
    
    /**
     * Set catalogues
     *
     * @param array $catalogues
     * @return XlfTranslator
     */
    public function setCatalogues(array $catalogues)
    {
        $this->catalogues = $catalogues;
        
        return $this;
    }
    
    /**
     * Get newOnly
     *
     * @return boolean
     */
    public function getNewOnly()
    {
        return $this->newOnly;
    }
    
    /**
     * Set newOnly
     *
     * @param boolean $newOnly
     * @return XlfTranslator
     */
    public function setNewOnly($newOnly)
    {
        $this->newOnly = $newOnly;
        
        return $this;
    }
    
    /**
     * Get commit
     *
     * @return boolean
     */
    public function getCommit()
    {
        return $this->commit;
    }
    
    /**
     * Set commit
     *
     * @param boolean $commit
     * @return XlfTranslator
     */
    public function setCommit($commit)
    {
        $this->commit = $commit;
        
        return $this;
    }
    
    /**
     * Get output
     *
     * @return boolean
     */
    public function getOutput()
    {
        return $this->output;
    }
    
    /**
     * Set output
     *
     * @param boolean $output
     * @return XlfTranslator
     */
    public function setOutput($output)
    {
        $this->output = $output;
        
        return $this;
    }
    
    /**
     * Get outputTanslated
     *
     * @return boolean
     */
    public function getOutputTranslated()
    {
        return $this->outputTranslated;
    }
    
    /**
     * Set outputTranslated
     *
     * @param boolean $outputTranslated
     * @return XlfTranslator
     */
    public function setOutputTranslated($outputTranslated)
    {
        $this->outputTranslated = $outputTranslated;
        
        return $this;
    }
    
    /**
     * Get memory
     *
     * @return boolean
     */
    public function getMemory()
    {
        return $this->memory;
    }
    
    /**
     * Set memory
     *
     * @param boolean $memory
     * @return XlfTranslator
     */
    public function setMemory($memory)
    {
        $this->memory = $memory;
        
        return $this;
    }
    
    /**
     * Machine translates
     *
     * @return XlfTranslator
     */
    public function translate()
    {
        $this->parsed = [];
        $this->mtFailCount = 0;
        
        $provider = $this->translator->getProvider();
        $catalogues = $this->getCatalogues();
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
                    
                    if (count($parts) !== 3) {
                        throw new Exception('Cannot parse file. Expected file in format catalogue.locale.xlf.');
                    }
                    
                    if (strpos($filePath, '.xlf') < 0) {
                        throw new Exception('Not a valid xlf file: ' . $filename);
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
     * @return boolean
     */
    protected function shouldParseCatalogue($catalogue)
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
     * @return boolean
     */
    protected function shouldParseLocale($locale)
    {
        if (!$this->translator->normaliseLanguageCode($locale)) {
            return false;
        }
        
        if ($this->locales && !in_array($locale, $this->locales)) {
            return false;
        }
        
        if ($this->excludeLocales && in_array($locale, $this->excludeLocales)) {
            return false;
        }
                    
        return $this->sourceLocale !== $locale;
    }
    
    /**
     * Writes to file
     *
     * @param SimpleXMLExtended $xmlData
     * @param string $file
     * @return XlfTranslator
     */
    protected function write(SimpleXMLExtended $xmlData, $file)
    {
        $xml = $xmlData->asXML();
        $fwh = fopen($file, 'w');
        fwrite($fwh, $xml);
        
        return $this;
    }
}
