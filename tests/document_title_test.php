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

final class document_title_test extends \PHPUnit\Framework\TestCase {
    public function test_decoded_metadata_keeps_words_unicode_and_literal_plain_text(): void {
        $cases = [
            '<strong>Caf&eacute;</strong><br>R&amp;D' => 'Café R&D',
            '<div>First</div><div>Second</div><ul><li>Third</li><li>Fourth</li></ul>' => 'First Second Third Fourth',
            'Learn<em>ing</em> &quot;A&quot; &#x1F33F;' => 'Learning "A" 🌿',
            '&lt;b&gt;Literal&lt;/b&gt; &amp;amp;' => '<b>Literal</b> &amp;',
            " A\r\n B\t C\0D&nbsp;&#10; E " => 'A B C D E',
            '<p>&nbsp;</p><br>' => '',
            '' => '',
            '0' => '0',
        ];
        foreach ($cases as $html => $expected) {
            $this->assertSame($expected, document_title::from_html((string)$html));
        }
    }
}
