<?php
/**
 * Plugin Name: A11y Add-on for FacetWP
 * Plugin URI: https://github.com/devcollaborative/A11y-Add-on-for-FacetWP
 * Description: Adds better a11y support to FacetWP plugin
 * Version: 1.0
 * Requires at least: 6.4
 * Requires PHP: 8
 * Author: DevCollaborative
 * Author URI: https://devcollaborative.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) or exit;

define( 'A11Y_ADDON_VERSION', '2.0.2' );

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
 * Enqueue jQuery because FacetWP just assumes it's enqueued.
*/
function a11y_addon_facet_assets() {
  wp_enqueue_script('jquery');
}
add_action( 'wp_enqueue_scripts', 'a11y_addon_facet_assets' );


/**
 * Add class to all filters
 * @link https://facetwp.com/documentation/developers/output/facetwp_facet_html/
*/
function a11y_addon_add_facet_class( $output, $params ){
  if ( 'dropdown' == $params['facet']['type'] ) {
    $output = str_replace( 'facetwp-dropdown', 'facetwp-dropdown a11y-addon-filter', $output );
	}

	$label = str_replace( ' ','', $params['facet']['label'] );
	$id_string = 'id="'.$label.'" class=';
    $output = str_replace('class=', $id_string, $output);

    return $output;
}

add_filter( 'facetwp_facet_html', 'a11y_addon_add_facet_class', 20, 2);

/**
 * Adjusts markup for specific facets so they use real input elements
 *
 * @param string $output HTML
 * @param array $params FacetWP field parameters
 * 
 * @return string Updated HTML output for a facet
 * 
 * @todo consider whether a combination of totally custom output and str_replace make sense or whether doing something with the WP HTML API might make more sense in the long term
 * 
 * courtesy of Mark Root-Wiley
 * @link https://github.com/mrwweb/accessibility-addon-for-facetwp
 */
