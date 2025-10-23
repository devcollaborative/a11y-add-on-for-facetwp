<?php

use FacetWP_Facet;

class FacetWP_Facet_Submit extends FacetWP_Facet {
    function __construct() {
        $this->label = __( 'Submit', 'fwp' );
    }

    /**
     * Display the facet front-end HTML
     */
    function render( $params ) {
        $classes = [ 'facetwp-submit' ];

        $output = '<button class="{classes}" type="submit" onclick="FWP.refresh()">{label}</button>';
        $output = str_replace( '{classes}', implode( ' ', $classes ), $output );
        $output = str_replace( '{label}', esc_attr( facetwp_i18n( $params['facet']['label'] ) ), $output );

        return $output;
    }
}