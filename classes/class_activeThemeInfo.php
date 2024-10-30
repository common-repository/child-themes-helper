<?PHP
namespace child_themes_helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class pas_cth_activeThemeInfo {
	private $currentActiveTheme; // WP_Theme Object for the currently active theme
	private $templateTheme;


	public $childThemeName;
	public $childThemeRoot;
	private $subfolderCountChildThemeRoot;
	private $subfolderCountTemplateThemeRoot;
	public $childStylesheet;
	public $templateThemeName;
	public $templateStylesheet;
	public $templateThemeRoot;


	// isChildTheme is true if the currently active theme is a child theme, false otherwise.
	public $isChildTheme;


	function __construct() {
		$selectedChildTheme = get_option("pas_cth_active_theme", false);
		$activeTheme = ($selectedChildTheme !== false ? wp_get_theme($selectedChildTheme) : false);
		if ($activeTheme === false) {
			throw new Exception('Active Theme Not Defined');
		} else {
			$this->currentActiveTheme = $activeTheme;
			$this->childThemeName	= $this->currentActiveTheme->get( "Name" );
			$this->childStylesheet	= $this->currentActiveTheme->get_stylesheet( );
			$this->childThemeRoot	=
				wp_normalize_path( $this->currentActiveTheme->get_theme_root( ) );
//				$this->fixDelimiters( $this->currentActiveTheme->get_theme_root( ) );
			$this->subfolderCountChildThemeRoot =
				count( explode( PAS_CTH_SEPARATOR, $this->childThemeRoot ) );
			$this->templateTheme = $this->currentActiveTheme->parent( );


			if ( $this->templateTheme ) {
				$this->templateThemeName	= $this->templateTheme->get( "Name" );
				$this->templateStylesheet	= $this->templateTheme->get_stylesheet( );
				$this->templateThemeRoot	=
					wp_normalize_path( $this->templateTheme->get_theme_root( ) );
//					$this->fixDelimiters( $this->templateTheme->get_theme_root( ) );
				$this->subfolderCountTemplateThemeRoot =
					count( explode( PAS_CTH_SEPARATOR, $this->templateThemeRoot ) );


				// Current theme is a child theme
				$this->isChildTheme = true;
			} else {
				// Current theme is NOT a child theme
				$this->isChildTheme = false;
			}
		}
	}
	/* Could have used "preg_replace", but couldn't find a search parameter that wouldn't trip
	 * over the forward slash as the final delimiter in the search.
	 * For example...
	 * $path = preg_replace( "/[\\/]+/", PAS_CTH_SEPARATOR, $path )
	 * ...would trip over the 2nd forward slash character and PHP would throw an error.
	 * additionally....
	 * $path = preg_replace( "/[\\\/]+/", PAS_CTH_SEPARATOR, $path )
	 * ...would trip over the extra backslash, preceding the 2nd forward slash.
	 * I tried using an alternate delimiter like this....
	 * $path = preg_replace( "|[\\/]+|", PAS_CTH_SEPARATOR, $path )
	 * ...but my web server would trip over that too.
	 * So I avoided using regular expressions for the fixDelimiters( ) function.
	 * Addendum: tried using single quotes around the search string. Web server tripped over that too.
	 */
	// Replaced all calls to this with wp_normalize_path()
	public function fixDelimiters( $path ) {
		$path = str_replace( "\\", "|+|", $path );
		$path = str_replace( "/", "|+|", $path );
		$path = str_replace( "|+|", PAS_CTH_SEPARATOR, $path );
		return $path;
	}


	public function getChildFolder( ) {
		return $this->childThemeRoot . PAS_CTH_SEPARATOR . $this->childStylesheet;
	}


	public function getTemplateFolder( ) {
		return ( $this->isChildTheme ?
					$this->templateThemeRoot . PAS_CTH_SEPARATOR . $this->templateStylesheet :
					false );
	}


	public function getFolderCount( $themeType ) {
		$returnValue = 0;
		switch ( $themeType ) {
			case PAS_CTH_CHILDTHEME:
				$returnValue = $this->subfolderCountChildThemeRoot;
				break;
			case PAS_CTH_TEMPLATETHEME:
				$returnValue = $this->subfolderCountTemplateThemeRoot;
				break;
		}
		return $returnValue;
	}
}


