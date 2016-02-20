<?php

namespace SMACP\MachineTranslator\Classes;

/**
 * This class extends SimpleXMLElement
 */
class SimpleXmlExtended extends \SimpleXMLElement
{

    /**
     * Writes a string to a node with 'CDATA' tags
     *
     * @param string $str
     */
    public function addCData($str)
    {
        $node = dom_import_simplexml($this);
        $oNode = $node->ownerDocument;
        $node->appendChild($oNode->createCDATASection($str));
        
        return $this;
    }
}
