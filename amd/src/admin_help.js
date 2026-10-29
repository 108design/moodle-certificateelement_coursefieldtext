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
 * File documentation for amd/src/admin_help.js.
 *
 * @package   certificateelement_coursefieldtext
 * @copyright 2026 Andreas Giesen
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
import {publish, subscribe} from 'core/pubsub';
import DrawerEvents from 'core/drawer_events';
import Drawers from 'theme_boost/drawers';
import jQuery from 'jquery';

/** Nonmodal contextual help; use native exclusive drawer coordination when available. */
export const init = config => {
    const panel = document.getElementById('certificateelement-coursefieldtext-context-help');
    if (!panel || panel.dataset.initialised) return;
    panel.dataset.initialised = '1';
    document.body.append(panel);
    const heading = panel.querySelector('h2');
    const content = panel.querySelector('.certificateelement-coursefieldtext-help-content');
    const choice = panel.querySelector('select');
    const helpUrl = new URL(panel.dataset.coursefieldtextHelpUrl, location.href);
    const hasTopic = topic => [...choice.options].some(option => option.value === topic);
    let opener = null;
    let lastInfoOpener = null;
    let activeTopic = '';
    let ownsSpace = false;
    const exclusiveDrawers = Boolean(DrawerEvents.DRAWER_EXCLUSIVE_REQUESTED && DrawerEvents.DRAWER_EXCLUSIVE_RELEASED);
    document.body.classList.toggle('certificateelement-coursefieldtext-help-legacy-drawers', !exclusiveDrawers);

    const position = () => {
        const nav = document.querySelector('.navbar.fixed-top');
        const top = nav ? Math.max(0, nav.getBoundingClientRect().bottom) + 12 : 12;
        panel.style.setProperty('--certificateelement-coursefieldtext-help-top', `${top}px`);
    };
    const hide = (returnFocus = true, releaseSpace = true) => {
        if (panel.hidden) return;
        panel.hidden = true;
        document.body.classList.remove('certificateelement-coursefieldtext-help-open');
        document.querySelectorAll('[data-coursefieldtext-help-open]').forEach(button => button.setAttribute('aria-expanded', 'false'));
        if (ownsSpace) {
            ownsSpace = false;
            if (releaseSpace) publish(DrawerEvents.DRAWER_EXCLUSIVE_RELEASED, {region: 'right'});
        }
        if (returnFocus && opener?.isConnected) opener.focus();
    };
    const showTopic = topic => {
        const article = [...panel.querySelectorAll('[data-coursefieldtext-help-article]')]
            .find(node => node.dataset.coursefieldtextHelpArticle === topic);
        if (!article) return;
        panel.querySelectorAll('article').forEach(node => { node.hidden = node !== article; });
        choice.value = topic;
        if (activeTopic !== topic) content.scrollTop = 0;
        activeTopic = topic;
    };
    const show = (topic, trigger) => {
        if (topic && !hasTopic(topic)) return;
        if (trigger) opener = trigger;
        // Keep focus inside Moodle's active modal when help is opened from an element form.
        const modal = document.querySelector('.modal.show');
        (modal || document.body).append(panel);
        panel.classList.toggle('certificateelement-coursefieldtext-help-in-modal', Boolean(modal));
        showTopic(topic || activeTopic || choice.value);
        position();
        if (panel.hidden) {
            if (exclusiveDrawers) {
                publish(DrawerEvents.DRAWER_EXCLUSIVE_REQUESTED, {region: 'right'});
                ownsSpace = true;
            }
            panel.hidden = false;
            document.body.classList.add('certificateelement-coursefieldtext-help-open');
        }
        document.querySelectorAll('[data-coursefieldtext-help-open]').forEach(button => button.setAttribute('aria-expanded', 'true'));
        (panel.querySelector('article:not([hidden]) h3') || heading).focus();
    };
    document.addEventListener('click', event => {
        const infoOpener = event.target.closest('.certificateelement-coursefieldtext-context-help-icon');
        if (infoOpener) lastInfoOpener = infoOpener;
        // Native information controls inside labels/disclosures must not change their host control.
        if (event.target.closest('.certificateelement-coursefieldtext-context-help-icon')?.closest('label, summary')) {
            event.preventDefault();
        }
        const close = event.target.closest('[data-coursefieldtext-help-close]');
        if (close) { hide(); return; }
        const button = event.target.closest('[data-coursefieldtext-help-open]');
        const link = event.target.closest('a[href]');
        let topic = '';
        if (link) {
            const url = new URL(link.href, window.location.href);
            if (url.origin === location.origin && url.pathname === helpUrl.pathname) {
                const requested = url.searchParams.get('coursefieldtexthelp') || '';
                if (hasTopic(requested)) topic = requested;
            }
        }
        if (!button && !topic) return;
        event.preventDefault();
        if (button && !panel.hidden) { hide(); return; }
        const popover = link?.closest('.popover');
        // Moodle 5.3 describes the popover body, rather than its outer wrapper.
        const describedId = popover?.querySelector('.popover-body')?.id || popover?.id;
        const icon = describedId
            ? document.querySelector(`.help-icon[aria-describedby="${CSS.escape(describedId)}"]`) : null;
        const trigger = button || icon || (lastInfoOpener?.isConnected ? lastInfoOpener : null) || document.querySelector('[data-coursefieldtext-help-open]');
        if (topic) {
            jQuery('.certificateelement-coursefieldtext-context-help-icon').popover('hide');
        }
        show(topic, trigger);
    });
    choice.addEventListener('change', () => showTopic(choice.value));
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || panel.hidden) return;
        const modal = document.querySelector('.modal.show, dialog[open]');
        if (modal && !modal.contains(panel)) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        hide();
    }, true);
    // Native tools win when explicitly reopened. Their DOM and input state are never replaced.
    document.addEventListener(Drawers.eventTypes.drawerShow, event => {
        if (event.target.classList.contains('drawer-right') && !panel.hidden) hide(false);
    });
    // A second overlay now owns the native exclusive request; do not release its claim.
    if (exclusiveDrawers) subscribe(DrawerEvents.DRAWER_EXCLUSIVE_REQUESTED, ({region} = {}) => {
        if (region === 'right' && ownsSpace) hide(false, false);
    });
    document.addEventListener('show.bs.modal', () => hide(false));
    jQuery(document).on('show.bs.modal', () => hide(false));
    // Moodle removes closed form modals; retain the page panel and release its open state.
    new MutationObserver(() => {
        if (!panel.isConnected) {
            hide(false);
            document.body.append(panel);
        }
    }).observe(document.body, {childList: true, subtree: true});
    window.addEventListener('resize', position);
    if (config.topic) show(config.topic);
};
