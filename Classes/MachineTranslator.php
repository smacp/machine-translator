<?php

namespace SMACP\MachineTranslator\Classes;

/**
 * MachineTranslator interface
 *
 * @author Stuart MacPherson
 */
interface MachineTranslator
{
    /**
     * Translates a word or phrase
     *
     * @param string $word
     * @param string $from
     * @param string $to
     */
    public function translate($word, $from, $to);
    
    /**
     * Gets api provider name
     *
     * @return string
     */
    public function getProvider();
}