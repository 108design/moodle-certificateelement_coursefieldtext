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

/** Read current course layout without creating issues or reading recipient data. */
final class course_preview {
    public const PATH = '/admin/tool/certificate/element/coursefieldtext/preview.php';

    /** Only saved, accessible Course certificate activities in this course are choices. */
    public static function activities(\stdClass $course): array {
        if ($course->id <= SITEID || !has_capability('moodle/course:update', \context_course::instance($course->id)) ||
                !get_config('mod_coursecertificate', 'version')) {
            return [];
        }
        $result = [];
        foreach (get_fast_modinfo($course)->get_instances_of('coursecertificate') as $cm) {
            if ($cm->uservisible) {
                $result[$cm->id] = $cm;
            }
        }
        return $result;
    }

    /** Resolve the saved activity, never a template supplied by the request. */
    public static function template(\stdClass $course, int $cmid): \tool_certificate\template {
        global $DB;
        require_capability('moodle/course:update', \context_course::instance($course->id));
        $activities = self::activities($course);
        if (!isset($activities[$cmid])) {
            throw new \moodle_exception('invalidpreviewactivity', 'certificateelement_coursefieldtext');
        }
        $activity = $DB->get_record('coursecertificate',
            ['id' => $activities[$cmid]->instance, 'course' => $course->id], 'id,template', MUST_EXIST);
        return \tool_certificate\template::instance((int)$activity->template);
    }

    public static function url(int $courseid, int $cmid = 0): \moodle_url {
        return new \moodle_url(self::PATH, ['courseid' => $courseid] + ($cmid ? ['cmid' => $cmid] : []));
    }

    public static function navigation($navigation, \stdClass $course, \context $context): void {
        if ($context->contextlevel !== CONTEXT_COURSE || !self::activities($course)) {
            return;
        }
        $node = $navigation->add(get_string('coursepreview', 'certificateelement_coursefieldtext'),
            self::url((int)$course->id), \navigation_node::TYPE_SETTING, null, 'coursefieldtext_preview');
        $node->set_force_into_more_menu(true);
    }

    /** A complete HTML-only layout using the activity's current saved template. */
    public static function render(\stdClass $course, int $cmid): string {
        $template = self::template($course, $cmid);
        $html = '';
        foreach ($template->get_pages() as $page) {
            $record = $page->to_record();
            $content = '';
            foreach ($page->get_elements() as $element) {
                $content .= self::render_element($element, $course);
            }
            $index = array_search($page->get_id(), array_keys($template->get_pages())) + 1;
            $label = get_string('page', 'tool_certificate', $index);
            $html .= \html_writer::tag('section',
                \html_writer::tag('h3', $label, ['class' => 'h5']) .
                \html_writer::div(\html_writer::div($content, 'coursefieldtext-preview-page', [
                    'style' => 'width:' . (float)$record->width . 'mm;height:' . (float)$record->height . 'mm;',
                    'aria-label' => $label,
                ]), 'coursefieldtext-preview-scroll', ['tabindex' => '0', 'role' => 'region', 'aria-label' => $label]),
                ['class' => 'mb-4']);
        }
        return $html;
    }

