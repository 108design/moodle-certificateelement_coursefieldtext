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
defined('MOODLE_INTERNAL') || die();

/** Register native long-text issue mirrors for this element's course-field type only. */
function certificateelement_coursefieldtext_tool_certificate_fields() {
    \certificateelement_coursefieldtext\source::register_fields();
}

/** Context help on registered editor pages only. */
function certificateelement_coursefieldtext_render_navbar_output(\renderer_base $renderer): string {
    return \certificateelement_coursefieldtext\help::navbar($renderer);
}

/** Read-only course preview supplied by this element, independently of the activity plugin. */
function certificateelement_coursefieldtext_extend_navigation_course($navigation, $course, $context) {
    \certificateelement_coursefieldtext\course_preview::navigation($navigation, $course, $context);
}
