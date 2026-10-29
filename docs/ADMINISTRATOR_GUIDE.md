# Certificate Multitool: administrator guide

Certificate Multitool separates course wording from certificate layout. The Custom Field plugin stores editor-free text and per-course mode/alignment choices. The Cert. Element plugin reads a selected field and renders it through Certificate manager. Both plugins are licensed under GNU GPL v3 or later and require no product activation.

## Requirements and installation

The Custom Field plugin targets Moodle 4.5 through 5.3. It can be used without Certificate manager. The Cert. Element plugin also requires the paired Custom Field release and a host-appropriate Certificate manager (`tool_certificate`). The release metadata defines the exact minimum builds.

On Moodle 4.5, use the Certificate manager 4.5 branch (minimum 4.5.7). On Moodle 5.x, use the corresponding 5.x Certificate manager branch. The current upstream 5.0.11 release declares Moodle 5.0 through 5.2; the Multitool's own Moodle 5.3 integration checks do not extend upstream's support declaration. Check the upstream requirements before choosing a production combination.

Course HTML previews and automatic course-field export at issuance use the Course certificate activity (`mod_coursecertificate`). Install a version suitable for the host Moodle and Certificate manager. Other issuers must supply the corresponding exported course-field values themselves.

| Component | Directory relative to Moodle's web root |
| --- | --- |
| `customfield_multiline` | `customfield/field/multiline` |
| `certificateelement_coursefieldtext` | `admin/tool/certificate/element/coursefieldtext` |

On installations with a public directory, these plugin directories are inside public. Install Custom Field before Cert. Element and complete Moodle's normal plugin upgrade. Retain existing component identities and field short names when upgrading.

## Create the course fields

Open **Site administration > Courses > Course custom fields** and create a category for certificate content. Add fields of type **Certificate Multitool - Custom Field**. Typical roles are a heading, upper copy, a course title and lower copy, but the plugin does not require this particular set.

Use descriptive names and stable, unique short names. Select each field in its certificate element. A field does not automatically mirror the Moodle course name; course editors must enter the intended certificate title unless another integration supplies it.

Write field descriptions that explain the intended text length and presentation choices. For a single-line course title, explain the sequence: Plain text first, Limited HTML with small if needed, then shorten a title that still wraps. The allowed length depends on the actual font and width, not a universal character count.

### Visibility, locked fields and defaults

Use **Visible to: Nobody** for certificate-only values that should stay out of normal Moodle field display. A deliberately selected hidden field can still supply certificate text and appear in the authorised course HTML preview. Do not put confidential internal notes in a field selected for a certificate.

Keep **Locked: No** for fields course editors should maintain. Moodle's native permissions still control editing. Required and Unique data follow the native course-field settings.

A default is used until the course saves its own value. An explicitly cleared and saved field stays empty. Native course backup and restore preserve text, mode and alignment choices.

## Configure the certificate elements

Create or edit a Certificate manager template. Add **Certificate Multitool - Cert. Element** for each field used by the layout and give each element a descriptive name.

Select the Course field and a designer Preview course. The selected course must be editable by the user configuring the preview. It supplies sample content in the template editor; it does not choose the course for an issued certificate or replace the course activity's template assignment.

| Setting | Purpose |
| --- | --- |
| Font, Size and Colour | Base typography for the complete block. |
| Text alignment | Left, Centre or Right default; an explicit course choice overrides it. |
| Line height | Font-size factor from 0.5 to 3. Leave blank for native spacing. Small values can overlap. |
| Small text size (%) | Size of small content relative to the base font; 10 to 100%, default 60%. |
| Area height (mm) | Optional fixed height for vertical placement and overflow checks. |
| Vertical alignment | Top, Middle or Bottom default; an explicit course choice overrides it. |
| Position X / Position Y | Native reference position on the page. |
| Width | Available wrapping width. Zero uses the native automatic width. |

Use **Show more...** for the native position and width controls.

**Font-size unit-label error in tool_certificate:** some Certificate manager controls display mm beside Size. The font-size values are points (pt). A value of 28 means 28 pt. Page dimensions, positions, widths and area heights use mm.

