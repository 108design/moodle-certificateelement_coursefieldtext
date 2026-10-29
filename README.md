# Certificate Multitool – Cert. Element

Place a multiline course description on a shared Moodle certificate template. Course editors maintain one plain-text field; certificate designers control all typography, colour, width, position alignment and line height. Explicit line breaks and blank lines are preserved.

## Requirements and installation

This GPL beta targets Moodle 4.5 through 5.3. It requires a host-appropriate Certificate manager (`tool_certificate`) and **Certificate Multitool – Custom Field** (`customfield_multiline`) 0.8.0-beta or later. Use Certificate manager 4.5.7 or later on Moodle 4.5, and the appropriate 5.x branch on Moodle 5.x. Upstream Certificate manager 5.0.11 declares Moodle 5.0–5.2; the Multitool's own 5.3 checks do not extend upstream's support declaration. Install in `admin/tool/certificate/element/coursefieldtext` and complete Moodle's upgrade. Use the **Course certificate** activity (`mod_coursecertificate`) to issue course certificates with their exported course fields.

## Set up a template

1. Create a **Certificate Multitool – Custom Field** course custom field and enter the required lines in course settings.
2. Add **Certificate Multitool – Cert. Element** to your certificate template and select the course field.
3. Choose Font, Size, Colour, Text alignment, Width, Line height and position in the element. With centre alignment, Position X is the middle of the block. Width limits wrapping; all lines share the same base formatting. Line height is a factor from 0.5 to 3: try 1.0 for compact lines. Leave it empty to retain the renderer default. Decimal points and commas are accepted; values below 1 can overlap depending on the font.
4. Optionally select a **Preview course** that you may edit. This affects the designer and preview PDF only.

Native field visibility controls normal Moodle display. A certificate designer allowed to edit the preview course can deliberately use a hidden field. Certificates may include a hidden field's saved value, so select fields intended for recipients.

## Issued text

The element reads the selected field's exported text saved with the issue. Later course edits affect new issues. PDF regeneration reuses saved text; layout and field selection still follow the current template. Missing historical data displays empty. Changing a course field shortname requires selecting it again for future issues. Other issuing integrations must provide the same exported course-field data.

The integration uses native long-text storage for the generated Certificate manager mirrors of multiline course fields. Other field types are unchanged. No Moodle Core or Certificate manager source changes are required.

Plain text mode treats HTML literally. Enable **Limited HTML** for the field in each course’s settings to enter `strong`/`b`, `em`/`i`, `u`, `ul`/`ol` with `li`, `br` and `p` as source without an editor. Attributes, CSS and media are not retained. Paragraphs can add native PDF paragraph spacing; use `br` for consecutive lines controlled by Line height. The element controls base font, size, colour, width, alignment and line height; semantic emphasis is retained. The issue saves the content mode, so changing a course field mode does not reinterpret existing issues. Choose **Text alignment** separately for each course field: **Use template alignment** (default), **Left**, **Centre** or **Right**, for either text mode. An explicit choice aligns text inside the block positioned by the template. Set the block width and position, font and line height in the template once; the same template can serve different course alignments. List items retain their left-aligned hanging indents. New issues save the chosen mode and alignment with the text.

On upgrade, existing saved values retain their previous Plain text or Limited HTML interpretation and inherit template alignment. Existing issues keep their saved presentation. Defaults from earlier HTML-enabled fields keep their interpretation until a course saves its own choice.

**Vertical alignment** follows the same default/override model: choose **Inherit from template**, **Top**, **Middle** or **Bottom** for each field in course settings; the certificate element supplies a **Top**, **Middle** or **Bottom** default. Optionally set **Area height (mm)** in the element. Position Y is then the upper edge of the area: Top aligns text there, Bottom aligns it to Position Y plus the height. A one-line value and a multiline value can use the same reserved space. A dashed box in the certificate editor shows the configured width and area height, including for empty text; this guide does not appear in the PDF. The PDF renderer rejects text that does not fit rather than clipping it; enlarge the area or adjust text, width, font size or line height.

Leave Area height empty to preserve legacy anchoring: Top starts at Position Y and Bottom ends at Position Y. Existing elements have no area height, so their positions remain unchanged. Issued exports retain explicit course choices; inherited choices and layout follow the current template when PDFs are regenerated. Existing stored PDFs remain unchanged.

## Preview from a course

Course editors can open **More → Certificate Preview** in a course containing a saved Course certificate activity. Choose an activity when several exist; the preview reads its assigned template. It shows current course text and fields, backgrounds, borders and text areas in HTML. The whole page scales proportionally to fit within 90% of the available width and usable window height, keeping its layout intact without horizontal scrolling. Save course/template changes before refreshing. Recipient, date, code and other issue values use placeholders. The view creates no certificate or PDF and leaves the designer preview course unchanged. Browser fonts and wrapping may differ from the final PDF. Unknown elements and images embedded in text fields are marked as unavailable; native raster images and the optional Course Field Image element are supported. No changes to Course certificate or Certificate manager are needed.

Choose **Middle** for vertical centring within a fixed-height text area. The whole block is centred, so one-line and two-line titles share the same vertical midpoint. Set an Area height in the certificate element first; a missing height is reported instead of silently changing the alignment. Course choices override the element default in both text modes. Existing values and issued exports keep their previous choices.

## Smaller extra text

In course settings, choose **Limited HTML** and save text such as `<small>Advanced Training</small><br>Certificate`, or `Certificate<br><small>Advanced Training</small>`. **Small text size (%)** in the certificate element controls its size from 10 to 100 percent of the base font size (default 60%). At 45 pt, a 60% extra line is 27 pt. The same element can place the extra line above or below the main line; the entire block still uses the configured area height and vertical alignment. Nested small tags do not shrink further. User attributes and CSS remain removed. Requires Certificate Multitool - Cert. Element 0.8.0-beta and Custom Field 0.7.0-beta or later.

Existing saved Plain text and Limited HTML values keep their interpretation on upgrade, including old HTML that happened to contain small tags. Save a course value again explicitly in Limited HTML mode to enable small. Partial updates without a submitted text mode preserve the previous interpretation. New issues retain the selected HTML semantics; existing issues retain their saved interpretation. The percentage is a template layout setting used again when a PDF is regenerated.

Elements without HTML preview support are shown as `[element name]`, for example `[issued date]`, using their configured position, width, typography and alignment. Their actual renderers are not called and no certificate is issued.


## Guides

- [Course editor guide](docs/COURSE_EDITOR_GUIDE.md) ([PDF](docs/COURSE_EDITOR_GUIDE.pdf))
- [Administrator guide](docs/ADMINISTRATOR_GUIDE.md) ([PDF](docs/ADMINISTRATOR_GUIDE.pdf))

## Licence

GNU GPL v3 or later. See [LICENSE.md](LICENSE.md). No product activation is required.
