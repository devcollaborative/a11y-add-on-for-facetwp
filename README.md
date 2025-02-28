# A11y-Add-on-for-FacetWP

DevCollaborative's accessibility improvements for FacetWP Wordpress plugin

## Description

A11y Add-on for FacetWP adds accessibility enhancements for FacetWP's form elements.

FacetWP must be installed and activated.

- Wraps all facets in a div with class=facet-wrap
- Adds a label to some facets with text=facet_label, for=facet_name
- Adds fieldset and legend to checkboxes and radio buttons
- Adds an id to each facet with id=facet_name
- FacetWP default checkbox markup replaced with semantic HTML checkboxes
- Search field uses &lt;search&gt; landmark, icon is removed
- Always hides counts in dropdowns
- Disables auto-refresh
- Customizes icon for prev/next pagination links
- Scroll back to top of results when pager is clicked

## Search button
We add the Submit button as a Custom HTML block:
````
<button id="search" onclick="FWP.refresh()" class="facetsubmit" type="submit">Filter</button>
```

Semantic HTML checkboxes courtesy of Mark Root-Wiley, [MRW Web Design](https://mrwweb.com/) [Accessibility Addon for FacetWP](https://github.com/mrwweb/accessibility-addon-for-facetwp)
