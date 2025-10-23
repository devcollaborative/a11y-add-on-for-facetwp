<?php
/**
 * Plugin Name: A11y Add-on for FacetWP
 * Plugin URI: https://github.com/devcollaborative/A11y-Add-on-for-FacetWP
 * Description: Adds better a11y support to FacetWP plugin. Disable FacetWP's native "Load a11y support"
 * Version: 1.1.0
 * Requires at least: 6.4
 * Requires PHP: 8
 * Author: DevCollaborative
 * Author URI: https://devcollaborative.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) or exit;

define( 'A11Y_ADDON_VERSION', '1.1.0' );

/**
 * Load custom facet types
 */
function load_facets( $facet_types ) {
  // Load custom facets
  include( dirname( __FILE__ ) . '/facets/Submit.php' );
  $facet_types['submit'] = new FacetWP_Facet_Submit();

  // Remove facets that aren't yet accessible
  $disabled_facets = [
    'autocomplete', // Autocomplete dropdown is not focusable.
    'slider',       // Using min + max setting is not accessible. Needs further review.
    'date_range',   // Uses a date picker library that isn't accessible.
    'hierarchy',    // Not keyboard or screen reader accessible.
    'rating',       // Not keyboard or screen reader accessible.
    'fselect',      // Not keyboard or screen reader accessible.
    'number_range', // Multiple inputs are not accessible.
    'proximity',    // Missing labels, among other things.
  ];

  foreach( $disabled_facets as $facet ) {
    if( isset( $facet_types[ $facet ] ) ) {
      unset( $facet_types[ $facet ] );
    }
  }

  return $facet_types;
}
add_filter( 'facetwp_facet_types', 'load_facets' );

/**
 * Run plugin update process on activation.
 */
function a11y_addon_handbook_activate() {
	a11y_addon_update_check();
}
register_activation_hook( __FILE__, 'a11y_addon_handbook_activate' );


/**
 * Checks the current plugins version, and runs the update process if versions don't match.
 */
function a11y_addon_update_check() {
	if ( A11Y_ADDON_VERSION !== get_option( 'a11y_addon_version' ) ) {

		// Update with new plugin version.
		update_option( 'a11y_addon_version', A11Y_ADDON_VERSION );

		//flush anything? update anything?
	}
}
add_action( 'plugins_loaded', 'a11y_addon_update_check' );

/**
 * Adjusts markup for specific facets so they use real input elements
 * Adds matching label for and ids to all facets
 *
 * @param string $output HTML
 * @param array $params FacetWP field parameters
 *
 * @return string Updated HTML output for a facet
 *
 * @todo consider whether a combination of totally custom output and str_replace make sense or whether doing something with the WP HTML API might make more sense in the long term
 *
 * most of this courtesy of Mark Root-Wiley
 * @link https://github.com/mrwweb/accessibility-addon-for-facetwp
 */
function a11y_addon_transform_facet_markup( $output, $params ) {
  $facet_type = $params['facet']['type'];

  switch ( $facet_type )  {
    case 'checkboxes':
      // Note: The trick to this working was moving the facetwp-checkbox class and data-value attribute to the `input`.
      // Clicking the label works because the input element still emits a click event when the label is clicked.
      // Leaving that class and attribute on the wrapping list item resulted in two events being fired when the label was clicked.
      $output = '';
      foreach( $params['values'] as $value ) {
        $checkbox = sprintf(
            '<div class="facetwp-checkbox-wrapper %6$s">
              <input type="checkbox" id="%3$s"%1$s value="%2$s" class="facetwp-checkbox%1$s" %6$s data-value="%2$s">
              <label for="%3$s">
                <span class="facetwp-display-value">%4$s</span>
                <span class="facetwp-counter">(%5$d)</span>
              </label>
            </div>',
            in_array( $value['facet_value'], $params['selected_values'] ) ? ' checked' : '',
            esc_attr( $value['facet_value'] ),
            'checkbox-' . esc_attr( $value['term_id'] ),
            esc_html( $value['facet_display_value'] ),
            $value['counter'],
          $value['counter'] == 0 ? 'disabled' : ''
          );
        
        $output .= $checkbox;
      }
      break;

    case 'search':
      // remove the fake button
      $output = str_replace( '<i class="facetwp-icon"></i>', '', $output );

      // add label to search input
      $id = $params['facet']['name'];
      $output = str_replace( '<input', '<div class="trec-facetwp-search-wrapper"><input id="' . esc_attr( $id ) . '"', $output );

      // placeholders are bad for UX
      $output = str_replace( 'placeholder="Enter keywords"', '', $output );
      break;

    case 'dropdown':
      $output = str_replace( 'facetwp-dropdown', 'facetwp-dropdown a11y-addon-filter', $output );

      $id_string = 'id="' . $params['facet']['name'] . '" class=';

      $output = str_replace('class=', $id_string, $output);
      break;

    case 'reset':
      $output = str_replace('<button', '<button type="reset"', $output);
      break;

    default:

      $id_string = 'id="'.$params['facet']['name'].'" class=';

      $output = str_replace('class=', $id_string, $output);
      break;

    case 'pager':
      // Use nav element for pager
      $output = str_replace( '<div', '<nav aria-label="' . esc_html__( 'Pagination', 'aawp' ) . '"', $output );
      $output = str_replace( '</div>', '</nav>', $output );

      // Add role="presentation" to dots & active item so screen readers don't read them as links
      $output = str_replace( 'facetwp-page dots"', 'facetwp-page dots" role="presentation"', $output );
      $output = str_replace( 'facetwp-page active"', 'facetwp-page active" role="presentation"', $output );

      // Change page links to buttons
      $output = str_replace( 'a ', 'button ', $output );
      break;
  }

  return $output;
}

