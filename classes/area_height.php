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

/** Validated element-local area height in millimetres; null retains legacy anchoring. */
final class area_height {
    public static function normalise($value): ?float {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
            if (!preg_match('/^[0-9]+(?:[.,][0-9]+)?$/D', $value)) {
                throw new \InvalidArgumentException('Invalid area height');
            }
            $value = (float)str_replace(',', '.', $value);
        }
        if ((!is_int($value) && !is_float($value)) || !is_finite((float)$value) || $value <= 0 || $value > 10000) {
            throw new \InvalidArgumentException('Invalid area height');
        }
        return (float)$value;
    }
}
