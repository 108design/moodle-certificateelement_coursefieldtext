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

/** Bounded adapter between multiline course values and parent-owned issuance data. */
final class source {
    public static function fields(): array {
        return array_filter(\core_course\customfield\course_handler::create()->get_fields(),
            static function($field) { return $field->get('type') === 'multiline'; });
    }

    /** Only the generated mirrors of multiline course fields need native long-text storage. */
    public static function register_fields(): void {
        $handler = \tool_certificate\customfield\issue_handler::create();
        foreach (self::fields() as $field) {
            $mirror = $handler->ensure_field_exists('coursecustomfield_' . $field->get('shortname'), 'textarea',
                get_string('course') . ': ' . $field->get_formatted_name(), true, $field->get_formatted_name());
            if ($mirror && $mirror->get('type') === 'text') {
                // mod_coursecertificate may have registered its short-text fallback first.
                // Native value already holds the exported text; preserve values, ID and all configuration.
                $mirror->set('type', 'textarea');
                $handler->save_field_configuration($mirror, (object)[]);
            }
        }
    }

    public static function preview(string $shortname, int $courseid): string {
        $value = self::preview_value($shortname, $courseid);
        return $value ? (string)$value->get_value() : '';
    }

    public static function preview_html(string $shortname, int $courseid): string {
        $value = self::preview_value($shortname, $courseid);
        return $value ? \customfield_multiline\text::export_to_html((string)$value->export_value()) : '';
    }

    public static function preview_alignment(string $shortname, int $courseid): string {
        $value = self::preview_value($shortname, $courseid);
        return $value ? \customfield_multiline\text::export_alignment((string)$value->export_value()) : '';
    }

    public static function preview_vertical_anchor(string $shortname, int $courseid): string {
        $value = self::preview_value($shortname, $courseid);
        return $value ? \customfield_multiline\text::export_vertical_anchor((string)$value->export_value()) : '';
    }

    private static function preview_value(string $shortname, int $courseid): ?\core_customfield\data_controller {
        $context = $courseid > SITEID ? \context_course::instance($courseid, IGNORE_MISSING) : false;
        if (!$context || !has_capability('moodle/course:update', $context)) {
            return null;
        }
        foreach (self::fields() as $field) {
            if ($field->get('shortname') !== $shortname) {
                continue;
            }
            $values = $field->get_handler()->get_instance_data($courseid, true);
            foreach ($values as $value) {
                if ((int)$value->get('fieldid') === (int)$field->get('id')) {
                    return $value;
                }
            }
        }
        return null;
    }

    /** Never fall back to current course content when historical data is absent. */
    public static function issued(string $shortname, \stdClass $issue): string {
        return \customfield_multiline\text::from_export(self::issued_value($shortname, $issue));
    }

    public static function issued_html(string $shortname, \stdClass $issue): string {
        return \customfield_multiline\text::export_to_html(self::issued_value($shortname, $issue));
    }

    public static function issued_alignment(string $shortname, \stdClass $issue): string {
        return \customfield_multiline\text::export_alignment(self::issued_value($shortname, $issue));
    }

    public static function issued_vertical_anchor(string $shortname, \stdClass $issue): string {
        return \customfield_multiline\text::export_vertical_anchor(self::issued_value($shortname, $issue));
    }

    private static function issued_value(string $shortname, \stdClass $issue): string {
        $data = json_decode($issue->data ?? '{}', true);
        $value = is_array($data) ? ($data['coursecustomfield_' . $shortname] ?? '') : '';
        return is_string($value) ? $value : '';
    }
}