    /** Deliberate allowlist: never call arbitrary recipient-dependent renderers. */
    private static function render_element(\tool_certificate\element $element, \stdClass $course): string {
        $type = $element->get_element();
        $data = json_decode($element->get_data() ?: '{}', true);
        if ($type === 'coursefieldtext' && $element instanceof element) {
            $html = $element->render_course_html((int)$course->id);
        } else if ($type === 'program') {
            $html = \tool_certificate\element_helper::render_html_content($element,
                self::course_value($course, is_array($data) ? (string)($data['display'] ?? '') : ''));
        } else if ($type === 'image' && $element instanceof \certificateelement_image\element) {
            $background = $element->is_background();
            $page = $element->get_page()->to_record();
            $html = self::image($element->get_file(), $background ? (float)$page->width : (float)($data['width'] ?? 0),
                $background ? (float)$page->height : (float)($data['height'] ?? 0), $element->get_display_name());
        } else if ($type === 'coursefieldimg' && class_exists('\certificateelement_coursefieldimg\image_source')) {
            // The existing resolver checks exact field/file areas, defaults, size and decodability.
            $file = \certificateelement_coursefieldimg\image_source::resolve((int)$course->id, (int)($data['fieldid'] ?? 0));
            $html = self::image($file, (float)($data['width'] ?? 0), (float)($data['height'] ?? 0),
                $element->get_display_name());
        } else if ($type === 'text') {
            $html = \tool_certificate\element_helper::render_html_content($element,
                format_text($element->get_data(), FORMAT_HTML, ['context' => \context_course::instance($course->id),
                    'filter' => false]));
        } else if ($type === 'border') {
            $html = $element->render_html();
        } else if (in_array($type, ['userfield', 'userpicture', 'date', 'code'], true)) {
            $key = ['userfield' => 'previewperson', 'userpicture' => 'previewperson', 'date' => 'previewdate',
                'code' => 'previewcode'][$type];
            $html = \tool_certificate\element_helper::render_html_content($element,
                s(get_string($key, 'certificateelement_coursefieldtext')));
        } else {
            $html = \tool_certificate\element_helper::render_html_content($element,
                \html_writer::span(s(get_string('previewunsupported', 'certificateelement_coursefieldtext',
                    $element->get_display_name())), 'coursefieldtext-preview-placeholder'));
        }
        $html = preview_html::dimensions($html);
        $page = $element->get_page()->to_record();
        $x = (float)$element->get_posx();
        $width = (float)$element->get_width();
        $image = in_array($type, ['image', 'coursefieldimg'], true);
        if (!$image) {
            if ((int)$element->get_refpoint() === 1) {
                $width = $width ?: min($x - $page->leftmargin, $page->width - $page->rightmargin - $x) * 2;
                $x -= $width / 2;
            } else if ((int)$element->get_refpoint() === 2) {
                $width = $width ?: $x - $page->leftmargin;
                $x -= $width;
            } else {
                $width = $width ?: max(0, $page->width - $page->rightmargin - $x);
            }
        }
        $style = 'left:' . $x . 'mm;top:' . (float)$element->get_posy() . 'mm;font-size:' .
            max(1, (float)$element->get_fontsize()) . 'pt;';
        if (!$image) {
            $style .= 'width:' . max(0, $width) . 'mm;';
        }
        return \html_writer::div($html, 'coursefieldtext-preview-element', [
            'style' => $style, 'data-preview-element' => $element->get_id(),
        ]);
    }

    /** Course data only; no grade, completion, enrolment, profile or issue queries. */
    private static function course_value(\stdClass $course, string $key): string {
        if ($key === 'coursefullname' || $key === 'courseshortname') {
            return s($key === 'coursefullname' ? $course->fullname : $course->shortname);
        }
        if ($key === 'courseid') {
            return s((string)$course->id);
        }
        if ($key === 'courseurl') {
            return s((new \moodle_url('/course/view.php', ['id' => $course->id]))->out(false));
        }
        if (str_starts_with($key, 'coursecustomfield_')) {
            $shortname = substr($key, strlen('coursecustomfield_'));
            foreach (\core_course\customfield\course_handler::create()->get_instance_data($course->id, true) as $value) {
                $field = $value->get_field();
                if ($field->get('shortname') !== $shortname) {
                    continue;
                }
                if (!in_array($field->get('type'), ['text', 'textarea', 'multiline', 'checkbox', 'select', 'date'], true)) {
                    return s(get_string('previewdynamic', 'certificateelement_coursefieldtext'));
                }
                if ($field->get('type') === 'textarea') {
                    return format_text((string)$value->get_value(), FORMAT_HTML,
                        ['context' => \context_course::instance($course->id), 'filter' => false]);
                }
                $export = (string)$value->export_value();
                if ($field->get('type') === 'multiline') {
                    return \customfield_multiline\text::export_to_html($export);
                }
                return s($export);
            }
            return '';
        }
        return s(get_string('previewdynamic', 'certificateelement_coursefieldtext'));
    }

    /** Inline only recognised raster bytes; native pluginfile permissions remain unchanged. */
    private static function image(?\stored_file $file, float $width, float $height, string $alt): string {
        if (!$file || $file->is_external_file() || $file->get_filesize() > 10 * 1024 * 1024 || !$info = $file->get_imageinfo()) {
            return \html_writer::span(s(get_string('previewmissingimage', 'certificateelement_coursefieldtext')));
        }
        if (!in_array($info['mimetype'], ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true) ||
                $info['width'] * $info['height'] > 16000000) {
            return \html_writer::span(s(get_string('previewmissingimage', 'certificateelement_coursefieldtext')));
        }
        $url = 'data:' . $info['mimetype'] . ';base64,' . base64_encode($file->get_content());
        return \tool_certificate\element_helper::render_image_html($url, $info, $width, $height, $alt);
    }
}
