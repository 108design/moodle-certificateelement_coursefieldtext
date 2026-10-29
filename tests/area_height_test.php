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

/** Area dimensions accept locale decimals and reject invalid/unsafe input. */
final class area_height_test extends \PHPUnit\Framework\TestCase {
    public function test_normalisation(): void {
        foreach ([null, '', '  '] as $input) { $this->assertNull(area_height::normalise($input)); }
        foreach ([40, 40.5, '40.5', '40,5', '0.1'] as $input) {
            $this->assertGreaterThan(0, area_height::normalise($input));
        }
        foreach ([0, -1, '0', 'bad', '1e2', '40px', INF, NAN, true, [], 10001] as $input) {
            try { area_height::normalise($input); $this->fail('Accepted invalid area'); }
            catch (\InvalidArgumentException $exception) { $this->assertSame('Invalid area height', $exception->getMessage()); }
        }
    }
}
