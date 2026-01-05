<?php

namespace App\Libraries;

/**
 * DocxParser Library
 *
 * Extracts text from DOCX files using native ZipArchive to ensure functionality
 * even without the phpoffice/phpword dependency installed in restricted environments.
 *
 * Note: While we added phpoffice/phpword to composer.json, this class provides a
 * robust fallback or lightweight alternative.
 */
class DocxParser
{
    /**
     * Read the DOCX file and return its text content.
     *
     * @param string $filePath The path to the DOCX file.
     * @return string The extracted text.
     */
    public function read($filePath)
    {
        $content = '';
        $zip = new \ZipArchive();

        if ($zip->open($filePath) === true) {
            // Check for document.xml which holds the main text
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $content .= $this->parseXml($data);
            }
            $zip->close();
        }

        return $content;
    }

    /**
     * Parse the XML content and strip tags to get raw text.
     *
     * @param string $xml
     * @return string
     */
    private function parseXml($xml)
    {
        // Remove XML tags but try to keep some structure
        // Simple strip_tags might lose paragraph breaks, so let's preserve them slightly
        // DOCX uses <w:p> for paragraphs

        $xml = str_replace('</w:p>', "\n", $xml);
        return strip_tags($xml);
    }
}
