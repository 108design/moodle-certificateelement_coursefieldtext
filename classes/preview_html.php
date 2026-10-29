<?php
/**
 * This file is part of Moodle - https://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle. If not, see <https://www.gnu.org/licenses/>.
 *
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
/**
 * @package certificateelement_coursefieldtext
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace certificateelement_coursefieldtext;
defined('MOODLE_INTERNAL') || die();

/** Adapt native designer HTML to a static page with physical millimetre dimensions. */
final class preview_html {
    public static function dimensions(string $html): string {
        $previous = libxml_use_internal_errors(true);
        try {
            $doc = new \DOMDocument();
            $doc->loadHTML('<?xml encoding="UTF-8"><div id="coursefieldtext-root">' . $html . '</div>',
                LIBXML_NONET | LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
            $xpath = new \DOMXPath($doc);
            foreach ($xpath->query('//*[@data-width or @data-height or @data-posx or @data-posy]') as $node) {
                $style = $node->getAttribute('style');
                foreach (['width' => 'width', 'height' => 'height', 'posx' => 'left', 'posy' => 'top'] as $key => $property) {
                    $value = $node->getAttribute('data-' . $key);
                    if ($node->hasAttribute('data-' . $key) && is_numeric($value) && is_finite((float)$value)) {
                        $style .= ';' . $property . ':' . (float)$value . 'mm';
                    }
                }
                $node->setAttribute('style', $style);
            }
            foreach ($xpath->query('//*[@style]') as $node) {
                $style = preg_replace_callback('/font-family:\s*([a-z0-9_-]+)/i', static function($match) {
                    $fallback = ['freesans' => 'Arial, sans-serif', 'helvetica' => 'Arial, sans-serif',
                        'freeserif' => 'Times New Roman, serif', 'times' => 'Times New Roman, serif',
                        'freemono' => 'Courier New, monospace', 'courier' => 'Courier New, monospace'];
                    return 'font-family: ' . ($fallback[strtolower($match[1])] ?? $match[1] . ', sans-serif');
                }, $node->getAttribute('style'));
                if (preg_match('/url\s*\(|@import/i', $style)) {
                    $style = ''; // No implicit media or stylesheet requests from rich-text CSS.
                }
                $node->setAttribute('style', $style);
            }
            // Only the adapter's inline raster images are displayed; do not request private or external media URLs.
            foreach ($xpath->query('//img') as $node) {
                $node->removeAttribute('srcset');
                if (!preg_match('~\Adata:image/(png|jpeg|gif|webp);base64,[A-Za-z0-9+/=]+\z~D', $node->getAttribute('src'))) {
                    $label = $doc->createTextNode(get_string('previewmissingimage', 'certificateelement_coursefieldtext'));
                    $node->parentNode->replaceChild($label, $node);
                }
            }
            foreach ($xpath->query('//iframe | //object | //embed | //audio | //video | //source | //link | //style | //script') as $node) {
                $node->parentNode->removeChild($node);
            }
            $result = '';
            foreach ($doc->getElementById('coursefieldtext-root')->childNodes as $node) {
                $result .= $doc->saveHTML($node);
            }
            return $result;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
