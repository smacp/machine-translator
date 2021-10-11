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

namespace smacp\MachineTranslator\lib;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;

/**
 * Class SimpleXmlExtended
 *
 * @package smacp\MachineTranslator\lib
 */
class SimpleXmlExtended extends SimpleXMLElement
{
    /**
     * Writes a string to a node with 'CDATA' tags.
     *
     * @param string $str
     *
     * @return SimpleXmlExtended
     */
    public function addCData(string $str): SimpleXmlExtended
    {
        $node = dom_import_simplexml($this);

        if ($node instanceof DOMElement) {
            $ownerDocument = $node->ownerDocument;
            if ($ownerDocument instanceof DOMDocument) {
                $node->appendChild($ownerDocument->createCDATASection($str));
            }
        }

        return $this;
    }
}