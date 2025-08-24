<?php
namespace Einvoicing\Presets;

use Einvoicing\Invoice;
use Einvoicing\Writers\AbstractWriter;
use UXML\UXML;

abstract class AbstractPreset {
    /**
     * Get specification identifier
     * @return string Specification identifier
     */
    abstract public function getSpecification(): string;


    /**
     * Get additional validation rules
     * @return array<string,callable> Map of rules
     */
    public function getRules(): array {
        return [];
    }


    /**
     * Setup invoice
     * @param Invoice $invoice Invoice instance
     */
    public function setupInvoice(Invoice $invoice) {
        $invoice->setRoundingMatrix(['' => 2]);
    }

    /**
     * finalizeXml
     * @param UXML $xml Xml root Element
     * @param Invoice $invoice Invoice instance 
     * @param AbstractWriter $writer Writer instance
     */
    public function finalizeXml(UXML $xml, Invoice $invoice, AbstractWriter $writer): void {
        // Override in country-specific presets for XML modifications
    }
}
