var Toolset_WCV = Toolset_WCV || {};

Toolset_WCV.Frontend = function( $ ) {

    var self = this;
    
    self.manageVariations = function( $container ) {
        if (
            typeof wc_add_to_cart_variation_params !== 'undefined'
            && $.isFunction( $.fn.wc_variation_form )
        ) {
            $container.find( '.variations_form' ).each( function() {
                $( this ).wc_variation_form();
            });
        }
        return self;
    };
    
    self.manageOnSale = function( $container ) {
        $container.find( '.woocommerce span.onsale' ).each( function() {
            if ( $( this ).closest( '.wcviews_onsale_wrap' ).length == 0 ) {
                $( this ).wrap( '<span class="wcviews_onsale_wrap" />' );
            }
        });
        return self;
    };
    
    self.manageStarRating = function( $container ) {
        $container.find( '.woocommerce .star-rating' ).addClass( 'wc_views_star_rating' );
        return self;
    };
    
    self.manageAjaxResults = function( $container ) {
        self.manageOnSale( $container )
            .manageStarRating( $container )
            .manageVariations( $container );
    };

    /**
     * Auxiliar method copied from the native WooCommerce variations script.
     *
     * @param string string
     * @return string
     */
    self.addSlashes = function( string ) {
        string = string.replace( /'/g, '\\\'' );
        string = string.replace( /"/g, '\\\"' );
        return string;
    };
    
    $( document ).on( 'js_event_wpv_pagination_completed', function( event, data ) {
        self.manageAjaxResults( data.layout );
    });

    $( document ).on( 'js_event_wpv_parametric_search_results_updated', function( event, data ) {
        self.manageAjaxResults( data.layout );
    });

    $( document ).on( 'woocommerce_update_variation_values', '.variations_form', function( event ) {
        // AFter initializing the variations, WooCommerce removes the "selected" attribute for the maybe selected default onw
        // so infinite scrolling fails to re-initialize variations properly on products already in previous pages.
        // This event lets us re-add the "selected" attribute.
        var $form = $( this ),
            $attributeFields = $form.find( '.variations select' );
        
        $attributeFields.each( function( index, el ) {
            var $currentAttrSelector = $( el ),
                currentAttrSelectorVal = $currentAttrSelector.val() || '';
            
            $currentAttrSelector.find( 'option.attached.enabled[value="' + self.addSlashes( currentAttrSelectorVal ) + '"]' ).attr( 'selected', 'selected' );
        });
    });
    
    self.init = function() {
        self.manageOnSale( $( document ) )
            .manageStarRating( $( document ) );
    };

    self.init();

};

jQuery( document ).ready( function( $ ) {
    Toolset_WCV.FrontendInstance = new Toolset_WCV.Frontend( $ );
});

/**
 * Callback that was forced after a View AJAX pagination event is completed!
 * We removed it from PHP, keep it for backwards compatibility.
 */
function wcviews_onsale_pagination_callback() {}