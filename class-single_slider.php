<?php

class FacetWP_Facet_Single_Slider extends FacetWP_Facet {

    function __construct() {
        $this->label = __( 'Single Slider', 'fwp' );
    }

    /**
     * Generate the facet HTML
     */
    function render( $params ) {
        $output = '<div class="facetwp-single_slider-wrap">';
        $output .= '<div class="facetwp-single_slider"></div>';
        $output .= '</div>';
        $output .= '<span class="facetwp-single_slider-label"></span>';
        $output .= '<div><input type="button" class="facetwp-single_slider-reset" value="' . __( 'Reset', 'fwp-front' ) . '" /></div>';
        return $output;
    }

    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $value = $params['selected_values'];
        $value = is_array( $value ) ? $value[0] : $value;
        $where = '';

        $end = ( '' == $value ) ? false : $value;

        $compare = $facet['compare'];

        if ( ! in_array( $compare, array( '<', '<=', '=', '>=', '>' ), true ) ) {
            $compare = '<';
        }

        if ( false !== $end ) {
            $where .= " AND (facet_display_value + 0) $compare '$end'";
        }

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' $where";
        return facetwp_sql( $sql, $facet );
    }

    /**
     * (Front-end) Attach settings to the AJAX response
     */
    function settings_js( $params ) {
        global $wpdb;

        $facet          = $params['facet'];
        $where_clause   = $params['where_clause'];
        $selected_value = $params['selected_values'];
        $selected_value = is_array( $selected_value ) ? $selected_value[0] : $selected_value;

        // Set default slider values
        $defaults = [
            'format' => '',
            'prefix' => '',
            'suffix' => '',
            'step'   => 1,
        ];

        $facet = array_merge( $defaults, $facet );

        $sql = "
        SELECT MIN(facet_value + 0) AS `min`, MAX(facet_display_value + 0) AS `max` FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' AND facet_display_value != '' $where_clause";
        $row = $wpdb->get_row( $sql );

        return [
            'range'               => [
                'min' => (float) $selected_value,
                'max' => (float) $row->max,
            ],
            'decimal_separator'   => FWP()->helper->get_setting( 'decimal_separator' ),
            'thousands_separator' => FWP()->helper->get_setting( 'thousands_separator' ),
            'start'               => [ $row->max ],
            'format'              => $facet['format'],
            'prefix'              => $facet['prefix'],
            'suffix'              => $facet['suffix'],
            'step'                => $facet['step']
        ];
    }

    /**
     * (Admin) Output settings HTML
     */
    function settings_html() {
        $thousands = FWP()->helper->get_setting( 'thousands_separator' );
        $decimal   = FWP()->helper->get_setting( 'decimal_separator' );
        ?>
        <div class="facetwp-row">
            <div>
                <?php _e( 'Prefix', 'fwp' ); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Text that appears before each slider value', 'fwp' ); ?></div>
                </div>
            </div>
            <div><input type="text" class="facet-prefix"/></div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e( 'Suffix', 'fwp' ); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Text that appears after each slider value', 'fwp' ); ?></div>
                </div>
            </div>
            <div><input type="text" class="facet-suffix"/></div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e( 'Format', 'fwp' ); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'The number format', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <select class="facet-format">
                    <?php if ( '' != $thousands ) : ?>
                        <option value="0,0">5<?php echo $thousands; ?>280</option>
                        <option value="0,0.0">5<?php echo $thousands; ?>280<?php echo $decimal; ?>4</option>
                        <option value="0,0.00">5<?php echo $thousands; ?>280<?php echo $decimal; ?>42</option>
                    <?php endif; ?>
                    <option value="0">5280</option>
                    <option value="0.0">5280<?php echo $decimal; ?>4</option>
                    <option value="0.00">5280<?php echo $decimal; ?>42</option>
                    <option value="0a">5k</option>
                    <option value="0.0a">5<?php echo $decimal; ?>3k</option>
                    <option value="0.00a">5<?php echo $decimal; ?>28k</option>
                </select>
            </div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e( 'Comparison method', 'fwp' ); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'The comparison method', 'fwp' ); ?></div>
                </div>
                <div>
                    <select class="facet-compare">
                        <option value="<">Lower than</option>
                        <option value="<=">Lower than or equal</option>
                        <option value="=">Equal</option>
                        <option value=">=">Greater than or equal</option>
                        <option value=">">Greater than</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e( 'Step', 'fwp' ); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'The amount of increase between intervals', 'fwp' ); ?> (default = 1)</div>
                </div>
            </div>
            <div><input type="text" class="facet-step" value="1"/></div>
        </div>

    <?php }

    function front_scripts() {
        FWP()->display->assets['nouislider.css'] = FACETWP_URL . '/assets/vendor/noUiSlider/nouislider.css';
        FWP()->display->assets['nouislider.js']  = FACETWP_URL . '/assets/vendor/noUiSlider/nouislider.min.js';
        FWP()->display->assets['nummy.js']       = FACETWP_URL . '/assets/js/src/nummy.js';
        ?>
        <script>
            (function ($) {
                /* ======== Slider ======== */

                FWP.hooks.addAction('facetwp/refresh/single_slider', function ($this, facet_name) {
                    FWP.facets[facet_name] = [];

                    // settings have already been loaded
                    if ('undefined' !== typeof FWP.frozen_facets[facet_name]) {
                        if ('undefined' !== typeof $this.find('.facetwp-single_slider')[0].noUiSlider) {
                            FWP.facets[facet_name] = $this.find('.facetwp-single_slider')[0].noUiSlider.get();
                        }
                    }
                });

                FWP.hooks.addAction('facetwp/set_label/single_slider', function ($this) {
                    var facet_name = $this.attr('data-name');
                    var min = FWP.settings[facet_name]['lower'];
                    var max = FWP.settings[facet_name]['upper'];
                    var format = FWP.settings[facet_name]['format'];
                    var opts = {
                        decimal_separator: FWP.settings[facet_name]['decimal_separator'],
                        thousands_separator: FWP.settings[facet_name]['thousands_separator']
                    };

                    var label = FWP.settings[facet_name]['prefix']
                        + nummy(max).format(format, opts)
                        + FWP.settings[facet_name]['suffix'];

                    $this.find('.facetwp-single_slider-label').html(label);
                });

                FWP.hooks.addFilter('facetwp/selections/single_slider', function (output, params) {
                    return params.el.find('.facetwp-single_slider-label').text();
                });

                $(document).on('facetwp-loaded', function () {
                    $('.facetwp-type-single_slider .facetwp-single_slider:not(.ready)').each(function () {
                        var $parent = $(this).closest('.facetwp-facet');
                        var facet_name = $parent.attr('data-name');
                        var opts = FWP.settings[facet_name];

                        // on first load, check for slider URL variable
                        if (false !== FWP.helper.get_url_var(facet_name)) {
                            FWP.frozen_facets[facet_name] = 'hard';
                        }

                        // fail on slider already initialized
                        if ('undefined' !== typeof $(this).data('options')) {
                            return;
                        }

                        // fail if start values are null
                        if (null === FWP.settings[facet_name].start[0]) {
                            return;
                        }

                        // fail on invalid ranges
                        if (parseFloat(opts.range.min) >= parseFloat(opts.range.max)) {
                            FWP.settings[facet_name]['lower'] = opts.range.min;
                            FWP.settings[facet_name]['upper'] = opts.range.max;
                            FWP.hooks.doAction('facetwp/set_label/single_slider', $parent);
                            return;
                        }

                        // custom slider options
                        var single_slider_opts = FWP.hooks.applyFilters('facetwp/set_options/single_slider', {
                            range: opts.range,
                            start: opts.start,
                            step: parseFloat(opts.step),
                            connect: true
                        }, {'facet_name': facet_name});


                        var single_slider = $(this)[0];
                        noUiSlider.create(single_slider, single_slider_opts);
                        single_slider.noUiSlider.on('update', function (values, handle) {
                            FWP.settings[facet_name]['lower'] = 0;
                            FWP.settings[facet_name]['upper'] = values[0];
                            FWP.hooks.doAction('facetwp/set_label/single_slider', $parent);
                        });
                        single_slider.noUiSlider.on('set', function () {
                            FWP.frozen_facets[facet_name] = 'hard';
                            FWP.autoload();
                        });

                        $(this).addClass('ready');
                    });

                    // hide reset buttons
                    $('.facetwp-type-single_slider').each(function () {
                        var name = $(this).attr('data-name');
                        var $button = $(this).find('.facetwp-single_slider-reset');
                        $.isEmptyObject(FWP.facets[name]) ? $button.hide() : $button.show();
                    });

                    $(document).on('click', '.facetwp-type-single_slider .facetwp-single_slider-reset', function () {
                        var facet_name = $(this).closest('.facetwp-facet').attr('data-name');
                        FWP.reset(facet_name);
                    });

                });

            })(jQuery);
        </script>
        <?php
    }
}
