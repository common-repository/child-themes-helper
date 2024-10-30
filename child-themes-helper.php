<?php
/*
	Plugin Name: Child Themes Helper
	Plugin URI: https://www.paulswarthout.com/Child-Themes-Helper/
	Description: Tool to aid the child theme developer. Copies files from the parent theme to the child theme, duplicating the folder structure in the child theme. Directly edit the child theme's files. Create a new child theme from any template theme.
	Version: 2.2.7
	Author: Paul A. Swarthout
	Author URI: https://www.PaulSwarthout.com
	License: GPL2
	License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

namespace child_themes_helper;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$plugin_version = \get_file_data( __FILE__, ['Version' => 'Version']);
$plugin_version = ( array_key_exists( 'Version', $plugin_version ) ? $plugin_version['Version'] : '');

// Constants created for this plugin
require_once( dirname( __FILE__ ) . '/lib/plugin_constants.php' );
// Current active theme information
require_once( dirname( __FILE__ ) . '/classes/class_activeThemeInfo.php' ); //
// Class to create the screenshot.png file for the active theme.
require_once( dirname( __FILE__ ) . '/classes/class_createScreenShot.php' );
// Main class for this plugin
require_once( dirname( __FILE__ ) . '/classes/class_childThemesHelper.php' );
// All functions targeted by wp_ajax_* calls.
require_once( dirname( __FILE__ ) . '/classes/class_ajax_functions.php' );
// Reads the font name from the true type font file
require_once( dirname( __FILE__ ) . '/classes/class_fontMeta.php' );
// Color selection tool
require_once( dirname( __FILE__ ) . '/classes/class_colorPicker.php' );
// Common Functions
require_once( dirname( __FILE__ ) . '/classes/class_common_functions.php' );
require_once( dirname( __FILE__ ) . '/classes/class-themes.php' );

/* Go get the current theme information.
 * This is a wrapper for the wp_get_theme( ) function.
 * It loads the information that we'll need for our purposes and tosses everything else
 *	that is returned by the wp_get_theme( ) function.
 */
$pas_cth_pluginDirectory =
	[
		'path' => plugin_dir_path ( __FILE__ ),
		'url'  => plugin_dir_url  ( __FILE__ )
	];
$pas_cth_themes = new pas_cth_themes(['pluginDirectory' => $pas_cth_pluginDirectory]);
$pas_cth_themeInfo = null;
$pas_cth_child_theme_selected = false;
$pas_cth_default_tab = "copy-theme-files";
$pas_cth_child_theme_selected = true;

$activeTheme = get_option("pas_cth_active_theme", false);
if ($activeTheme !== false) {
	if (wp_get_theme($activeTheme)->exists()) {
		try {
			$pas_cth_themeInfo = new pas_cth_activeThemeInfo();
			if ($pas_cth_themeInfo == null || $pas_cth_themeInfo->childThemeName == "") {
				\delete_option("pas_cth_active_theme");
				error_log("Child Themes Helper: Active Child Theme is corrupt. Header seems to be missing in styles.css.");
				$pas_cth_default_tab = "options";
				return;
			}
		} catch (Exception $exc) {
			if ($exc->getMessage() == "Active Theme Not Defined") {

				$pas_cth_default_tab			= "options";
				$pas_cth_child_theme_selected	= false;

			}
		}
	} else {
		/*
		 * If an admin deletes a child theme that CTH has set to 'active', (not the activated theme)
		 *   then disable 'active' and
		 *     return the user to the Options tab to set a different child theme as 'active'.
		 */
		delete_option("pas_cth_active_theme");
		$pas_cth_default_tab = "options";
	}
} else {
	$pas_cth_default_tab = "options";
}

$pas_cth_library	= new pas_cth_library_functions( ['pluginDirectory' => $pas_cth_pluginDirectory] );

$args = [
			'pluginDirectory'	=>	$pas_cth_pluginDirectory,
			'pluginName'		=>	'Child Themes Helper',
			'pluginFolder'		=>	'child-themes-helper',
			'activeThemeInfo'	=>	$pas_cth_themeInfo,
			'libraryFunctions'	=>	$pas_cth_library,
			'Themes'			=>	$pas_cth_themes,
			'defaultTab'		=>	$pas_cth_default_tab,
			'plugin_version'	=>	$plugin_version,
		];

$pas_cth_colorPicker		= new pas_cth_colorPicker( $args );
$args['colorPicker']		= $pas_cth_colorPicker;

