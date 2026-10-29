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
 * File documentation for tests/line_height_test.php.
 *
 * @package   certificateelement_coursefieldtext
 * @copyright 2026 Andreas Giesen
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
namespace certificateelement_coursefieldtext;
defined('MOODLE_INTERNAL') || die();

/** Line height parsing without database fixtures. */
final class line_height_test extends \PHPUnit\Framework\TestCase {
    public function test_standard_and_localised_factors(): void {
        foreach ([null, '', '  '] as $value) {
            $this->assertNull(line_height::normalise($value));
        }
        foreach (['1,25' => 1.25, ' 1.0 ' => 1.0, '0.5' => 0.5, '3' => 3.0] as $value => $expected) {
            $this->assertSame($expected, line_height::normalise($value));
        }
        $this->assertSame(1.5, line_height::normalise(1.5));
    }

    public function test_invalid_values_cannot_reach_pdf_or_css(): void {
        foreach ([0, -1, 0.49, 3.01, INF, NAN, true, [], '1; color:red', '1e2', '1,2,3'] as $value) {
            try {
                line_height::normalise($value);
                $this->fail('Invalid value accepted');
            } catch (\InvalidArgumentException $e) {
                $this->assertSame('Invalid line height', $e->getMessage());
            }
        }
    }
}
