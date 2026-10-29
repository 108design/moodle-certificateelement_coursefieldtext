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
$string['pluginname'] = 'Certificate Multitool – Cert. Element';
$string['elementname'] = 'Certificate Multitool – Cert. Element';
$string['choosefield'] = 'Choose a Certificate Multitool – Custom Field course field';
$string['missingfield'] = 'Missing course field: {$a}';
$string['coursefield'] = 'Course field';
$string['coursefield_help'] = 'Choose a Certificate Multitool – Custom Field course field. Enter the text in course settings. This element applies the template font, size, colour, width and text alignment to all lines.';
$string['previewcourse'] = 'Preview course';
$string['previewcourse_help'] = 'Optional course used only for the designer and preview PDF. You must be allowed to edit the course. Issued certificates use their saved course values, independently of this preview selection.';
$string['issuance'] = 'Issued text';
$string['issuancesummary'] = 'Issued certificates use the course text saved at issuance. ';
$string['issuance_help'] = 'Later course changes do not change the saved text of existing certificates. Missing saved values remain empty. ';
$string['privacy:metadata'] = 'Certificate values and recipient data are stored and processed by Certificate manager and the Moodle custom fields subsystem.';
$string['adminhelp'] = 'Help: Certificate Multitool – Cert. Element';
$string['adminhelptopics'] = 'Help Topics';
$string['adminhelpmore'] = 'More in the help panel';
$string['overview'] = 'Course field text';
$string['overview_help'] = 'Choose a multiline course field and set its presentation in the certificate template.';
$string['adminhelp_overview'] = 'Set up the text block';
$string['adminhelp_overview_article'] = '<p>Create a Certificate Multitool – Custom Field course field and enter the required lines in course settings. Add Certificate Multitool – Cert. Element to a certificate template and select that field.</p><p>Set Font, Size, Colour, Line height, Text alignment and Width in the element. With centre alignment, Position X is the middle of the text block. Width limits wrapping; a long line may wrap again. Leave Line height empty for the previous default or enter a font-size factor such as 1.0 for compact lines. Choose text mode and optional text alignment for each field in course settings. Use template alignment retains template alignment; an explicit course choice applies inside the same block. Width and position remain in the template; lists and emphasis are retained while HTML attributes are removed. With an Area height, Position Y marks the upper area edge and Top or Bottom aligns text inside that area. Without a height, Position Y remains the legacy upper or lower text anchor. An explicit course-field choice overrides this default. A Preview course lets you inspect a course you may edit. The dashed box in the certificate editor shows width and area height even when the text is empty; it is not included in the PDF. Middle vertically centres the entire text block within the fixed area. It requires an Area height in the certificate element.</p><p>Small text size (%) controls only text marked with small. At 60% and font size 45 it becomes 27. The order in the course field places the smaller text above or below the main line. Area height and Middle align the whole block. Save the course field again using Limited HTML to enable small.</p>';
$string['adminhelp_issuance'] = 'Saved certificate text';
$string['adminhelp_issuance_article'] = '<p>Use the Course certificate activity to pass course values to new issues. Other issuing integrations must supply the same exported course-field data. An issue without the selected saved value displays no text; it never reads the current course value as a fallback.</p><p>Changing course text affects new issues only. Regeneration reuses the saved text; layout and field selection still follow the current template. Changing a field shortname requires selecting it again for future issues. Existing PDFs remain unchanged until regenerated.</p>';

$string['lineheight'] = 'Line height';
$string['lineheight_help'] = 'Font-size factor from 0.5 to 3, for example 1.0 for compact lines or 1.25 for more spacing. Decimal points and commas are accepted. Leave empty to keep the PDF renderer default. Values below 1 may overlap depending on the font. Also applies to wrapped lines and lists.';
$string['invalidlineheight'] = 'Enter a factor between 0.5 and 3, or leave empty for the default.';

$string['verticalanchor'] = 'Vertical alignment';
$string['anchortop'] = 'Top';
$string['anchormiddle'] = 'Middle';
$string['anchorbottom'] = 'Bottom';
$string['verticalanchor_help'] = 'Default for courses using Inherit from template. With an area height, Top aligns text at Position Y and Bottom at Position Y plus area height. Without an area height, Top places the upper text edge at Position Y and Bottom places the lower text edge at Position Y. An explicit course selection overrides this default. Regenerated PDFs use the current template layout. Middle vertically centres the entire text block within the fixed area. It requires an Area height in the certificate element.';
$string['invalidanchor'] = 'Choose Top, Middle or Bottom.';
$string['textaboveedge'] = 'The text block does not fit above its bottom anchor on this page. Increase Position Y or width, or reduce font size or line height.';

