<?php

/*
Plugin Name: WP Places I Have Been
Plugin URI: http://benwatson.uk/WP-Places-I-Have-Been
Description: An easy way to display the flags of the countries that you have visited on your Wordpress site.
Version: 1.0
Author: Ben Watson
Author URI: http://benwatson.uk
License: GPL2
*/

//Create shortcode
add_shortcode( 'Places_I_Have_Been', 'wp_pihb_show_places_i_have_been');

//Add scripts and styles to admin section
add_action( 'admin_enqueue_scripts', 'wp_pihb_enqueue' );

function wp_pihb_enqueue($hook) {
	if ( 'toplevel_page_wp_pihb_places_i_have_been_settings' != $hook && 'places-i-have-been_page_wp_pihb_places_i_have_been_output_settings' != $hook ) {
		return;
	}
	wp_enqueue_style( 'wp_pihb_places_i_have_been_styles', plugins_url("dist/main.css", __FILE__));
	wp_enqueue_script( 'wp_pihb_places_i_have_been_script', plugins_url("dist/main.js", __FILE__), 'jQuery');
}

//Add styles to front end
add_action( 'wp_enqueue_scripts', 'wp_pihb_places_i_have_been_styles' );

function wp_pihb_places_i_have_been_styles() {
	wp_enqueue_style( 'wp_pihb_places_i_have_been_styles', plugins_url("dist/places_i_have_been.css", __FILE__));
}

// Add menu item to admin section
add_action('admin_menu', 'wp_pihb_places_i_have_been_menu');

function wp_pihb_places_i_have_been_menu() {
	add_menu_page('Places I Have Been', 'Places I Have Been', 'administrator', 'wp_pihb_places_i_have_been_settings', 'wp_pihb_places_i_have_been_settings_page', 'dashicons-flag');
	add_submenu_page( 'wp_pihb_places_i_have_been_settings', 'Places I Have Been - Manage Countries', 'Manage Countries', 'manage_options', 'wp_pihb_places_i_have_been_settings');
	add_submenu_page( 'wp_pihb_places_i_have_been_settings', 'Places I Have Been - Manage Display Settings', 'Display settings', 'manage_options', 'wp_pihb_places_i_have_been_output_settings', 'wp_pihb_places_i_have_been_output_settings_page');
}

//Add plugin settings
add_action( 'admin_init', 'wp_pihb_places_i_have_been_settings' );

function wp_pihb_places_i_have_been_settings() {
	register_setting( 'wp_pihb_places_i_have_been_settings_group', 'wp_places_i_have_been' );
	register_setting( 'wp_pihb_places_i_have_been_settings_group_display', 'wp_places_i_have_been_display_settings' );
}

//The main settings page in the admin section
function wp_pihb_places_i_have_been_settings_page() { ?>
	<div class="wrap">
	<h2>Which countries have you been to?</h2>
	<span id="totalCountries">You have been to <span class="counter"></span> countries!</span>
	<form method="post" action="options.php">
    <?php settings_fields( 'wp_pihb_places_i_have_been_settings_group' ); ?>
	<?php do_settings_sections( 'wp_pihb_places_i_have_been_settings_group' ); ?>
	<?php
        $theData = theRegions();
		$theExistingData = maybe_unserialize(get_option('wp_places_i_have_been'));
		echo wp_pihb_region_structure('AS', $theData['AS'], $theExistingData);
		echo wp_pihb_region_structure('EU', $theData['EU'], $theExistingData);
		echo wp_pihb_region_structure('AF', $theData['AF'], $theExistingData);
		echo wp_pihb_region_structure('OC', $theData['OC'], $theExistingData);
		echo wp_pihb_region_structure('NA', $theData['NA'], $theExistingData);
		echo wp_pihb_region_structure('SA', $theData['SA'], $theExistingData);
		echo wp_pihb_region_structure('AN', $theData['AN'], $theExistingData);

    ?>

	<?php submit_button(); ?>

	</form>
	</div>
<?php }

