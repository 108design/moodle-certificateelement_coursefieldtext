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
require_once(__DIR__ . '/../../../../../config.php');

use certificateelement_coursefieldtext\course_preview;
use certificateelement_coursefieldtext\help;

$courseid = required_param('courseid', PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('moodle/course:update', $context);
$activities = course_preview::activities($course);
if (!$cmid && count($activities) === 1) {
    $cmid = (int)array_key_first($activities);
}
// Validate before emitting the header, including requests with a foreign/non-certificate CM.
$template = $cmid ? course_preview::template($course, $cmid) : null;
$PAGE->set_url(course_preview::url($courseid, $cmid));
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_cacheable(false);
$title = get_string('coursepreview', 'certificateelement_coursefieldtext');
$PAGE->set_title($title);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add($title, course_preview::url($courseid));
$PAGE->requires->css(new moodle_url('/admin/tool/certificate/element/coursefieldtext/styles/preview.css',
    ['rev' => get_config('certificateelement_coursefieldtext', 'version')]));

$PAGE->requires->js_call_amd('certificateelement_coursefieldtext/preview_fit', 'init');

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo html_writer::div(get_string('previewsummary', 'certificateelement_coursefieldtext') . ' ' . help::icon('preview'),
    'mb-4');
if (!$activities) {
    echo html_writer::div(get_string('previewnoactivities', 'certificateelement_coursefieldtext'),
        'alert alert-info', ['role' => 'status']);
} else {
    $choices = [0 => get_string('choosepreviewactivity', 'certificateelement_coursefieldtext')];
    foreach ($activities as $id => $activity) {
        $choices[$id] = format_string($activity->name);
    }
    echo html_writer::start_tag('form', ['method' => 'get', 'action' => (new moodle_url(course_preview::PATH))->out(false),
        'class' => 'mb-4']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'courseid', 'value' => $courseid]);
    echo html_writer::start_div('fitem row mx-0');
    echo html_writer::div(html_writer::tag('label', get_string('previewactivity', 'certificateelement_coursefieldtext'),
        ['for' => 'preview-cmid', 'class' => 'mr-2 mb-0']) .
        $OUTPUT->help_icon('previewactivity', 'certificateelement_coursefieldtext'), 'col-md-3 col-form-label d-flex justify-content-between');
    echo html_writer::div(html_writer::select($choices, 'cmid', $cmid, false,
        ['id' => 'preview-cmid', 'class' => 'custom-select mr-2 mb-2']) .
        html_writer::tag('button', get_string('showpreview', 'certificateelement_coursefieldtext'),
            ['type' => 'submit', 'class' => 'btn btn-primary mb-2']), 'col-md-9 d-flex flex-wrap align-items-start');
    echo html_writer::end_div();
    echo html_writer::end_tag('form');
    if ($template) {
        echo $OUTPUT->heading($template->get_formatted_name(), 3);
        echo html_writer::div(html_writer::link(course_preview::url($courseid, $cmid),
            get_string('refreshpreview', 'certificateelement_coursefieldtext'), ['class' => 'btn btn-secondary mr-2 mb-2']) .
            html_writer::link(new moodle_url('/course/edit.php', ['id' => $courseid]),
                get_string('editpreviewcourse', 'certificateelement_coursefieldtext'), ['class' => 'btn btn-secondary mb-2']),
            'mb-4');
        echo course_preview::render($course, $cmid);
    }
}
echo $OUTPUT->footer();