$pas_cth_ChildThemesHelper	= new pas_cth_ChildThemesHelper( $args );
$pas_cth_AJAXFunctions		= new pas_cth_AJAXFunctions( $args );

add_action( 'admin_menu',				Array( $pas_cth_ChildThemesHelper,	'dashboard_menu' ) );
add_action( 'admin_enqueue_scripts',	Array( $pas_cth_ChildThemesHelper,	'dashboard_styles' ) );
add_action( 'admin_enqueue_scripts',	Array( $pas_cth_ChildThemesHelper,	'dashboard_scripts' ) );
add_action( 'admin_enqueue_scripts',	Array( $pas_cth_colorPicker,		'color_picker_styles' ) );
add_action( 'admin_enqueue_scripts',	Array( $pas_cth_colorPicker,		'color_picker_scripts' ) );

add_action( 'init',		__NAMESPACE__ . '\pas_cth_startBuffering' );		// Response Buffering
/*
 * DEMO_MODE:
 * The Child Themes Helper plugin has a feature called DEMO MODE where it can be made to appear on a
 * specific non-admin user's dashboard. There are 4 parts of this feature that must all be present
 * to make this happen. All four parts require a person with access to the website's files and a
 * user that has been assigned the 'manage_options' capability.
 *
 * [1]: Create a user.
 * Any username will do. For my demo website (http://www.1acsi.com) I chose the user 'demo'.
 *
 * [2]: Create a new WordPress Role.
 * Any role name will do. For my demo website, I used the 'User Roles & Capabilities' plugin to create
 * the 'DEMO' role.
 *
 * [3]: Create a new WordPress Capability.
 * Almost any capability will do. It must be a new capability that doesn't already exist among the
 * WordPress built-in capabilities. For my demo website, I used the 'User Roles & Capabilities' plugin
 * to create the 'DEMO' capability.
 *
 * [4]: Assign the necessary capabilities to your new role.
 * Using the 'User Roles & Capabilities' plugin, I assigned the following capabilities to the 'DEMO' role.
 *		'install_themes'		Probably not required to demonstrate Child Themes Helper, but certainly nice
 *								for those users that would like to see how the Child Themes Helper works
 *								with the theme they're using.
 *
 *		'switch_themes'			Required to activate the Child Theme that they used the Child Themes Helper to create.
 *
 *		'read'					Required to give the 'demo' user access to the dashboard.
 *
 *		'DEMO'					Or whatever capability you created.
 *
 * [5]: Define 2 constants in the 'wp_config.php' file.
 *		define('DEMO_USER', 'demo') This should be the user login of the user you created in step 1, above.
 *		define('DEMO_CAPABILITY', 'DEMO') This should be the capability that you created in step 3,
 *        and assigned to the DEMO_USER in step 4.
 *
 * When a website visitor visits your website and logs in with the DEMO_USER, they will be directed to the
 * website's admin dashboard. They will only see the Appearance menu item which will take them directly to the
 * Themes page (no other submenu items are available), the Child Themes Helper menu item, Profile, and Dashboard,
 * and nothing else.
 *
 * Any attempt to access the user profile will redirect them back to the dashboard.
 *
 * DEMO MODE can be completely disabled by:
 * 1) Deleting the two defines (or commenting them out) for DEMO_USER and DEMO_CAPABILITY from the 'wp_config.php' file.
 * 2) Disabling or deleting the DEMO_USER user.
 *
 * NOTE: If you just delete the two lines from the 'wp_config.php' file, a user logging in with the DEMO_USER
 * will still be able to modify the website's themes. You MUST disable or delete the DEMO_USER.
 *
 */
if (defined("DEMO_CAPABILITY")) { add_action( 'init',	'pas_cth_no_profile_access' ); }

add_action( 'wp_footer', __NAMESPACE__ . '\pas_cth_flushBuffer' );		// Response Buffering

