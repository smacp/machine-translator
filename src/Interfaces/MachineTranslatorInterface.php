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

namespace smacp\MachineTranslator\Interfaces;

/**
 * Interface MachineTranslator interface
 *
 * @author Stuart MacPherson
 *
 * @package smacp\MachineTranslator\Interfaces
 */
interface MachineTranslatorInterface
{
    /**
     * Translates a word or phrase
     *
     * @param string $word      The word to translate
     * @param string $from      The locale to translate from
     * @param string $to        The locale to translate to
     * @param mixed[] $options  Optional array of options
     *
     * @return string
     */
    public function translate(string $word, string $from, string $to, array $options = []): string;

    /**
     * Gets api provider name
     *
     * @return string
     */
    public function getProvider(): string;
}