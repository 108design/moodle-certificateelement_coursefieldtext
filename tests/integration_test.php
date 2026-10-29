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

/** Real course export, native issue storage, template controls and PDF rendering. */
final class integration_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        // Parent caches category IDs across tests; native database resets invalidate them.
        \tool_certificate\customfield\issue_handler::reset_caches();
        $this->setAdminUser();
    }

    private function fixture(string $text = "One\nTwo\nThree\nFour"): array {
        $generator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $category = $generator->create_category();
        $field = $generator->create_field(['type' => 'multiline', 'categoryid' => $category->get('id')]);
        $course = $this->getDataGenerator()->create_course();
        $value = \core_customfield\data_controller::create(0, (object)['instanceid' => $course->id], $field);
        $value->set('contextid', $value->get_context()->id);
        $value->instance_form_save((object)[$value->get_form_element_name() => $text]);
        $certgen = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $template = $certgen->create_template(['name' => 'Multiline']);
        $page = $certgen->create_page($template);
        $element = $certgen->create_element($page->get_id(), 'coursefieldtext', [
            'field' => $field->get('shortname'), 'previewcourseid' => $course->id, 'name' => 'Multiline',
            'font' => 'freesans', 'fontsize' => 14, 'colour' => '#123456', 'width' => 100,
            'posx' => 105, 'posy' => 70, 'refpoint' => 1]);
        return [$field, $course, $value, \tool_certificate\template::instance($template->get_id()), $element];
    }

    private function issue($template, $course): \stdClass {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/coursecertificate/lib.php');
        $user = $this->getDataGenerator()->create_user();
        $data = \mod_coursecertificate\helper::get_issue_data($course, $user);
        $id = $template->issue_certificate($user->id, null, $data, 'mod_coursecertificate', $course->id);
        return $DB->get_record('tool_certificate_issues', ['id' => $id], '*', MUST_EXIST);
    }

    public function test_real_issuance_long_text_and_regeneration_use_saved_export(): void {
        $text = str_repeat('A & <b>Ö</b> ', 250) . "\n\nLast";
        [$field, $course, $value, $template, $element] = $this->fixture($text);
        $handler = \tool_certificate\customfield\issue_handler::create();
        // Deliberately reproduce the native short-text fallback before our callback runs.
        $handler->ensure_field_exists('coursecustomfield_' . $field->get('shortname'), 'text', 'Fallback', true);
        $issue = $this->issue($template, $course);
        $mirror = $handler->find_field_by_shortname('coursecustomfield_' . $field->get('shortname'));
        $this->assertSame('textarea', $mirror->get('type'));
        $this->assertSame($text, source::issued($field->get('shortname'), $issue));
        $this->assertStringStartsWith('%PDF-', $template->get_issue_file($issue)->get_content());
        $value->set('contextid', $value->get_context()->id);
        $value->instance_form_save((object)[$value->get_form_element_name() => 'Changed']);
        $template->create_issue_file($issue, true);
        $this->assertSame($text, source::issued($field->get('shortname'), $issue));
        $newissue = $this->issue($template, $course);
        $this->assertSame('Changed', source::issued($field->get('shortname'), $newissue));
        $field->delete();
        $this->assertSame($text, source::issued($value->get_field()->get('shortname'), $issue));
    }

    public function test_preview_permission_defaults_and_explicit_empty(): void {
        [$field, $course, $value, $template, $element] = $this->fixture();
        $shortname = $field->get('shortname');
        $this->assertSame("One\nTwo\nThree\nFour", source::preview($shortname, $course->id));
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setUser($student);
        $this->assertSame('', source::preview($shortname, $course->id));
        $this->setAdminUser();
        $value->delete();
        $config = $field->get('configdata');
        $config['defaultvalue'] = "Default\nLine";
        $field->set('configdata', json_encode($config));
        $field->save();
        // Native handler definitions are request-cached; use the actual form save path to refresh them.
        $field->get_handler()->save_field_configuration($field, (object)[]);
        $this->assertSame("Default\nLine", source::preview($shortname, $course->id));
        $empty = \core_customfield\data_controller::create(0, (object)['instanceid' => $course->id], $field);
        $empty->set('contextid', $empty->get_context()->id);
        $empty->instance_form_save((object)[$empty->get_form_element_name() => '']);
        $this->assertSame('', source::preview($shortname, $course->id));
        $this->assertSame('', source::issued($shortname, (object)['data' => '{}']));
        $this->assertSame('', source::issued($shortname, (object)['data' => '{invalid']));
    }

    public function test_all_alignments_reach_native_pdf_renderer_without_html_injection(): void {
        [$field, $course, $value, $template, $element] = $this->fixture("Long first line\nShort\n\n<script>literal</script>");
        $issue = $this->issue($template, $course);
        foreach ([0 => 'L', 1 => 'C', 2 => 'R'] as $refpoint => $alignment) {
            $element->save_form_data((object)['field' => $field->get('shortname'), 'previewcourseid' => $course->id,
                'refpoint' => $refpoint, 'width' => 100, 'font' => 'freesans', 'fontsize' => 14,
                'colour' => '#123456', 'posx' => 105, 'posy' => 70]);
            $pdf = new class {
                public array $calls = [];
                public function __call($method, $args) { $this->calls[$method] = $args; }
            };
            $element->render($pdf, false, null, $issue);
            $args = $pdf->calls['writeHTMLCell'];
            $this->assertSame($alignment, $args[9]);
            $this->assertSame(3, substr_count($args[4], '<br>'));
            $this->assertStringNotContainsString('<script>', $args[4]);
            $this->assertSame(14, (int)$pdf->calls['setFont'][2]);
            $this->assertStringStartsWith('%PDF-', $template->generate_pdf(false, $issue, true));
        }
    }
    public function test_hidden_standard_and_multiline_fields_are_available_to_certificates(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/coursecertificate/lib.php');
        $generator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $category = $generator->create_category();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $fields = [];
        foreach (['text', 'textarea', 'multiline'] as $type) {
            $field = $generator->create_field(['type' => $type, 'categoryid' => $category->get('id'),
                'configdata' => ['visibility' => 0]]);
            $data = \core_customfield\data_controller::create(0, (object)['instanceid' => $course->id], $field);
            $data->set('contextid', $data->get_context()->id);
            $data->set($data->datafield(), $type === 'textarea' ? '<p>Hidden editor text</p>' : "Hidden\n" . $type);
            $data->set('value', $type === 'textarea' ? '<p>Hidden editor text</p>' : "Hidden\n" . $type);
            $data->set('valueformat', $type === 'textarea' ? FORMAT_HTML : FORMAT_PLAIN);
            $data->save();
            $fields[] = $field;
        }
        $this->setUser($student);
        foreach ($fields as $field) {
            $this->assertFalse($field->get_handler()->can_view($field, $course->id));
        }
        $this->setAdminUser();
        $export = \mod_coursecertificate\helper::get_issue_data($course, $student);
        foreach ($fields as $field) {
            $this->assertNotEmpty($export['coursecustomfield_' . $field->get('shortname')]);
        }
    }

    public function test_partial_designer_save_keeps_field_and_preview_selection(): void {
        [$field, $course, $value, $template, $element] = $this->fixture();
        $element->save_form_data((object)['posx' => 99, 'posy' => 40]);
        $record = $element->prepare_data_for_form();
        $this->assertSame($field->get('shortname'), $record->field);
        $this->assertSame((int)$course->id, $record->previewcourseid);
        $this->assertEquals(99, $record->posx);
    }

    public function test_native_element_form_exposes_all_presentation_controls_and_help(): void {
        global $PAGE;
        $this->fixture();
        $PAGE->set_url('/admin/tool/certificate/template.php');
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $form = $generator->create_template_and_edit_element_form('coursefieldtext');
        $html = $form->render();
        foreach (['field', 'previewcourseid', 'lineheight', 'smalltextsize', 'areaheight', 'verticalanchor', 'font', 'fontsize', 'colour', 'width', 'refpoint', 'posx', 'posy'] as $name) {
            $this->assertStringContainsString('name="' . $name . '"', $html);
        }
        $this->assertStringContainsString('coursefieldtexthelp', $html);
        $this->assertStringContainsString('help-icon', $html);
    }

    public function test_existing_shorttext_mirror_keeps_historical_value_id_and_owner_visibility(): void {
        [$field] = $this->fixture();
        $handler = \tool_certificate\customfield\issue_handler::create();
        $key = 'coursecustomfield_' . $field->get('shortname');
        $mirror = $handler->ensure_field_exists($key, 'text', 'Existing mirror', false);
        $mirrorid = $mirror->get('id');
        // Generic customfield storage is the migration boundary; no recipient record is required here.
        $stored = \core_customfield\data_controller::create(0,
            (object)['instanceid' => 98765, 'contextid' => \context_system::instance()->id], $mirror);
        $stored->instance_form_save((object)[$stored->get_form_element_name() => 'Historical & text']);
        source::register_fields();
        $updated = $handler->find_field_by_shortname($key);
        $this->assertEquals($mirrorid, $updated->get('id'));
        $this->assertSame('textarea', $updated->get('type'));
        $this->assertEmpty($updated->get_configdata_property('visible'));
        $this->assertSame('Historical & text', \core_customfield\data_controller::create($stored->get('id'))->get_value());
    }

    public function test_small_size_save_preview_issuance_and_legacy_regeneration(): void {
        [$field, $course, $value, $template, $element] = $this->fixture('<small>Advanced</small><br>Certificate');
        $name = $value->get_form_element_name();
        $value->set('valueformat', FORMAT_HTML);
        $value->save();
        $old = $this->issue($template, $course);
        $this->assertSame('Advanced<br>Certificate', source::issued_html($field->get('shortname'), $old));
        $value->instance_form_save((object)[$name => $value->get_value(), $name . '_format' => FORMAT_HTML]);
        $this->assertSame(60.0, $element->prepare_data_for_form()->smalltextsize);
        $element->save_form_data((object)['smalltextsize' => '62,5']);
        $element->save_form_data((object)['posy' => 70]);
        $this->assertSame(62.5, $element->prepare_data_for_form()->smalltextsize);
        $this->assertStringContainsString('font-size: 62.5%;', $element->render_html());
        $this->assertStringContainsString('font-size: 62.5%;', $element->render_course_html($course->id));
        $this->assertArrayHasKey('smalltextsize', $element->validate_form_elements(
            ['field' => $field->get('shortname'), 'smalltextsize' => 'bad'], []));
        $new = $this->issue($template, $course);
        $this->assertSame('<small>Advanced</small><br>Certificate', source::issued_html($field->get('shortname'), $new));
        $pdf = new class {
            public array $calls = [];
            public function __call($method, $args) { $this->calls[$method] = $args; }
        };
        $element->render($pdf, false, null, $new);
        $this->assertStringContainsString('font-size: 8.75pt;', $pdf->calls['writeHTMLCell'][4]);
        $value->instance_form_save((object)[$name => 'Changed', $name . '_format' => FORMAT_PLAIN]);
        $template->create_issue_file($old, true);
        $template->create_issue_file($new, true);
        $this->assertSame('Advanced<br>Certificate', source::issued_html($field->get('shortname'), $old));
        $this->assertSame('<small>Advanced</small><br>Certificate', source::issued_html($field->get('shortname'), $new));
        $element->save_form_data((object)['smalltextsize' => '']);
        $this->assertSame(60.0, $element->prepare_data_for_form()->smalltextsize);
    }

    public function test_line_height_is_validated_retained_on_drag_and_restored_after_render_errors(): void {
        [$field, $course, $value, $template, $element] = $this->fixture();
        $this->assertNull($element->prepare_data_for_form()->lineheight);
        $this->assertArrayHasKey('lineheight', $element->validate_form_elements(
            ['field' => $field->get('shortname'), 'lineheight' => '0'], []));
        $element->save_form_data((object)['lineheight' => '1,0']);
        $element->save_form_data((object)['posx' => 101]);
        $this->assertSame(1.0, $element->prepare_data_for_form()->lineheight);
        $this->assertStringContainsString('line-height: 1;', $element->render_html());
        $pdf = new class {
            public float $ratio = 1.25;
            public array $rendered = [];
            public bool $fail = false;
            public function getCellHeightRatio() { return $this->ratio; }
            public function setCellHeightRatio($ratio) { $this->ratio = $ratio; }
            public function __call($name, $args) {
                if ($name === 'writeHTMLCell') {
                    $this->rendered[] = $this->ratio;
                    if ($this->fail) { throw new \RuntimeException('render failed'); }
                }
            }
        };
        $element->render($pdf, true, null, null);
        $this->assertSame([1.0], $pdf->rendered);
        $this->assertSame(1.25, $pdf->ratio);
        $pdf->fail = true;
        try {
            $element->render($pdf, true, null, null);
            $this->fail('Expected renderer error');
        } catch (\RuntimeException $e) {
            $this->assertSame('render failed', $e->getMessage());
        }
        $this->assertSame(1.25, $pdf->ratio);
        $element->save_form_data((object)['lineheight' => '']);
        $this->assertNull($element->prepare_data_for_form()->lineheight);
    }

    public function test_issued_html_keeps_semantic_mode_after_field_switch_deletion_and_missing_data(): void {
        [$field, $course, $value, $template, $element] = $this->fixture(
            '<p style="font-size:80pt"><strong>Topics</strong></p><ul><li>A &amp; B</li><li>C</li></ul>');
        $value->instance_form_save((object)[$value->get_form_element_name() => $value->get_value(),
            $value->get_form_element_name() . '_format' => FORMAT_HTML]);
        $this->assertStringContainsString('<ul style="text-align: left;">', $element->render_html());
        $this->assertStringContainsString('<ul>', source::preview_html($field->get('shortname'), $course->id));
        $issue = $this->issue($template, $course);
        $html = source::issued_html($field->get('shortname'), $issue);
        $this->assertSame('<div><strong>Topics</strong></div><ul><li>A &amp; B</li><li>C</li></ul>', $html);
        $this->assertStringStartsWith('%PDF-', $template->generate_pdf(false, $issue, true));
        $shortname = $field->get('shortname');
        $value->instance_form_save((object)[$value->get_form_element_name() => $value->get_value(),
            $value->get_form_element_name() . '_format' => FORMAT_PLAIN]);
        $this->assertStringNotContainsString('<ul>', source::preview_html($shortname, $course->id));
        $this->assertSame($html, source::issued_html($shortname, $issue));
        $plainissue = $this->issue($template, $course);
        $this->assertStringNotContainsString('<ul>', source::issued_html($shortname, $plainissue));
        $template->create_issue_file($issue, true);
        $this->assertSame($html, source::issued_html($shortname, $issue));
        $field->delete();
        $this->assertSame($html, source::issued_html($shortname, $issue));
        $this->assertSame('', source::issued_html($shortname, (object)['data' => '{}']));
    }

    public function test_course_alignment_snapshot_overrides_text_inside_same_template_block(): void {
        [$field, $course, $value, $template, $element] = $this->fixture("Long first line\nShort");
        $name = $value->get_form_element_name();
        foreach (['L' => 'left', 'C' => 'center', 'R' => 'right'] as $alignment => $css) {
            $value->instance_form_save((object)[$name => "Long first line\nShort", $name . '_alignment' => $alignment]);
            $this->assertStringContainsString('text-align: ' . $css . ';', $element->render_html());
            $issue = $this->issue($template, $course);
            $saved = source::issued_html($field->get('shortname'), $issue);
            $this->assertSame($alignment, source::issued_alignment($field->get('shortname'), $issue));
            $pdf = new class {
                public array $calls = [];
                public function __call($method, $args) { $this->calls[$method] = $args; }
            };
            $element->render($pdf, false, null, $issue);
            $this->assertSame($alignment, $pdf->calls['writeHTMLCell'][9]);
            $this->assertEqualsWithDelta(100, $pdf->calls['writeHTMLCell'][0], 0.001);
            $this->assertEquals(55, $pdf->calls['writeHTMLCell'][2]);
            $this->assertStringStartsWith('%PDF-', $template->generate_pdf(false, $issue, true));
            $value->instance_form_save((object)[$name => 'Later', $name . '_alignment' => '']);
            $this->assertSame($saved, source::issued_html($field->get('shortname'), $issue));
            $this->assertSame(1, (int)$element->get_refpoint());
            $this->assertSame(100, (int)$element->get_width());
        }
    }

    public function test_vertical_default_course_override_and_issue_snapshot(): void {
        [$field, $course, $value, $template, $element] = $this->fixture("First\nSecond\nThird\nLast");
        $name = $value->get_form_element_name();
        $this->assertSame('top', $element->prepare_data_for_form()->verticalanchor);
        $this->assertStringNotContainsString('translateY', $element->render_html());
        $element->save_form_data((object)['verticalanchor' => 'bottom', 'lineheight' => 1.0]);
        $element->save_form_data((object)['posx' => 105]);
        $this->assertSame('bottom', $element->prepare_data_for_form()->verticalanchor);
        $this->assertStringContainsString('translateY(-100%)', $element->render_html());
        $inherited = $this->issue($template, $course);
        $this->assertSame('', source::issued_vertical_anchor($field->get('shortname'), $inherited));
        $this->assertStringStartsWith('%PDF-', $template->generate_pdf(false, $inherited, true));
        $value->instance_form_save((object)[$name => "First\nSecond\nThird\nLast", $name . '_verticalanchor' => 'top']);
        $this->assertStringNotContainsString('translateY', $element->render_html());
        $top = $this->issue($template, $course);
        $this->assertSame('top', source::issued_vertical_anchor($field->get('shortname'), $top));
        $value->instance_form_save((object)[$name => 'Now bottom', $name . '_verticalanchor' => 'bottom']);
        $bottom = $this->issue($template, $course);
        $this->assertSame('bottom', source::issued_vertical_anchor($field->get('shortname'), $bottom));
        $element->save_form_data((object)['verticalanchor' => 'top']);
        $this->assertStringContainsString('translateY', $element->render_html());
        $this->assertStringStartsWith('%PDF-', $template->generate_pdf(false, $bottom, true));
        $this->assertSame('top', source::issued_vertical_anchor($field->get('shortname'), $top));
        $this->assertArrayHasKey('verticalanchor', $element->validate_form_elements(
            ['field' => $field->get('shortname'), 'verticalanchor' => 'invalid'], []));
        $field->delete();
        $this->assertSame('bottom', source::issued_vertical_anchor($value->get_field()->get('shortname'), $bottom));
    }

    public function test_area_height_save_clear_validation_course_overrides_and_native_pdf(): void {
        [$field, $course, $value, $template, $element] = $this->fixture();
        $this->assertNull($element->prepare_data_for_form()->areaheight);
        $element->save_form_data((object)['areaheight' => '40,5', 'verticalanchor' => 'bottom']);
        $element->save_form_data((object)['posx' => 105]);
        $this->assertSame(40.5, $element->prepare_data_for_form()->areaheight);
        $this->assertStringContainsString('data-coursefieldtext-area="40.5"', $element->render_html());
        $this->assertStringContainsString('em; display: flex;', $element->render_html());
        $this->assertStringContainsString('justify-content: flex-end', $element->render_html());
        $this->assertStringNotContainsString('translateY', $element->render_html());
        $name = $value->get_form_element_name();
        $value->instance_form_save((object)[$name => $value->get_value(), $name . '_verticalanchor' => 'top']);
        $this->assertStringContainsString('justify-content: flex-start', $element->render_html());
        $issue = $this->issue($template, $course);
        $this->assertStringStartsWith('%PDF-', $template->generate_pdf(false, $issue, true));
        $this->assertArrayHasKey('areaheight', $element->validate_form_elements(
            ['field' => $field->get('shortname'), 'areaheight' => -1], []));
        $this->assertArrayHasKey('areaheight', $element->validate_form_elements(
            ['field' => $field->get('shortname'), 'areaheight' => 250, 'posy' => 70], []));
        $element->save_form_data((object)['areaheight' => '']);
        $this->assertNull($element->prepare_data_for_form()->areaheight);
        $this->assertStringNotContainsString('data-coursefieldtext-area', $element->render_html());
    }

    public function test_middle_default_course_override_and_issued_snapshot(): void {
        [$field, $course, $value, $template, $element] = $this->fixture("First\nLast");
        $name = $value->get_form_element_name();
        $element->save_form_data((object)['areaheight' => 40, 'verticalanchor' => 'middle', 'lineheight' => 1]);
        $element->save_form_data((object)['posx' => 105]);
        $this->assertSame('middle', $element->prepare_data_for_form()->verticalanchor);
        $this->assertStringContainsString('justify-content: center;', $element->render_html());
        $inherited = $this->issue($template, $course);
        $this->assertSame('', source::issued_vertical_anchor($field->get('shortname'), $inherited));
        foreach ([FORMAT_PLAIN, FORMAT_HTML] as $mode) {
            $raw = $mode === FORMAT_HTML ? '<strong>First</strong><br>Last' : "First\nLast";
            $value->instance_form_save((object)[$name => $raw, $name . '_format' => $mode, $name . '_verticalanchor' => 'middle']);
            $element->save_form_data((object)['verticalanchor' => 'top']);
            $this->assertStringContainsString('justify-content: center;', $element->render_course_html((int)$course->id));
            $issue = $this->issue($template, $course);
            $this->assertSame('middle', source::issued_vertical_anchor($field->get('shortname'), $issue));
            $value->instance_form_save((object)[$name => 'Changed', $name . '_verticalanchor' => 'bottom']);
            $this->assertSame('middle', source::issued_vertical_anchor($field->get('shortname'), $issue));
            $this->assertStringStartsWith('%PDF-', $template->generate_pdf(false, $issue, true));
        }
        $this->assertArrayHasKey('areaheight', $element->validate_form_elements(
            ['field' => $field->get('shortname'), 'areaheight' => '', 'verticalanchor' => 'middle'], []));
        $element->save_form_data((object)['areaheight' => '', 'verticalanchor' => 'middle']);
        $value->instance_form_save((object)[$name => 'Changed', $name . '_verticalanchor' => '']);
        $this->assertStringContainsString(get_string('middleneedsarea', 'certificateelement_coursefieldtext'), $element->render_html());
    }
}