/* AJAX PHP functions may be found in the 'classes/class_ajax_functions.php' file
 * AJAX Javascript functions are in the 'js/pasChildThemes.js' file
 *
 * The following 5 ajax functions handle the functionality for removing child theme files
 * and copying template theme files to the child theme. No changes are EVER made to the template
 * theme files.
 *
 * It all starts with a user clicking on a file in either the left pane ( Child Theme ) or the
 * right pane ( Template Theme ) and triggering the onclick event to call the Javascript
 * pas_cth_js_selectFile( ) function.
 * From there the path is different based upon the $themeType, either Child ( left ) or Template ( right ).
 *
 * For removing a child theme file, the next steps, in order, are:
 * PHP selectFile( )					#1 REMOVED THIS FUNCTION and Eliminated 1 AJAX call
 * JS pas_cth_js_removeChildFile( )		#2
 * PHP verifyRemoveFile( )				#3
 * JS pas_cth_js_deleteChildFile( )		#4
 * PHP deleteFile( )					#5
 * File has been deleted. We're done.
 *
 * For copying a template theme file to the child theme, the next steps, in order, are:
 * PHP selectFile( )					#6 REMOVED THIS FUNCTION and eliminated 1 AJAX call
 * JS pas_cth_js_copyTemplateFile( )	#7
 * PHP verifyCopyFile( )				#8
 * JS pas_cth_js_overwriteFile( )		#9
 * PHP copyFile( )						#10
 * File has been copied. We're done.
 */
add_action( 'wp_ajax_verifyRemoveFile',		Array( $pas_cth_AJAXFunctions, 'verifyRemoveFile' ) ); //#3
add_action( 'wp_ajax_deleteFile',			Array( $pas_cth_AJAXFunctions, 'deleteFile' ) ); //#5
add_action( 'wp_ajax_verifyCopyFile',		Array( $pas_cth_AJAXFunctions, 'verifyCopyFile' ) ); //#8
add_action( 'wp_ajax_copyFile',				Array( $pas_cth_AJAXFunctions, 'copyFile' ) ); //#10

/* From the Create Child Theme "form", "submit" button triggers the pas_cth_js_createChildTheme( )
 * javascript function.
 * It is triggered with an AJAX call from Javascript when the
 * Create Child Theme button is clicked. */
add_action( 'wp_ajax_createChildTheme',		Array( $pas_cth_AJAXFunctions, 'createChildTheme' ) );

// Save Options for generating a simple, custom, screenshot.png file for a new child theme.
add_action( 'wp_ajax_saveOptions',			Array( $pas_cth_AJAXFunctions, 'saveOptions' ) );

add_action( 'wp_ajax_displayColorPicker',	Array( $pas_cth_AJAXFunctions, 'chooseColor' ) );
add_action( 'wp_ajax_saveDefaultFont',		Array( $pas_cth_AJAXFunctions, "saveFont" ) );

add_action( 'wp_ajax_editFile',				Array( $pas_cth_AJAXFunctions, "editFile" ) );
add_action( 'wp_ajax_saveFile',				Array( $pas_cth_AJAXFunctions, "saveFile" ) );

add_action( 'wp_ajax_setExpertMode',		Array( $pas_cth_AJAXFunctions, "ajax_set_expert_mode") );
add_action( 'wp_ajax_setDefaultChildTheme', Array( $pas_cth_AJAXFunctions, "ajax_set_child_theme" ) );
add_action( 'wp_ajax_generateScreenShot',	Array( $pas_cth_AJAXFunctions, "ajax_generate_screen_shot" ) );

// Plugin Deactivation
function pas_cth_deactivate( ) {
	update_option( 'pas_cth_test', 'plugin-deactivated' );

	delete_option( 'pas_cth_fcColor' );
	delete_option( 'pas_cth_bcColor' );
	delete_option( 'pas_cth_font' );
	delete_option( 'pas_cth_imageWidth' );
	delete_option( 'pas_cth_imageHeight' );
	delete_option( 'pas_cth_string1' );
	delete_option( 'pas_cth_string2' );
	delete_option( 'pas_cth_string3' );
	delete_option( 'pas_cth_string4' );
	delete_option( 'pas_cth_fontList' );
	delete_option( 'pas_cth_active_theme' );
}

register_deactivation_hook( __FILE__, __NAMESPACE__ . '\pas_cth_deactivate' );//Plugin Deactivation


/*
 * The next 3 functions set up buffering on the page.
 * This is so we can wp_redirect( admin_url( "themes.php" ) ) after creating a new child theme.
 */
function pas_cth_callback( $buffer ){
	return $buffer;
}

function pas_cth_StartBuffering( ){
	ob_start( __NAMESPACE__ . "\pas_cth_callback" );
}

function pas_cth_FlushBuffer( ){
	ob_end_flush( );
}

function pas_cth_no_profile_access() {
	if (strtolower(wp_get_current_user()->user_login) == strtolower("demo")) {
		if (strpos ($_SERVER ['REQUEST_URI'] , 'wp-admin/profile.php' )){
			wp_redirect(get_option('siteurl') . "/wp-admin");
			exit;
		}
	}
}