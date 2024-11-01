jQuery( document ).ready( function( $ ) {

    jQuery('form fieldset').each(function(){
        var countryContainer = jQuery(this).find('.countryContainer');
        countryContainer.slideUp();
        jQuery(this).find('legend').append('<a class="icon-container"><span class="plus-icon"></span></a>');
        jQuery(this).find('.countryCount').text(countryCount(countryContainer));
        jQuery('#totalCountries').find('.counter').text(countryCount(jQuery('form')));
    });

    jQuery('form legend').on('click', function(e) {
        jQuery(this).toggleClass('active');
        jQuery(this).next().slideToggle();
    });

    jQuery('form input[type=checkbox]').on('click', function(e){
        jQuery(this).parent().toggleClass('checked');
        var parent = jQuery(this).parent().parent();
        jQuery(this).parent().parent().parent().find('.countryCount').text(countryCount(parent));
        jQuery('#totalCountries').find('.counter').text(countryCount(jQuery('form')));

    });

    function countryCount(elem){
        var totalCountries  = jQuery(elem).find('.country').length;
        var visitedCountries = jQuery(elem).find('.country.checked').length;
        return visitedCountries +' / '+totalCountries+' ('+Math.ceil((visitedCountries/totalCountries)*100)+'%)';
    }

});