function a11y_addon_transform_facet_markup( $output, $params ) {

  $facet_type = $params['facet']['type'];

  switch ($facet_type) {
  
    case 'checkboxes':
      // Note: The trick to this working was moving the facetwp-checkbox class and data-value attribute to the `input`. Clicking the label works because the input element still emits a click event when the label is clicked. Leaving that class and attribute on the wrapping list item resulted in two events being fired when the label was clicked.
      $output = '';
      $output .= '<ul>';
      foreach( $params['values'] as $value ) {
        if( $value['counter'] > 0 || ! $params['facet']['preserve_ghosts'] === 'no' ) {
          $output .= sprintf(
            '<li>
              <input type="checkbox" id="%3$s"%1$s value="%2$s" class="facetwp-checkbox%1$s" data-value="%2$s">
              <label for="%3$s">
                <span class="facetwp-display-value">%4$s</span>
                <span class="facetwp-counter">(%5$d)</span>
              </label>
            </li>',
            in_array( $value['facet_value'], $params['selected_values'] ) ? ' checked' : '',
            esc_attr( $value['facet_value'] ),
            'checkbox-' . esc_attr( $value['term_id'] ),
            esc_html( $value['facet_display_value'] ),
            $value['counter']
          );
        }
      }
      $output .= '</ul>';
      break;

    case 'search':
      // use search landmark and insert a real button
      $output = '<search>' . $output . '</search>';
      // remove the fake button
      $output = str_replace( '<i class="facetwp-icon"></i>', '', $output );
      // add label to search input
      $id = $params['facet']['name'];
      $output = str_replace( 
        '<input', '<div class="trec-facetwp-search-wrapper"><input id="' . esc_attr( $id ) . '"', $output );
      // facetwp-icon class is important to retain event handling
      $output = str_replace( '</span>', '<button class="facetwp-icon"><span class="ally-addon-search-submit-text">Submit</span></button></div></span>', $output );
      // placeholders are bad for UX
      $output = str_replace( 'placeholder="Enter keywords"', '', $output );
      break;
    
    /*
    //do we want this?
    case 'pager':            
      // put links in a list with nav element
      $output = str_replace( '<div', '<nav aria-labelledby="resource-paging-heading"><h3 class="screen-reader-text" id="resource-paging-heading">' . esc_html__( 'Results Pages', 'afwp' ) . '</h3><ul', $output );
      $output = str_replace( '</div>', '</ul></nav>', $output );
      $output = str_replace( '<a', '<li><a', $output );
      $output = str_replace( '</a>', '</a></li>', $output );
      // add tabindex to valid links only for keyboard accessibility
      $output = str_replace( 'data-page', 'tabindex="0" data-page', $output );
      break;
    */

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
  (function($) {

    function remove_underscores( name ) {
        return name.replace(/_/g, ' ');
    }

    // Make enter & space work for links
    $(document).on('keydown', '.facetwp-page, .facetwp-toggle, .facetwp-selection-value', function(e) {
        var keyCode = e.originalEvent.keyCode;
        if ( 32 == keyCode || 13 == keyCode ) {
            e.preventDefault();
            $(this).click();
        }
    });

    $(document).on('keydown', '.facetwp-checkbox, .facetwp-radio', function(e) {
        var keyCode = e.originalEvent.keyCode;
        if ( 32 == keyCode || 13 == keyCode ) {
          var is_checked = $(this).hasClass('checked');

          if (!is_checked) {
            $(this).attr('aria-checked', 'true');
          } else {
            $(this).attr('aria-checked', 'false');
          }

          e.preventDefault();
          $(this).click();
        }
    });

    $(document).on('facetwp-loaded', function() {
      $('.facetwp-checkbox, .facetwp-radio').each(function() {
        $(this).attr('role', 'checkbox');
        $(this).attr('aria-checked', $(this).hasClass('checked') ? 'true' : 'false');
        $(this).attr('tabindex', 0);
      });

      $('.facetwp-type-checkboxes').each(function() {
          $(this).attr('aria-label', remove_underscores($(this).data('name')));
          $(this).attr('role', 'group' );
      });

      // pager
      $('.facetwp-pager').attr('role', 'navigation');
      $('.facetwp-page').each(function(e) {
          let $el = $(this);
          $el.attr('role', 'button');
          $el.attr('tabindex', 0);
      });


      // Add labels
      $('.facetwp-facet').each(function() {
        var $facet = $(this);
        var facet_name = $facet.attr('data-name');
        var facet_type = $facet.attr('data-type');

        console.log(facet_name);

        if ( facet_name && facet_type ) {
          // Don't label the pagination or reset
          if ( facet_name.match(/pagination/g) ||
              facet_name.match(/reset/g) ||
              facet_name.match(/results_count/g) ) {
            return;
          }

          var facet_label = FWP.settings.labels[facet_name];

          if ($facet.closest('.facet-wrap').length < 1 && $facet.closest('.facetwp-flyout').length < 1) {
            $facet.wrap(`<div class="facet-wrap facet-wrap-${facet_name}"></div>`);

            if ( facet_type.match(/checkboxes/g) || facet_type.match(/radio/g) ){
              // Checkboxes & radio buttons don't need a <label> element, facetWP adds aria-label to them.
              $facet.before('<div class="facet-label" aria-hidden="true">' + facet_label + '</div>');
            } else {
              $facet.before('<label class="facet-label" for="'+facet_label.replace(/\s/g, '')+'">' + facet_label + '</label>');
            }
          }
        }
      });
    });
  })(jQuery);
  </script>
  <?php
}

add_action( 'wp_head', 'a11y_addon_add_facet_labels', 100 );

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

// Customize icon for prev/next pagination links.
function fwp_facetwp_facet_pager_link($html, $params) {
  if ( 'next' == $params['extra_class'] ) {
    $icon = 'Next <svg class="icon" aria-hidden="true"><use xlink:href="#caret-right"/></svg></svg>';
    $html = str_replace( 'Next', $icon, $html );
  }

  if ( 'prev' == $params['extra_class'] ) {
    $icon = '<svg class="icon" aria-hidden="true"><use xlink:href="#caret-left"/></svg></svg> Prev';
    $html = str_replace( 'Prev', $icon, $html );
  }

  return $html;
}
add_action( 'facetwp_facet_pager_link', 'a11y_addon_facetwp_facet_pager_link', 100, 2 );

/**
* Scroll back to top of results when pager is clicked
* @link https://facetwp.com/how-to-scroll-the-page-on-facet-interaction/#scroll-when-a-pager-facet-is-used
**/

function a11y_addon_scroll_on_pager_interaction() {
?>
  <script>
    (function($) {
      $(document).on('facetwp-refresh', function() {
          if ( FWP.soft_refresh == true )  {
            FWP.enable_scroll = true;
          } else {
            FWP.enable_scroll = false;
          }
      });
      $(document).on('facetwp-loaded', function() {
        if (FWP.enable_scroll == true) {
          $('html, body').animate({
            scrollTop: $('.facetwp-template').offset().top
          }, 500);
        }
      });
    })(jQuery);
  </script>
<?php
}
add_action( 'facetwp_scripts', 'a11y_addon_scroll_on_pager_interaction' );