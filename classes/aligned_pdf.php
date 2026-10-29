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

/** Keep native X/width; align against legacy Y or inside an optional fixed-height area. */
final class aligned_pdf {
    private object $pdf;
    private string $alignment;
    private string $anchor;
    private ?float $areaheight;
    private ?float $pageheight;

    public function __construct(object $pdf, string $alignment, string $anchor = 'top',
            ?float $areaheight = null, ?float $pageheight = null) {
        if (!in_array($alignment, ['', 'L', 'C', 'R'], true) || !in_array($anchor, ['top', 'middle', 'bottom'], true)) {
            throw new \coding_exception('Invalid certificate text alignment');
        }
        $this->pdf = $pdf;
        $this->alignment = $alignment;
        $this->anchor = $anchor;
        $this->areaheight = area_height::normalise($areaheight);
        $this->pageheight = $pageheight;
        if ($anchor === 'middle' && $this->areaheight === null) {
            throw new \moodle_exception('middleneedsarea', 'certificateelement_coursefieldtext');
        }
    }

    public function __call(string $method, array $args) {
        if (strtolower($method) === 'writehtmlcell') {
            if ($this->alignment !== '') {
                $args[9] = $this->alignment;
            }
            if ($this->areaheight !== null) {
                if ($args[3] < 0 || ($this->pageheight !== null &&
                        $args[3] + $this->areaheight > $this->pageheight + 0.0000001)) {
                    throw new \moodle_exception('areaoutsidepage', 'certificateelement_coursefieldtext');
                }
                if ($args[4] !== '') {
                    $height = $this->measure_height($args);
                    if ($height > $this->areaheight + 0.0000001) {
                        throw new \moodle_exception('textoutsidearea', 'certificateelement_coursefieldtext');
                    }
                    if ($this->anchor === 'bottom' || $this->anchor === 'middle') {
                        $remaining = max(0.0, $this->areaheight - $height);
                        $args[3] += $this->anchor === 'middle' ? $remaining / 2 : $remaining;
                    }
                }
            } else if ($this->anchor === 'bottom' && $args[4] !== '') {
                $args[3] -= $this->measure_height($args);
                if ($args[3] < -0.0000001) {
                    throw new \moodle_exception('textaboveedge', 'certificateelement_coursefieldtext');
                }
                $args[3] = max(0.0, $args[3]);
            }
        }
        return $this->pdf->$method(...$args);
    }

    /** Measure actual HTML wrapping, fonts and spacing, then undo all probe output. */
    private function measure_height(array $args): float {
        $this->pdf->startTransaction();
        try {
            $this->pdf->SetAutoPageBreak(false, 0);
            $args[3] = 1;
            $args[6] = 1; // Leave the probe cursor below the entire block.
            $this->pdf->writeHTMLCell(...$args);
            return max(0.0, (float)$this->pdf->GetY() - 1.0);
        } finally {
            $this->pdf->rollbackTransaction(true);
        }
    }
}
