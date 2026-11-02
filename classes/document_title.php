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

/** Plain PDF metadata derived from the already decoded and sanitised snapshot HTML. */
final class document_title {
    public static function from_html(string $html): string {
        // Block boundaries must separate words; inline emphasis must not introduce spaces.
        $html = preg_replace('~</?(?:br|div|p|ul|ol|li)\b[^>]*>~i', ' ', $html);
        // Strip real formatting before decoding, preserving literal tags in plain-text values.
        $text = html_entity_decode(strip_tags(str_replace("\0", ' ', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/[\s\p{Z}\p{Cc}]+/u', ' ', $text));
    }
}
