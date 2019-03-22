<?php
/*
Plugin Name: FacetWP - Single Handle Slider
Description: Select a max value with a single handle slide
Version: 0.1.0
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-slider
*/

defined( 'ABSPATH' ) or exit;

/**
 * FacetWP registration hook
 */
add_filter( 'facetwp_facet_types', function( $types ) {
    include( dirname( __FILE__ ) . '/class-single_slider.php' );
    $types['single_slider'] = new FacetWP_Facet_Single_Slider();
    return $types;
} );