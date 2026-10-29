# Certificate Multitool: course editor guide

Certificate Multitool uses course fields to supply text to a shared certificate template. You edit the text, choose Plain text or Limited HTML, and select alignment where needed. The template sets the font, base size, colour, position and available space.

The two plugins work together:

| Plugin | Purpose |
| --- | --- |
| Certificate Multitool - Custom Field | Stores course text and its presentation choices without a rich-text editor. |
| Certificate Multitool - Cert. Element | Places a selected field on a Certificate manager template and provides the course HTML preview. |

Your administrator creates the fields and selects the certificate template in a Course certificate activity. Field names and the number of available fields depend on your site's setup.

## Edit the course values

1. Open the course and select **Settings**.
2. Find the custom fields used for certificate content.
3. Enter the text and choose its Text mode. Select alignment overrides only where you need them.
4. Select **Save and display**.
5. Open **More > Certificate Preview**. If several Course certificate activities are available, choose the intended activity and select **Show Preview**.

The preview reads saved values from the course and the template assigned to that activity. Use **Edit Course Values** to return to Settings, save the changes, then select **Refresh Preview**. Unsaved edits are not included.

If the fields or preview are missing, ask your administrator to check your course-editing permissions and access to the Course certificate activity.

## Plain text and Limited HTML

**Plain text** preserves hard line breaks and blank lines. Press Enter to begin another line. A long line can also wrap at the width set by the template. HTML tags appear as ordinary text in this mode.

**Limited HTML** accepts a small set of tags entered as source. Use `br` or `p` to create visible breaks; a newline in the HTML source alone does not create a new rendered line.

| Purpose | HTML |
| --- | --- |
| Bold | `<strong>Text</strong>` or `<b>Text</b>` |
| Italic | `<em>Text</em>` or `<i>Text</i>` |
| Underline | `<u>Text</u>` |
| Smaller text | `<small>Text</small>` |
| Line break | `<br>` |
| Paragraph | `<p>Text</p>` |
| Bullet list | `<ul><li>First item</li><li>Second item</li></ul>` |
| Numbered list | `<ol><li>First item</li><li>Second item</li></ol>` |

The field removes attributes, CSS, images and embedded content. Font and spacing choices belong to the template. Nesting small tags does not reduce the size again.

### Smaller heading text above or below the main line

Select Limited HTML and use either order in the same field:

```html
<small>Advanced Training</small><br>Certificate
```

```html
Certificate<br><small>Advanced Training</small>
```

The certificate element controls **Small text size (%)**. Its default is 60% of the base font size. For example, a base size of 45 pt gives 27 pt inside small at 60%. Course editors choose the text and its order; administrators choose the percentage.

### Keep a course title on one line when the layout requires it

For a template with a single-line course-title area, enter the title in Plain text without manual line breaks. If it wraps, choose Limited HTML and put the entire title inside small:

```html
<small>Course title</small>
```

Save and check the preview. If the smaller title still wraps, shorten the wording. Changing the course field does not increase the element's available width.

## Horizontal and vertical alignment

**Use template alignment** follows the element's horizontal setting. An explicit Left, Centre or Right selection in the course takes priority. For example, Left in the course overrides Centre in the element. The text stays inside the same positioned area.

**Inherit from template** follows the element's vertical setting. With a fixed Area height in the template:

| Choice | Placement |
| --- | --- |
| Top | Starts at the top of the reserved area. |
| Middle | Centres the whole block within the area, including mixed-size lines. |
| Bottom | Ends at the bottom of the area. |

Middle requires an Area height set by the administrator. Without a fixed height, Top starts at the element's Y position and Bottom ends at that position. Keep the template choice unless your administrator has designed the field for an override.

A short sentence that continues into the course title can use Bottom in an upper area. Standalone copy can use Middle, while a longer block can start at Top. A left-aligned HTML list and centred plain text can use the same lower area. These are typical combinations; the layout can support other variants within its available space.

## Lists and spacing

Choose Limited HTML for lists and use one li per item. Left alignment is usually easier to read for a list. Paragraphs and lists have their own spacing, and extra br tags consume more of the available height. Remove unnecessary breaks if the block becomes too tall.

The template's line-height factor applies to the block. Course values cannot move the area or change its height. Saving an empty field leaves the area blank and keeps later elements in their template positions.

## Check the result

Check the complete certificate after saving. Look for wrapped titles, missing lines, overlapping blocks and lower text too close to the date. Dotted outlines mark text areas and do not print in the PDF.

The HTML preview scales the page to fit within 90% of the available width and usable window height, whichever limits it first. It shows current course content. Recipient and issue-specific values use placeholders such as `[Participant name]` or `[issued date]`.

The preview creates no certificate or PDF. Browser fonts and wrapping may differ from the PDF, so ask an administrator for a final PDF check when precise spacing matters.

| Problem | Action |
| --- | --- |
| Old wording appears | Save the course values, then refresh the preview. |
| HTML tags are visible | Choose Limited HTML if those tags should be interpreted. |
| A single-line title wraps | Try small around the whole title, then shorten it if necessary. |
| A block is too tall | Remove unnecessary breaks or shorten it. Ask the administrator if the area needs changing. |
| An alignment choice has no usable area | Return to the template choice and ask the administrator to check the height. |
| No activity or preview is available | Ask the administrator to check the saved template assignment and your access. |

## Existing certificates

New issues use the text and explicit course choices saved at issuance. Existing issues retain their saved content. Stored PDFs stay unchanged until regenerated. A regenerated PDF uses the saved content with the current template layout and inherited defaults.

Existing Plain text and Limited HTML values keep their interpretation when the plugins are upgraded. To enable small for an older value, save that course value explicitly in Limited HTML mode. This does not rewrite certificates already issued.