## Reserve areas for variable-length content

With a positive Area height, Position Y is the area's top edge. Top starts there, Bottom ends at Y + Area height, and Middle centres the whole measured block between them. A one-line heading and a two-line heading can therefore share the same vertical midpoint.

The PDF renderer measures the actual HTML block with its fonts, wrapping and line height. Text that exceeds the area height, or an area outside the page, is rejected. Increase the area, adjust typography or shorten the content; the renderer does not silently clip it.

Without an Area height, Top starts at Y and Bottom ends at Y. Middle requires a positive height. Leave the height blank when you want this native anchor behaviour, for example for a deliberately single-line course title.

The designer shows a dashed box for a configured width and area height, including empty text. It is an editing aid and has no PDF border effect. An empty field leaves its space reserved; later elements do not move upward.

Plan the shortest and longest expected content before choosing the areas. Leave gaps around the participant name, title and date. Include list indentation and paragraph spacing in the checks. The course changes content inside an area, while the template defines the page geometry.

## Limited HTML and smaller text

The course chooses Plain text or Limited HTML for each value. The limited tag set is strong/b, em/i, u, small, ul/ol/li, br and p. Attributes, CSS and embedded media are removed. The raw course source is retained; certificate export uses the limited interpretation.

Small text can sit above or below the main heading in a single field:

```html
Certificate<br><small>Advanced Training</small>
```

The element's percentage applies to all small content. At a base size of 45 pt and 60%, the smaller text is 27 pt. Nested small tags do not compound the reduction. The whole block still follows its area height and vertical alignment.

For a single-line course title, choose a width and base font suitable for typical names. Tell editors to try Limited HTML with small around the entire title if it wraps, and to shorten it if that still needs a second line. This is a layout policy, not a restriction on every Multitool field.

## Standard participant name and date elements

Use Certificate manager's regular **User field** element with **Full name** for the recipient's name. Set its font, point size, colour, alignment, X/Y and width separately. It does not need a Multitool course field. Check a long name and leave room for wrapping where the design permits it.

Use an appropriate date element for the issue date, including Flex Date if installed. The course HTML preview shows recipient/date placeholders instead of reading a real recipient or issuing a certificate.

A dotted rule beneath a date can be a regular **Text** element containing period characters. In that design, edit its text, font size and position to change the visible line. It is a separate element from the date and from a drawn line or border.

## Connect the Course certificate activity

In each course, create or edit the Course certificate activity and select the saved template. The course preview reads this saved activity assignment. With one available activity it selects it automatically; several activities produce a selector.

The preview entry is **More > Certificate Preview**. It is supplied by Cert. Element; no patch to Certificate manager or Course certificate is required. Course editors need course-update permission and access to the relevant activity. Deliberately selected hidden fields remain usable within this authorised preview.

The preview uses current course text, native text/images/backgrounds/borders and supported course-field elements. Unknown element types use short bracketed placeholders with their configured position, width and alignment. They do not execute their own renderers. Remote/private images embedded in text are omitted; supported native raster images are handled separately.

The page fits within 90% of the available width and usable window height. Browser fonts and wrapping remain approximations. The preview does not issue a certificate, create a PDF or change the designer Preview course.

## Validation and later changes

Check representative course variations, including an empty field, short plain text, a longer block and a list. Review saved mode/alignment choices and the gaps around adjacent elements. Use an authorised final PDF preview to verify exact font wrapping, date output and long recipient names.

Explicit course choices override the matching element defaults. Inherited choices follow the template. New issues retain their exported text and explicit course choices. Rendering an existing issue reads its saved values, without falling back to current course text if a historical value is missing.

Stored PDFs stay unchanged until regenerated. Regeneration uses the saved issue content with the current template layout and inherited defaults. Consider this effect before editing a shared template. Changing a short name or selecting another field does not create missing historical values.

Existing values keep their Plain text or Limited HTML interpretation on upgrade. Older HTML values need an explicit Limited HTML save to enable small for future exports. This preserves the interpretation of existing certificates. No content migration is needed for this release.
