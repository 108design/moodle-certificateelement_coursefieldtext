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

/** Measure the same HTML, roll back its effects, then change only Y and alignment. */
final class aligned_pdf_test extends \PHPUnit\Framework\TestCase {
    private function pdf(float $height = 20): object {
        return new class($height) {
            public array $writes = [];
            public int $rollbacks = 0;
            public bool $probe = false;
            public bool $fail = false;
            public bool $pagebreak = true;
            private float $height;
            public function __construct(float $height) { $this->height = $height; }
            public function startTransaction() { $this->probe = true; }
            public function SetAutoPageBreak($value, $margin) { $this->pagebreak = $value; }
            public function GetY() { return 1 + $this->height; }
            public function rollbackTransaction($self) { $this->probe = false; $this->pagebreak = true; $this->rollbacks++; }
            public function writeHTMLCell(...$args) {
                if ($this->probe) {
                    if ($this->fail) { throw new \RuntimeException('measurement failed'); }
                    return;
                }
                $this->writes[] = $args;
            }
        };
    }

    public function test_bottom_changes_y_only_and_inherited_alignment_stays_native(): void {
        $pdf = $this->pdf();
        $adapter = new aligned_pdf($pdf, '', 'bottom');
        $args = [80, 0, 65, 100, 'One<br>Two', 0, 0, false, true, 'R'];
        $adapter->writeHTMLCell(...$args);
        $expected = $args; $expected[3] = 80.0;
        $this->assertSame($expected, $pdf->writes[0]);
        $this->assertSame(1, $pdf->rollbacks);
        $this->assertTrue($pdf->pagebreak);
        $top = new aligned_pdf($pdf, 'C');
        $top->writeHTMLCell(...$args);
        $this->assertSame(100, $pdf->writes[1][3]);
        $this->assertSame('C', $pdf->writes[1][9]);
        $this->assertSame(1, $pdf->rollbacks);
        $boundary = $this->pdf(100.000000001);
        (new aligned_pdf($boundary, '', 'bottom'))->writeHTMLCell(...$args);
        $this->assertSame(0.0, $boundary->writes[0][3]);
    }

    public function test_probe_errors_restore_state_and_overflow_is_explicit(): void {
        $pdf = $this->pdf(); $pdf->fail = true;
        $adapter = new aligned_pdf($pdf, 'L', 'bottom');
        try {
            $adapter->writeHTMLCell(80, 0, 10, 100, 'Text', 0, 0, false, true, 'C');
            $this->fail('Expected probe failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('measurement failed', $exception->getMessage());
        }
        $this->assertSame([], $pdf->writes);
        $this->assertTrue($pdf->pagebreak);
        $this->assertSame(1, $pdf->rollbacks);
        $pdf = $this->pdf(110); $adapter = new aligned_pdf($pdf, '', 'bottom');
        try {
            $adapter->writeHTMLCell(80, 0, 10, 100, 'Text', 0, 0, false, true, 'C');
            $this->fail('Expected explicit overflow');
        } catch (\moodle_exception $exception) {
            $this->assertSame('textaboveedge', $exception->errorcode);
        }
        $this->assertSame([], $pdf->writes);
        $this->assertSame(1, $pdf->rollbacks);
        $adapter->writeHTMLCell(80, 0, 10, 100, '', 0, 0, false, true, 'C');
        $this->assertCount(1, $pdf->writes);
        $this->assertSame(1, $pdf->rollbacks);
    }
    public function test_fixed_area_top_bottom_and_legacy_coordinates(): void {
        $args = [80, 0, 65, 100, 'Text', 0, 0, false, true, 'C'];
        foreach (['top' => 100, 'middle' => 110, 'bottom' => 120] as $anchor => $expectedy) {
            $pdf = $this->pdf(20);
            (new aligned_pdf($pdf, '', $anchor, 40, 297))->writeHTMLCell(...$args);
            $this->assertEquals($expectedy, $pdf->writes[0][3]);
            $this->assertSame(65, $pdf->writes[0][2]);
            $this->assertSame(1, $pdf->rollbacks);
        }
        foreach (['top' => 100, 'bottom' => 80] as $anchor => $expectedy) {
            $pdf = $this->pdf(20);
            (new aligned_pdf($pdf, '', $anchor))->writeHTMLCell(...$args);
            $this->assertEquals($expectedy, $pdf->writes[0][3]);
        }
    }

    public function test_fixed_area_overflow_and_page_bounds_never_clip_output(): void {
        foreach (['top', 'middle', 'bottom'] as $anchor) {
            $pdf = $this->pdf(41);
            try {
                (new aligned_pdf($pdf, '', $anchor, 40, 297))->writeHTMLCell(80, 0, 65, 100, 'Text', 0, 0, false, true, 'C');
                $this->fail('Expected overflow');
            } catch (\moodle_exception $exception) {
                $this->assertSame('textoutsidearea', $exception->errorcode);
            }
            $this->assertSame([], $pdf->writes);
            $this->assertSame(1, $pdf->rollbacks);
            $this->assertTrue($pdf->pagebreak);
        }
        $pdf = $this->pdf();
        try {
            (new aligned_pdf($pdf, '', 'bottom', 40, 297))->writeHTMLCell(80, 0, 65, 280, '', 0, 0, false, true, 'C');
            $this->fail('Expected page boundary error');
        } catch (\moodle_exception $exception) {
            $this->assertSame('areaoutsidepage', $exception->errorcode);
        }
        $this->assertSame([], $pdf->writes);
    }
    public function test_middle_requires_an_area_and_preserves_empty_output(): void {
        $pdf = $this->pdf(20);
        (new aligned_pdf($pdf, '', 'middle', 40, 297))->writeHTMLCell(80, 0, 65, 100, '', 0, 0, false, true, 'C');
        $this->assertSame(100, $pdf->writes[0][3]);
        $this->assertSame(0, $pdf->rollbacks);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('middleneedsarea', 'certificateelement_coursefieldtext'));
        new aligned_pdf($pdf, '', 'middle');
    }
}
