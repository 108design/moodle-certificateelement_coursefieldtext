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

final class small_text_test extends \PHPUnit\Framework\TestCase {
    public function test_percentage_validation_and_pdf_html_size_conversion(): void {
        foreach ([null, '', ' ', 60, '60'] as $value) {
            $this->assertSame(60.0, small_text::normalise($value));
        }
        $this->assertSame(62.5, small_text::normalise('62,5'));
        foreach ([0, 9.9, 100.1, '60%', 'NaN', INF, true, [], '1; color:red'] as $invalid) {
            try {
                small_text::normalise($invalid);
                $this->fail('Expected invalid size');
            } catch (\InvalidArgumentException $e) {
                $this->assertSame('Invalid small text size', $e->getMessage());
            }
        }
        $html = '<small><b>Advanced</b></small><br>Certificate';
        $this->assertSame('<span style="font-size: 27pt;"><b>Advanced</b></span><br>Certificate',
            small_text::render($html, 60, 45));
        $this->assertSame('<span style="font-size: 60%;"><b>Advanced</b></span><br>Certificate',
            small_text::render($html, 60));
        $literal = '&lt;small&gt;Literal&lt;/small&gt;<br>Normal';
        $this->assertSame($literal, small_text::render($literal, 60, 45));
        $this->assertSame('Certificate', small_text::render('Certificate', 60, 45));
    }
}
