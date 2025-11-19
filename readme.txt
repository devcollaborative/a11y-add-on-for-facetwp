=== A11y-Add-on-for-FacetWP ===
Contributors: devcollab, hbrokmeier, cparkinson, mrwweb 
Tags: accessibility
Requires at least: 6.0
Tested up to: 6.7.2
Stable tag: 1.1.1
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

DevCollaborative's accessibility improvements for FacetWP Wordpress plugin

=== Description ===

A11y Add-on for FacetWP adds accessibility enhancements for FacetWP's form elements.

FacetWP must be installed and activated.

- Wraps all facets in a div with class=facet-wrap
- Adds a label to some facets with text=facet_label, for=facet_name
- Adds fieldset and legend to checkboxes and radio buttons
- Wrap pagination in nav tag, add screenreader h2
- Adds an id to each facet with id=facet_name
- FacetWP default checkbox markup replaced with semantic HTML checkboxes
- Search field uses &lt;search&gt; landmark, icon is removed
- Always hides counts in dropdowns
- Disables auto-refresh
- Customizes icon for prev/next pagination links
- Scroll back to top of results when pager is clicked
- Only accessible facet types are enabled: Checkboxes, Dropdown, Radio, Search, Pager, Reset, Sort
- Custom "Submit" facet type added


Semantic HTML checkboxes courtesy of Mark Root-Wiley, [MRW Web Design](https://mrwweb.com/) [Accessibility Addon for FacetWP](https://github.com/mrwweb/accessibility-addon-for-facetwp)

=== Changelog ===
= 1.1.1 = 
**[New]** Yahnis Elsts' git updater checker added

**Full Changelog**: https://github.com/devcollaborative/a11y-add-on-for-facetwp/compare/v1.1.0...v1.1.1

= 1.1.0 =
* Improved labels and markup
* **[New]** Adds a label to some facets with text=facet_label, for=facet_name
* **[New]** Adds fieldset and legend to checkboxes and radio buttons
* **[New]** Search field uses &lt;search&gt; landmark, icon is removed
 
**Full Changelog**: https://github.com/devcollaborative/a11y-add-on-for-facetwp/compare/v1.0.0...v1.1.0

= 1.0.0 = 
* Added: Accessibility improvements in html output for checkboxes, search box. All facets get labels, ids, and wrapper divs.

**Full Changelog**: https://github.com/devcollaborative/a11y-add-on-for-facetwp/commits/v1.0.0