$string['areaheight'] = 'Area height (mm)';
$string['areaheight_help'] = 'Optional text area in millimetres. Position Y always marks the top of this area. Top aligns text to its upper edge; Bottom aligns it to Position Y plus the area height. The course selection overrides the element default. Leave blank to retain legacy behaviour: Top or Bottom refers directly to Position Y. PDF generation rejects text taller than the area with an error; enlarge the area or adjust text, width, font size or line height. In the certificate editor, a dashed box shows the configured width and area height, even when the text is empty. It does not appear in the PDF. Middle distributes remaining space equally above and below the text block.';
$string['invalidareaheight'] = 'Enter a positive area height in millimetres or leave blank for legacy anchoring.';
$string['areaoutsidepage'] = 'The entire text area must fit on the certificate page. Adjust Position Y or the area height.';
$string['textoutsidearea'] = 'The text does not fit inside the configured area. Enlarge the area or adjust text, width, font size or line height.';

$string['coursepreview'] = 'Certificate Preview';
$string['invalidpreviewactivity'] = 'Choose an available Course certificate activity from this course.';
$string['previewsummary'] = 'HTML layout preview using saved course values. Fonts and wrapping may differ from the PDF. Recipient, date, code and other issue-specific values are placeholders. Dotted lines mark text areas.';
$string['previewactivity'] = 'Course certificate activity';
$string['previewactivity_help'] = 'You need course editing rights and access to the activity. Choose a saved Course certificate activity in this course. Its assigned template is used for the preview. Save changes to course values or the template selection first, then reload the preview.';
$string['choosepreviewactivity'] = 'Choose an activity';
$string['showpreview'] = 'Show Preview';
$string['refreshpreview'] = 'Refresh Preview';
$string['editpreviewcourse'] = 'Edit Course Values';
$string['previewnoactivities'] = 'This course has no available Course certificate activity. Check activity visibility and access permissions, or create an activity with a certificate template.';
$string['previewperson'] = '[Participant name]';
$string['previewdate'] = '[Issue date]';
$string['previewcode'] = '[Certificate code]';
$string['previewdynamic'] = '[Issue-specific value]';
$string['previewmissingimage'] = '[Image unavailable in preview]';
$string['previewunsupported'] = '[{$a}]';
$string['preview'] = 'About the HTML preview';
$string['preview_help'] = 'This preview shows the current course layout without issuing a certificate. Stored certificates are unchanged.';
$string['adminhelp_preview'] = 'Check a Course Certificate';
$string['adminhelp_preview_article'] = '<p>Open Certificate Preview under More in the course. If the course has several Course certificate activities, choose one and select Show Preview. Only its saved template assignment is used. A single available activity is previewed directly.</p><p>Edit Course Values opens course settings. Save the text, mode and alignment there, then return and select Refresh Preview. Template and activity changes must also be saved first.</p><p>The view is read-only and creates neither issues nor PDFs. The course name and course fields use current values, including intentionally hidden certificate fields. The designer preview course selection is unchanged. Recipient, date, code and other issue values remain placeholders; unavailable and third-party elements are marked.</p><p>Dotted lines mark reserved text areas. The complete certificate page is scaled proportionally to at most 90% of the container width and usable window height. The first limit reached determines the size; line breaks and spacing are preserved. Browser fonts, wrapping and spacing may differ; use a PDF to check the final output. The preview supports static text, course data, multiline fields, borders, native raster images and the optional course-image element. Images embedded in text fields and other media use placeholders.</p>';

$string['middleneedsarea'] = 'Set an Area height in the certificate element to vertically centre the text.';

$string['smalltextsize'] = 'Small text size (%)';
$string['smalltextsize_help'] = 'Size for text marked with small in HTML mode: 10 to 100 percent of this element font size, default 60. For example, 45 at 60% becomes 27. Enter &lt;small&gt;Extra text&lt;/small&gt;&lt;br&gt;Certificate in the course field, or reverse the order, and save using Limited HTML. Nested small does not shrink further. Position, area height and vertical alignment still apply to the whole block. Previously saved HTML enables small only after saving again in HTML mode; existing issues retain their interpretation.';
$string['invalidsmalltextsize'] = 'Enter a percentage from 10 to 100, or leave empty for 60%.';
