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

/** One multiline course value with base presentation controlled by the template. */
class element extends \tool_certificate\element {
    public static function get_element_type_name() {
        return get_string('elementname', 'certificateelement_coursefieldtext');
    }

    private function settings(): array {
        $data = json_decode($this->get_data() ?: '{}', true);
        $settings = (is_array($data) ? $data : []) +
            ['field' => '', 'previewcourseid' => 0, 'lineheight' => null, 'verticalanchor' => 'top', 'areaheight' => null,
                'smalltextsize' => 60];
        $settings['smalltextsize'] = small_text::normalise($settings['smalltextsize']);
        $settings['verticalanchor'] = in_array($settings['verticalanchor'], ['top', 'middle', 'bottom'], true) ?
            $settings['verticalanchor'] : 'top';
        try {
            $settings['lineheight'] = line_height::normalise($settings['lineheight']);
        } catch (\InvalidArgumentException $e) {
            $settings['lineheight'] = null;
        }
        // A stored invalid area is an error, never silently revert to legacy positioning.
        $settings['areaheight'] = area_height::normalise($settings['areaheight']);
        return $settings;
    }

    public function render_form_elements($mform) {
        $options = ['' => get_string('choosefield', 'certificateelement_coursefieldtext')];
        foreach (source::fields() as $field) {
            $options[$field->get('shortname')] = $field->get_formatted_name() . ' (' . $field->get('shortname') . ')';
        }
        $selected = (string)$this->settings()['field'];
        if ($selected !== '' && !isset($options[$selected])) {
            $options[$selected] = get_string('missingfield', 'certificateelement_coursefieldtext', $selected);
        }
        $mform->addElement('select', 'field', get_string('coursefield', 'certificateelement_coursefieldtext'), $options);
        $mform->addHelpButton('field', 'coursefield', 'certificateelement_coursefieldtext');
        $mform->addRule('field', null, 'required');
        $mform->addElement('course', 'previewcourseid', get_string('previewcourse', 'certificateelement_coursefieldtext'),
            ['requiredcapabilities' => ['moodle/course:update']]);
        $mform->addHelpButton('previewcourseid', 'previewcourse', 'certificateelement_coursefieldtext');
        $mform->addElement('static', 'issuebehaviour', get_string('issuance', 'certificateelement_coursefieldtext'),
            get_string('issuancesummary', 'certificateelement_coursefieldtext') . help::icon('issuance'));
        $mform->addElement('text', 'lineheight', get_string('lineheight', 'certificateelement_coursefieldtext'),
            ['size' => 6, 'placeholder' => get_string('default')]);
        $mform->setType('lineheight', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('lineheight', 'lineheight', 'certificateelement_coursefieldtext');
        $mform->addElement('text', 'smalltextsize', get_string('smalltextsize', 'certificateelement_coursefieldtext'),
            ['size' => 6]);
        $mform->setType('smalltextsize', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('smalltextsize', 'smalltextsize', 'certificateelement_coursefieldtext');
        $mform->addElement('text', 'areaheight', get_string('areaheight', 'certificateelement_coursefieldtext'), ['size' => 6]);
        $mform->setType('areaheight', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('areaheight', 'areaheight', 'certificateelement_coursefieldtext');
        $mform->addElement('select', 'verticalanchor', get_string('verticalanchor', 'certificateelement_coursefieldtext'), [
            'top' => get_string('anchortop', 'certificateelement_coursefieldtext'),
            'middle' => get_string('anchormiddle', 'certificateelement_coursefieldtext'),
            'bottom' => get_string('anchorbottom', 'certificateelement_coursefieldtext'),
        ]);
        $mform->setType('verticalanchor', PARAM_RAW);
        $mform->addHelpButton('verticalanchor', 'verticalanchor', 'certificateelement_coursefieldtext');
        parent::render_form_elements($mform);
    }

    public function validate_form_elements($data, $files) {
        $errors = [];
        $found = false;
        foreach (source::fields() as $field) {
            $found = $found || $field->get('shortname') === ($data['field'] ?? '');
        }
        if (!$found) {
            $errors['field'] = get_string('choosefield', 'certificateelement_coursefieldtext');
        }
        $courseid = (int)($data['previewcourseid'] ?? 0);
        $context = $courseid > SITEID ? \context_course::instance($courseid, IGNORE_MISSING) : false;
        if ($courseid && (!$context || !has_capability('moodle/course:update', $context))) {
            $errors['previewcourseid'] = get_string('nopermissions', 'error', 'moodle/course:update');
        }
        try {
            line_height::normalise($data['lineheight'] ?? null);
        } catch (\InvalidArgumentException $e) {
            $errors['lineheight'] = get_string('invalidlineheight', 'certificateelement_coursefieldtext');
        }
        try {
            small_text::normalise($data['smalltextsize'] ?? $this->settings()['smalltextsize']);
        } catch (\InvalidArgumentException $e) {
            $errors['smalltextsize'] = get_string('invalidsmalltextsize', 'certificateelement_coursefieldtext');
        }
        try {
            $area = area_height::normalise($data['areaheight'] ?? $this->settings()['areaheight']);
            if (($data['verticalanchor'] ?? $this->settings()['verticalanchor']) === 'middle' && $area === null) {
                $errors['areaheight'] = get_string('middleneedsarea', 'certificateelement_coursefieldtext');
            }
            if ($area !== null && ((float)($data['posy'] ?? $this->get_posy()) < 0 ||
                    (float)($data['posy'] ?? $this->get_posy()) + $area > (float)$this->get_page()->to_record()->height)) {
                $errors['areaheight'] = get_string('areaoutsidepage', 'certificateelement_coursefieldtext');
            }
        } catch (\InvalidArgumentException $e) {
            $errors['areaheight'] = get_string('invalidareaheight', 'certificateelement_coursefieldtext');
        }
        if (!in_array($data['verticalanchor'] ?? 'top', ['top', 'middle', 'bottom'], true)) {
            $errors['verticalanchor'] = get_string('invalidanchor', 'certificateelement_coursefieldtext');
        }
        return $errors;
    }

    public function save_form_data(\stdClass $data) {
        $settings = $this->settings();
        $anchor = $data->verticalanchor ?? $settings['verticalanchor'];
        if (!in_array($anchor, ['top', 'middle', 'bottom'], true)) {
            throw new \invalid_parameter_exception('Invalid certificate vertical anchor');
        }
        $data->data = json_encode(['field' => (string)($data->field ?? $settings['field']),
            'previewcourseid' => (int)($data->previewcourseid ?? $settings['previewcourseid']),
            'lineheight' => line_height::normalise($data->lineheight ?? $settings['lineheight']), 'verticalanchor' => $anchor,
            'areaheight' => area_height::normalise($data->areaheight ?? $settings['areaheight']),
            'smalltextsize' => small_text::normalise($data->smalltextsize ?? $settings['smalltextsize'])]);
        parent::save_form_data($data);
    }

    public function prepare_data_for_form() {
        $record = parent::prepare_data_for_form();
        foreach ($this->settings() as $key => $value) {
            $record->$key = $value;
        }
        return $record;
    }

    /** The native HTML cell applies its refpoint alignment to every explicit line. */
    public function render($pdf, $preview, $user, $issue) {
        $settings = $this->settings();
        $text = $preview ? source::preview_html((string)$settings['field'], (int)$settings['previewcourseid']) :
            ($issue ? source::issued_html((string)$settings['field'], $issue) : '');
        $text = small_text::render($text, $settings['smalltextsize'], (float)$this->get_fontsize());
        $alignment = $preview ? source::preview_alignment((string)$settings['field'], (int)$settings['previewcourseid']) :
            ($issue ? source::issued_alignment((string)$settings['field'], $issue) : '');
        $courseanchor = $preview ? source::preview_vertical_anchor((string)$settings['field'], (int)$settings['previewcourseid']) :
            ($issue ? source::issued_vertical_anchor((string)$settings['field'], $issue) : '');
        $anchor = $courseanchor === '' ? $settings['verticalanchor'] : $courseanchor;
        $target = $alignment === '' && $anchor === 'top' && $settings['areaheight'] === null ? $pdf :
            new aligned_pdf($pdf, $alignment, $anchor, $settings['areaheight'], (float)$this->get_page()->to_record()->height);
        $height = $settings['lineheight'];
        if ($height === null) {
            \tool_certificate\element_helper::render_content($target, $this, $text);
            return;
        }
        $previousheight = $pdf->getCellHeightRatio();
        try {
            $pdf->setCellHeightRatio($height);
            \tool_certificate\element_helper::render_content($target, $this, $text);
        } finally {
            $pdf->setCellHeightRatio($previousheight);
        }
    }

    public function render_html() {
        return $this->render_preview_html((int)$this->settings()['previewcourseid'], true);
    }

    /** Read-only current course preview without changing saved designer settings. */
    public function render_course_html(int $courseid): string {
        require_capability('moodle/course:update', \context_course::instance($courseid));
        return $this->render_preview_html($courseid);
    }

    private function render_preview_html(int $courseid, bool $designer = false): string {
        $settings = $this->settings();
        $text = small_text::render(source::preview_html((string)$settings['field'], $courseid),
            $settings['smalltextsize']);
        // Native PDF lists keep hanging indents and left-aligned items inside the positioned block.
        $html = str_replace(['<ul>', '<ol>'],
            ['<ul style="text-align: left;">', '<ol style="text-align: left;">'], $text);
        if ($settings['lineheight'] !== null) {
            $html = \html_writer::div($html, '', ['style' => 'line-height: ' . $settings['lineheight'] . ';']);
        }
        $alignment = source::preview_alignment((string)$settings['field'], $courseid);
        if ($alignment !== '') {
            $html = \html_writer::div($html, '', ['style' => 'text-align: ' .
                ['L' => 'left', 'C' => 'center', 'R' => 'right'][$alignment] . ';']);
        }
        $html = \tool_certificate\element_helper::render_html_content($this, $html);
        $courseanchor = source::preview_vertical_anchor((string)$settings['field'], $courseid);
        $anchor = $courseanchor === '' ? $settings['verticalanchor'] : $courseanchor;
        if ($anchor === 'middle' && $settings['areaheight'] === null) {
            return \html_writer::div(s(get_string('middleneedsarea', 'certificateelement_coursefieldtext')), 'alert alert-warning');
        }
        if ($settings['areaheight'] !== null) {
            // The native parent retains Y; the visible area always starts at that Y.
            // The designer scales PDF points and millimetres together; CSS mm are physical and would not scale.
            $emheight = $settings['areaheight'] * 72 / (25.4 * max(1, (float)$this->get_fontsize()));
            $justify = ['top' => 'flex-start', 'middle' => 'center', 'bottom' => 'flex-end'][$anchor];
            $html = \html_writer::div($html, '', ['style' => 'height: ' . $emheight .
                'em; display: flex; flex-direction: column; justify-content: ' . $justify . ';' .
                    ($designer ? ' outline: 1px dashed #6c757d; outline-offset: -1px;' : ''),
                'data-coursefieldtext-area' => $settings['areaheight']]);
        } else if ($anchor === 'bottom') {
            // Keep the parent's original Y as drag/reference coordinate; translate only visible text.
            $html = \html_writer::div($html, '', ['style' => 'transform: translateY(-100%);',
                'data-coursefieldtext-anchor' => 'bottom']);
        }
        return $html;
    }
}
