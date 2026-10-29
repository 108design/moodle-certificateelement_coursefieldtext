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
 * File documentation for amd/src/preview_fit.js.
 *
 * @package   certificateelement_coursefieldtext
 * @copyright 2026 Andreas Giesen
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.

/** Fit complete certificate pages without reflowing their physical layout. */
export const init = () => {
    const frames = [...document.querySelectorAll('.coursefieldtext-preview-scroll')];
    const fit = () => {
        const navbar = document.querySelector('.navbar.fixed-top');
        const availableHeight = Math.max(1, window.innerHeight - (navbar?.getBoundingClientRect().height || 0));
        frames.forEach(frame => {
            const page = frame.querySelector('.coursefieldtext-preview-page');
            if (!page || !frame.clientWidth || !page.offsetWidth || !page.offsetHeight) return;
            const scale = Math.min(1, 0.9 * frame.clientWidth / page.offsetWidth,
                0.9 * availableHeight / page.offsetHeight);
            // Collapse unused vertical space when the width is the limiting dimension.
            const borders = frame.offsetHeight - frame.clientHeight;
            frame.style.height = (Math.ceil(page.offsetHeight * scale / 0.9) + borders) + 'px';
            page.style.transform = 'translate(-50%, -50%) scale(' + scale + ')';
            frame.classList.add('is-fitted');
        });
    };
    fit();
    // Container width also changes when Moodle drawers open or close.
    const observer = new ResizeObserver(fit);
    frames.forEach(frame => observer.observe(frame));
    window.addEventListener('resize', fit);
};
