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

/** Native activity/template resolution, permissions and read-only HTML integration. */
final class course_preview_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    private function fixture(): array {
        $course = $this->getDataGenerator()->create_course(['fullname' => 'Current & course']);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $template = $generator->create_template(['name' => 'Assigned system template']);
        $page = $generator->create_page($template);
        $page->save((object)['width' => 210, 'height' => 297, 'leftmargin' => 15, 'rightmargin' => 15]);
        $fieldgen = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $category = $fieldgen->create_category();
        $field = $fieldgen->create_field(['type' => 'multiline', 'categoryid' => $category->get('id'),
            'configdata' => ['visibility' => 0]]);
        $value = \core_customfield\data_controller::create(0, (object)['instanceid' => $course->id], $field);
        $value->set('contextid', $value->get_context()->id);
        $name = $value->get_form_element_name();
        $value->instance_form_save((object)[$name => '<strong>Current hidden</strong><br>Second',
            $name . '_format' => FORMAT_HTML, $name . '_alignment' => 'C', $name . '_verticalanchor' => 'bottom']);
        $element = $generator->create_element($page->get_id(), 'coursefieldtext', [
            'name' => 'Current course text', 'field' => $field->get('shortname'), 'previewcourseid' => 0,
            'areaheight' => 40, 'verticalanchor' => 'top', 'lineheight' => 1, 'font' => 'freesans',
            'fontsize' => 14, 'colour' => '#123456', 'width' => 100, 'posx' => 105, 'posy' => 100, 'refpoint' => 1]);
        $activity = $this->getDataGenerator()->create_module('coursecertificate',
            ['course' => $course->id, 'template' => $template->get_id(), 'name' => 'Saved assignment']);
        $editor = $this->getDataGenerator()->create_user(['firstname' => 'PrivateRecipientName']);
        $this->getDataGenerator()->enrol_user($editor->id, $course->id, 'editingteacher');
        return [$course, $template, $page, $field, $value, $element, $activity, $editor];
    }

    public function test_saved_assignment_current_hidden_html_and_no_persistent_writes(): void {
        global $DB;
        [$course, $template, $page, $field, $value, $element, $activity, $editor] = $this->fixture();
        $this->setUser($editor);
        $this->assertFalse(has_capability('tool/certificate:manage', $template->get_context()));
        $before = $DB->get_record('tool_certificate_elements', ['id' => $element->get_id()]);
        $savedvalue = $DB->get_record('customfield_data', ['id' => $value->get('id')]);
        $issues = $DB->count_records('tool_certificate_issues');
        $this->assertSame($template->get_id(), course_preview::template($course, $activity->cmid)->get_id());
        $html = course_preview::render($course, $activity->cmid);
        $this->assertStringContainsString('<strong>Current hidden</strong>', $html);
        $this->assertStringContainsString('data-coursefieldtext-area="40"', $html);
        $this->assertStringContainsString('justify-content: flex-end', $html);
        $this->assertStringContainsString('left:55mm;top:100mm;', $html);
        $this->assertStringNotContainsString('data-action="editelement"', $html);
        $this->assertStringNotContainsString('PrivateRecipientName', $html);
        $this->assertEquals($before, $DB->get_record('tool_certificate_elements', ['id' => $element->get_id()]));
        $this->assertEquals($savedvalue, $DB->get_record('customfield_data', ['id' => $value->get('id')]));
        $this->assertSame($issues, $DB->count_records('tool_certificate_issues'));
        $name = $value->get_form_element_name();
        $value->instance_form_save((object)[$name => "<strong>Literal</strong>\nNext", $name . '_format' => FORMAT_PLAIN,
            $name . '_verticalanchor' => 'top']);
        $plain = course_preview::render($course, $activity->cmid);
        $this->assertStringContainsString('&lt;strong&gt;Literal&lt;/strong&gt;', $plain);
        $this->assertStringContainsString('justify-content: flex-start', $plain);
        $value->instance_form_save((object)[$name => '', $name . '_format' => FORMAT_PLAIN]);
        $this->assertStringNotContainsString('Current hidden', course_preview::render($course, $activity->cmid));
    }

    public function test_cross_course_and_non_certificate_ids_rejected(): void {
        [$course, $template, $page, $field, $value, $element, $activity, $editor] = $this->fixture();
        $other = $this->getDataGenerator()->create_course();
        $foreign = $this->getDataGenerator()->create_module('coursecertificate', ['course' => $other->id]);
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
        $this->setUser($editor);
        foreach ([$foreign->cmid, $forum->cmid, 0, 999999] as $cmid) {
            try {
                course_preview::template($course, $cmid);
                $this->fail('Invalid activity accepted');
            } catch (\moodle_exception $exception) {
                $this->assertSame('invalidpreviewactivity', $exception->errorcode);
            }
        }
        $this->expectException(\required_capability_exception::class);
        course_preview::render($other, $foreign->cmid);
    }

    public function test_students_have_no_preview_or_navigation(): void {
        [$course, $template, $page, $field, $value, $element, $activity] = $this->fixture();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setUser($student);
        $this->assertSame([], course_preview::activities($course));
        $node = \navigation_node::create('Course');
        course_preview::navigation($node, $course, \context_course::instance($course->id));
        $this->assertFalse($node->get('coursefieldtext_preview'));
        $this->expectException(\required_capability_exception::class);
        course_preview::render($course, $activity->cmid);
    }

    public function test_native_navigation_discovery_multiple_activities_and_missing_module(): void {
        global $CFG;
        [$course, $template, $page, $field, $value, $element, $activity, $editor] = $this->fixture();
        $this->getDataGenerator()->create_module('coursecertificate', ['course' => $course->id]);
        $this->setUser($editor);
        require_once($CFG->dirroot . '/admin/tool/certificate/element/coursefieldtext/lib.php');
        $functions = get_plugins_with_function('extend_navigation_course', 'lib.php');
        $this->assertSame('certificateelement_coursefieldtext_extend_navigation_course',
            $functions['certificateelement']['coursefieldtext']);
        $this->assertCount(2, course_preview::activities($course));
        $node = \navigation_node::create('Course');
        certificateelement_coursefieldtext_extend_navigation_course($node, $course, \context_course::instance($course->id));
        $this->assertNotFalse($node->get('coursefieldtext_preview'));
        unset_config('version', 'mod_coursecertificate');
        $this->assertSame([], course_preview::activities($course));
    }

    public function test_static_course_data_images_and_personal_placeholders_on_multiple_pages(): void {
        [$course, $template, $page, $field, $value, $element, $activity, $editor] = $this->fixture();
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $base = ['font' => 'freesans', 'fontsize' => 12, 'colour' => '#000000', 'width' => 100,
            'posx' => 15, 'posy' => 30, 'refpoint' => 0];
        foreach (['coursefullname', 'coursecustomfield_' . $field->get('shortname'), 'coursegrade'] as $key) {
            $generator->create_element($page->get_id(), 'program', ['display' => $key] + $base);
        }
        $generator->create_element($page->get_id(), 'userfield', ['userfield' => 'firstname'] + $base);
        $generator->create_element($page->get_id(), 'date', ['dateitem' => 'issuedate', 'dateformat' => 'strftimedate'] + $base);
        $generator->create_element($page->get_id(), 'code', ['display' => 0] + $base);
        $image = $generator->create_element($page->get_id(), 'image', ['width' => 210, 'height' => 297,
            'isbackground' => 1, 'image' => 0] + $base);
        $image->save((object)['sequence' => 0]);
        get_file_storage()->create_file_from_string(['contextid' => $template->get_context()->id,
            'component' => 'tool_certificate', 'filearea' => 'element', 'itemid' => $image->get_id(),
            'filepath' => '/', 'filename' => 'background.png'], base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j6XQAAAAASUVORK5CYII='));
        $second = $generator->create_page($template);
        $generator->create_element($second->get_id(), 'text', ['text' => '<strong>Second page</strong>'] + $base);
        $this->setUser($editor);
        $html = course_preview::render($course, $activity->cmid);
        foreach (['Current &amp; course', '<strong>Current hidden</strong>', 'data:image/png;base64,',
            'width:210mm', '<strong>Second page</strong>'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        foreach (['previewperson', 'previewdate', 'previewcode', 'previewdynamic'] as $key) {
            $this->assertStringContainsString(get_string($key, 'certificateelement_coursefieldtext'), $html);
        }
        $this->assertStringNotContainsString('PrivateRecipientName', $html);
        $this->assertSame(2, substr_count($html, 'class="coursefieldtext-preview-page"'));
    }

    public function test_standard_editor_field_and_optional_course_image_use_current_course(): void {
        if (!class_exists('\certificateelement_coursefieldimg\image_source')) {
            $this->markTestSkipped('Optional Course Field Image adapter is not installed.');
        }
        [$course, $template, $page, $field, $value, $element, $activity, $editor] = $this->fixture();
        $fieldgen = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $category = $fieldgen->create_category();
        $field = $fieldgen->create_field(['type' => 'textarea', 'categoryid' => $category->get('id'),
            'configdata' => ['visibility' => 0]]);
        $data = \core_customfield\data_controller::create(0, (object)['instanceid' => $course->id], $field);
        $data->set('contextid', $data->get_context()->id);
        $data->set('value', '<p><strong>Editor field</strong><img src="@@PLUGINFILE@@/course.png"></p>');
        $data->set('valueformat', FORMAT_HTML);
        $data->save();
        $image = imagecreatetruecolor(2, 2);
        ob_start(); imagepng($image); $png = ob_get_clean(); imagedestroy($image);
        get_file_storage()->create_file_from_string(['contextid' => $data->get_context()->id,
            'component' => 'customfield_textarea', 'filearea' => 'value', 'itemid' => $data->get('id'),
            'filepath' => '/', 'filename' => 'course.png'], $png);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $base = ['font' => 'freesans', 'fontsize' => 12, 'colour' => '#000000', 'posx' => 15, 'posy' => 20, 'refpoint' => 0];
        $generator->create_element($page->get_id(), 'program',
            ['display' => 'coursecustomfield_' . $field->get('shortname'), 'width' => 100] + $base);
        $generator->create_element($page->get_id(), 'coursefieldimg',
            ['fieldid' => $field->get('id'), 'width' => 25, 'height' => 0, 'previewcourseid' => 0] + $base);
        $this->setUser($editor);
        $html = course_preview::render($course, $activity->cmid);
        $this->assertStringContainsString('<strong>Editor field</strong>', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString('width:25mm;height:25mm', $html);
        $this->assertStringNotContainsString('pluginfile.php', $html);
    }

    public function test_unknown_renderer_is_marked_without_executing_it(): void {
        [$course, $template, $page, $field, $value, $element, $activity, $editor] = $this->fixture();
        // Deliberately incomplete native signature: invoking its actual renderer would need private signature files.
        $signature = \tool_certificate\element::instance(0,
            (object)['pageid' => $page->get_id(), 'element' => 'digitalsignature']);
        $signature->save((object)['name' => 'issued date', 'posx' => 105, 'posy' => 10, 'data' => '{}',
            'font' => 'freesans', 'fontsize' => 18, 'colour' => '#123456', 'width' => 100]);
        $this->setUser($editor);
        foreach ([0 => ['left', 105], 1 => ['center', 55], 2 => ['right', 5]] as $refpoint => [$align, $left]) {
            $signature->save((object)['refpoint' => $refpoint]);
            $html = course_preview::render($course, $activity->cmid);
            $this->assertStringContainsString('[issued date]', $html);
            $doc = new \DOMDocument();
            @$doc->loadHTML('<?xml encoding="utf-8"?>' . $html);
            $xpath = new \DOMXPath($doc);
            $node = $xpath->query('//*[@data-preview-element="' . $signature->get_id() . '"]')->item(0);
            $this->assertStringContainsString('left:' . $left . 'mm;top:10mm;font-size:18pt;width:100mm;',
                $node->getAttribute('style'));
            if ($align !== 'left') {
                $this->assertStringContainsString('text-align: ' . $align . ';', $doc->saveHTML($node));
            } else {
                $this->assertStringNotContainsString('text-align: center', $doc->saveHTML($node));
            }
            $this->assertStringContainsString('font-family: Arial, sans-serif', $doc->saveHTML($node));
        }
    }
}
