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

/** Template-controlled size for the sanitised semantic small tag. */
final class small_text {
    public static function normalise($value): float {
        if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
            return 60.0;
        }
        if (is_string($value)) {
            $value = trim($value);
            if (!preg_match('/^[0-9]+(?:[.,][0-9]+)?$/D', $value)) {
                throw new \InvalidArgumentException('Invalid small text size');
            }
            $value = (float)str_replace(',', '.', $value);
        }
        if ((!is_int($value) && !is_float($value)) || !is_finite((float)$value) || $value < 10 || $value > 100) {
            throw new \InvalidArgumentException('Invalid small text size');
        }
        return (float)$value;
    }

    public static function render(string $html, float $percentage, ?float $fontsize = null): string {
        $percentage = self::normalise($percentage);
        // The codec removes nested small tags and all user attributes before this trusted conversion.
        $size = $fontsize === null ? $percentage : $fontsize * $percentage / 100;
        $size = rtrim(rtrim(number_format($size, 6, '.', ''), '0'), '.');
        return str_replace(['<small>', '</small>'],
            ['<span style="font-size: ' . $size . ($fontsize === null ? '%;' : 'pt;') . '">', '</span>'], $html);
    }
}