add_filter( 'facetwp_facet_html', 'a11y_addon_transform_facet_markup', 10, 2);


/**
 * Programatically add labels above filters
 * @link https://facetwp.com/add-labels-above-each-facet/
*/
function a11y_addon_add_facet_labels() {
  ?>
  <script>
  (function() {
    document.addEventListener('facetwp-loaded', function() {
      // Add labels
      var facets = document.querySelectorAll('.facetwp-facet');

      facets.forEach(function(facet) {
        var facet_name = facet.getAttribute('data-name');
        var facet_type = facet.getAttribute('data-type');

        if (facet_name && facet_type) {
          // Exclude some facets from getting labels
          if (facet_name.match(/pagination/g) ||
              facet_name.match(/reset/g) ||
              facet_name.match(/submit/g) ||
              facet_name.match(/results_count/g)) {
            return;
          }

          var facet_label = FWP.settings.labels[facet_name];

          if (!facet.closest('.facet-wrap') && !facet.closest('.facetwp-flyout')) {
            if (facet_type.match(/checkboxes/g) || facet_type.match(/radio/g)) {
              // Checkboxes & radio buttons need a fieldset/legend created here using role group & arialabelledby
              var wrapDiv = document.createElement('div');
              wrapDiv.className = 'facet-wrap facet-wrap-' + facet_name;
              wrapDiv.setAttribute('role', 'group');
              wrapDiv.setAttribute('aria-labelledby', facet_name + '_label');

              var labelDiv = document.createElement('div');
              labelDiv.id = facet_name + '_label';
              labelDiv.className = 'facet-label';
              labelDiv.textContent = facet_label;

              facet.parentNode.insertBefore(wrapDiv, facet);
              wrapDiv.appendChild(labelDiv);
              wrapDiv.appendChild(facet);
            } else {
              var wrapDiv = document.createElement('div');
              wrapDiv.className = 'facet-wrap facet-wrap-' + facet_name;

              var label = document.createElement('label');
              label.className = 'facet-label';
              label.setAttribute('for', facet_name.replace(/\s/g, '')); // remove spaces for id
              label.textContent = facet_label;

              facet.parentNode.insertBefore(wrapDiv, facet);
              wrapDiv.appendChild(label);
              wrapDiv.appendChild(facet);
            }
          }
        }
      });
    });
  })();
  </script>
  <?php
}

add_action( 'facetwp_scripts', 'a11y_addon_add_facet_labels', 100 );

/**
 * Submit form when enter key is pressed
 * @link https://facetwp.com/help-center/add-on-features-and-extras/submit-button#submit-on-enter
*/
function a11y_addon_add_facet_submit_form() {
  ?>
  <script>
    (function() {
      document.addEventListener('facetwp-loaded', function() {
        document.addEventListener('keyup', function(event) {
          if (event.keyCode === 13) {
            FWP.refresh()
          }
        });
      });
    })();
  </script>
  <?php
}
add_action( 'facetwp_scripts', 'a11y_addon_add_facet_submit_form', 100 );

/**
 * Hide counts in all dropdowns
 * @link https://facetwp.com/help-center/facets/facet-types/dropdown/
*/
function a11y_addon_hide_dropdown_counts( $return, $params ) {
	return false;
}
add_filter( 'facetwp_facet_dropdown_show_counts', 'a11y_addon_hide_dropdown_counts', 10, 2 );

/**
 * Disable auto-refresh when checkbox is clicked
 * @link https://facetwp.com/how-to-disable-facet-auto-refresh-and-add-a-submit-button/
*/
function a11y_addon_disable_auto_refresh() {
?>
	<script>
		(function($) {
			$(function() {
				if ('undefined' !== typeof FWP) {
					FWP.auto_refresh = false;
				}
			});
		})(fUtil);
	</script>
<?php
}
add_action( 'facetwp_scripts', 'a11y_addon_disable_auto_refresh', 100 );
