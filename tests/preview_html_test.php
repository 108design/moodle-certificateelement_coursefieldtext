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

/** Pure dimension conversion; no database fixtures. */
final class preview_html_test extends \PHPUnit\Framework\TestCase {
    public function test_dimensions_fonts_unicode_and_media_are_bounded(): void {
        $html = preview_html::dimensions('<div data-width="40.5" data-height="20" data-posx="-1" data-posy="30" ' .
            'style="font-family: freesans;">Ü &amp; Ö<img src="https://example.invalid/private.png" srcset="bad">' .
            '<img data-width="10" src="data:image/png;base64,AAAA">' .
            '<span style="background:url(https://example.invalid/asset)">Text</span>' .
            '<iframe src="https://example.invalid/frame"></iframe></div>');
        foreach (['width:40.5mm', 'height:20mm', 'left:-1mm', 'top:30mm', 'Arial, sans-serif', 'Ü &amp; Ö',
            'data:image/png;base64,AAAA', 'width:10mm'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        $this->assertStringNotContainsString('https://', $html);
        $this->assertStringNotContainsString('srcset', $html);
        $this->assertStringNotContainsString('<?xml', $html);
        $this->assertStringNotContainsString('iframe', $html);
        $this->assertStringNotContainsString('url(', $html);
    }
}