//The output settings page in the admin section
function wp_pihb_places_i_have_been_output_settings_page() { ?>
	<div class="wrap">
		<h2>How many flags do you want to display per row on your website?</h2>

		<form method="post" action="options.php">
			<?php settings_fields( 'wp_pihb_places_i_have_been_settings_group_display' ); ?>
			<?php do_settings_sections( 'wp_pihb_places_i_have_been_settings_group_display' );
			$theExistingData = maybe_unserialize(get_option('wp_places_i_have_been_display_settings'));

			//List current style options
			$styleOptions = [];
			$styleOptions['onePerRow']      = 1;
			$styleOptions['twoPerRow']      = 2;
			$styleOptions['threePerRow']    = 3;
			$styleOptions['fourPerRow']     = 4;
			$styleOptions['fivePerRow']     = 5;
			$styleOptions['sixPerRow']      = 6;
			$styleOptions['tenPerRow']      = 10;
			$styleOptions['twentyPerRow']   = 20;

			foreach ($styleOptions as $k => $v) {
				($theExistingData == $k) ? $selected = 'checked="checked"' : $selected = '';
				echo '<label for="'.$k.'" class="flagOptions"><input type="radio" value="'.$k.'" name="wp_places_i_have_been_display_settings" id="'.$k.'" '.$selected.'/>'.$v.'</label>';
			}
			submit_button(); ?>

		</form>
	</div>
<?php }

/*--Functions to handle the data--*/
// Organise region and country data into a usable object
function theRegions() {
	$countriesJSON = file_get_contents("data/countries.json", FILE_USE_INCLUDE_PATH);
	$regionsJSON = file_get_contents("data/regions.json", FILE_USE_INCLUDE_PATH);
	$regions = json_decode($regionsJSON, true);
	$countries = json_decode($countriesJSON, true);
	$unique_regions = array_unique($regions);
	$arr = [];
	foreach ($unique_regions as $k => $v) {
		$arr2 = [];
		foreach($regions as $k2 => $v2) {
			if($v == $v2 ) {
				$arr2[$k2] = $countries[$k2];
			}
		}
		asort($arr2);
		$arr[$v] = $arr2;
	}
	return $arr;
}

//Define structure of the output of HTML for admin section
function wp_pihb_region_structure($region, $array, $existingData) {
	switch ($region) {
		case 'AS':
			$regionName = 'Asia';
        break;
		case 'EU':
			$regionName = 'Europe';
        break;
		case 'AF':
			$regionName = 'Africa';
			break;
		case 'NA':
			$regionName = 'North America';
		break;
		case 'OC':
			$regionName = 'Oceania';
		break;
		case 'SA':
			$regionName = 'South America';
		break;
		case 'AN':
			$regionName = 'Antarctica';
		break;
	}
	$wp_pihb_region_structure = '<fieldset><legend>'.$regionName.'<span class="countryCount"></span></legend><div class="countryContainer">';
	foreach($array as $k => $v) {
		($existingData["'$region'"]["'$k'"]) ? $been = true : $been = false;
		$wp_pihb_region_structure .= wp_pihb_country_structure($k, $v, $region, $been);
	}
	$wp_pihb_region_structure .= '</div></fieldset>';
	return $wp_pihb_region_structure;
}

//Define structure of the output of HTML for admin section
function wp_pihb_country_structure($code, $name, $region, $been) {
	($been == true) ? $checked = 'checked="checked"' : $checked = '';
	($been == true) ? $countryClass = 'checked' : $countryClass = '';

	$country = '<div class="country '.$code.' '.$countryClass.'"><label for="'.$code.'"><img src="'.plugins_url("/flags/".$code.".png", __FILE__).'" />'.$name.'</label><input type="checkbox" name="wp_places_i_have_been[\''.$region.'\'][\''.$code.'\']" id="'.$code.'" value="1" '.$checked.'></div>';
	return $country;
}

//Get the data
function wp_pihb_show_places_i_have_been(){
	$theExistingData = maybe_unserialize(get_option('wp_places_i_have_been'));
	$arr = [];
	foreach($theExistingData as $k => $v) {
		foreach($v as $k2 => $v2) {
			array_push($arr, $k2);
		}
	}
	sort($arr);

	return wp_pihb_output_structure($arr);
}

//Define structure of the output of HTML for front end section
function wp_pihb_output_structure($arr) {
	$flagsPerRow = get_option('wp_places_i_have_been_display_settings');
	($flagsPerRow == '') ? $flagsPerRow = 'fourPerRow' : $flagsPerRow = $flagsPerRow;
	$structure = '<ul id="PlacesIHaveBeen" class="'.$flagsPerRow.'">';
	foreach ( $arr as $k ) {
		$countriesJSON = file_get_contents("data/countries.json", FILE_USE_INCLUDE_PATH);
		$countriesJSON = json_decode($countriesJSON);
		$k2 = str_replace('\'', '', $k);
		$countryName = $countriesJSON->$k2;
		$structure .= '<li><img src="'. plugins_url( "/flags/". str_replace('\'', '', $k) .".png", __FILE__ ) .'" alt="'.$countryName.'" data-toggle="tooltip" data-placement="top" title="'.$countryName.'" /></li>';
	}
	$structure .= '</ul>';
	return $structure;
}