<?php

/**
 * Web application class for an administration application
 *
 * @package   Admin
 * @copyright 2004-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class AdminApplication extends SiteWebApplication
{
	// {{{ public properties

	/**
	 * A visble title for this admin
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Convenience reference to MDB2 object within the built-in AdminDatabaseModule
	 *
	 * @var MDB2_Connection Database connection object (readonly)
	 */
	public $db;

	/**
	 * Default locale
	 *
	 * This locale is used for translations, collation and locale-specific
	 * formatting. The locale is a five character identifier composed of a
	 * language code (ISO 639) an underscore and a country code (ISO 3166). For
	 * example, use 'en_CA' for Canadian English.
	 *
	 * @var string
	 */
	public $default_locale = null;

	// }}}
	// {{{ protected properties

	/**
	 * The default path to include components from
	 *
	 * @var string
	 *
	 * @see AdminApplication::setDefaultComponentIncludePath()
	 */
	protected $default_component_include_path =
		'../../include/admin/components';

	/**
	 * An array of paths to check for components when finding the filename of
	 * a component
	 *
	 * The array is of the form: 'ClassPrefix' => 'path'.
	 *
	 * @var array
	 *
	 * @see AdminApplication::addComponentIncludePath()
	 */
	protected $component_include_paths = array();

	/**
	 * Source of the front page.
	 *
	 * @var string the page to load as the front page of the admin.
	 */
	protected $front_source = 'AdminSite/Front';

	/**
	 * Class to use for the menu-view
	 *
	 * @var string the menu-view class name.
	 */
	protected $menu_view_class = 'AdminMenuView';

	/*
	 * @var array
	 */
	protected $has_component_cache = array();

	// }}}
	// {{{ public function run()

	public function run()
	{
		if ($this->default_locale !== null)
			setlocale(LC_ALL, $this->default_locale);

		parent::run();
	}

	// }}}
	// {{{ public function replacePage()

	/**
	 * Replace the page object
	 *
	 * This method can be used to load another page to replace the current
	 * page. For example, this is used to load a confirmation page when
	 * processing an admin index page.
	 *
	 * @param string $source the source of the page with which to replace the
	 *                        current page. The source will be passed to the
	 *                        {@link SiteWebApplication::resolvePage()} method.
	 *
	 * @param integer $reset_vars bitwise argument of whether or not to reset
	 *                           GET or POST vars when replacing the page. By
	 *                           default, both are reset.
	 */
	public function replacePage($source, $reset_vars = null)
	{
		if ($reset_vars === null) {
			$reset_vars = self::VAR_GET | self::VAR_POST;
		}

		if ($reset_vars & self::VAR_GET) {
			$_GET = array();
		}

		if ($reset_vars & self::VAR_POST) {
			$_POST = array();
		}

		parent::replacePage($source);
	}

	// }}}
	// {{{ public function getDefaultSubComponent()

	/**
	 * Gets the name of the default sub-component of this application
	 *
	 * @return string the name of the default sub-component to use if no
	 *                 sub-component is specified in the page request source.
	 */
	public function getDefaultSubComponent()
	{
		return 'Index';
	}

	// }}}
	// {{{ public function getFrontendBaseHref()

	/**
	 * Gets the base href of the frontend application administered by this
	 * admin application
	 *
	 * @param boolean $secure whether or not the base href should be a secure
	 *                         URI. The default value is false.
	 *
	 * @return string the base href of the frontend application administered
	 *                 by this admin application.
	 */
	public function getFrontendBaseHref($secure = false)
	{
		$base_href = $this->getBaseHref($secure);
		$base_href = dirname($base_href).'/'; // strip off admin sub-directory
		return $base_href;
	}

	// }}}
	// {{{ public function getFrontSource()

	/**
	 * Gets the source of the front page
	 *
	 * @return string the subcomponent page to load as the front page of this
	 *                 admin application.
	 */
	public function getFrontSource()
	{
		return $this->front_source;
	}

	// }}}
	// {{{ public function setFrontSource()

	/**
	 * Sets the source of the front page
	 *
	 * @param string $source the subcomponent page to load as the front page
	 *                        of this admin application.
	 */
	public function setFrontSource($source)
	{
		$this->front_source = $source;
	}

	// }}}
	// {{{ public function setMenuViewClass()

	/**
	 * Sets the class to use for this admin application's menu-view
	 *
	 * @param string $class_name the class to use for this admin application's
	 *                            menu-view.
	 *
	 * @throws AdminException if the menu-view class is not defined or if the
	 *                         menu-view class is not an AdminMenuView.
	 */
	public function setMenuViewClass($class_name)
	{
		if (!class_exists($class_name))
			throw new AdminException(sprintf(
				"AdminMenuView class '%s' is undefined.", $class_name));

		if ($class_name !== 'AdminMenuView' &&
			!is_subclass_of($class_name, 'AdminMenuView'))
			throw new AdminException(sprintf(
				"Class '%s' is not an AdminMenuView.", $class_name));

		$this->menu_view_class = $class_name;
	}

	// }}}
	// {{{ public function getMenuViewClass()

	/**
	 * Gets the class used for this admin application's menu-view
	 *
	 * @return string the class to use for this admin application's menu-view.
	 *                 By default, this is 'AdminMenuView'.
	 *
	 * @see AdminApplication::setMenuViewClass()
	 */
	public function getMenuViewClass()
	{
		return $this->menu_view_class;
	}

	// }}}
	// {{{ public function setDefaultComponentIncludePath()

	/**
	 * Sets the default a path to include components from
	 *
	 * Paths are relative to the www dir of the site.
	 *
	 * @param string $path the include path.
	 */
	public function setDefaultComponentIncludePath($path)
	{
		$this->default_component_include_path = $path;
	}

	// }}}
	// {{{ public function getDeafultComponentIncludePath()

	/**
	 * Gets the default component include path
	 *
	 * @return string the component include path.
	 */
	public function getDefaultComponentIncludePath()
	{
		return $this->default_component_include_path;
	}

	// }}}
	// {{{ public function addComponentIncludePath()

	/**
	 * Adds a path to the list of component include paths
	 *
	 * Paths do not contain leading or trailing slashes and are relative to
	 * the PHP include path. The component include paths are searched in order
	 * so if a file exists in two places, the first filename will be returned.
	 *
	 * @param string $path the include path to add.
	 * @param string $prefix the prefix to prefix classes defined in the
	 *                        include path with.
	 *
	 * @see AdminApplication::getComponentIncludePaths()
	 */
	public function addComponentIncludePath($path, $prefix)
	{
		$this->component_include_paths[$prefix] = $path;
	}

	// }}}
	// {{{ public function getComponentIncludePaths()

	/**
	 * Gets the array of paths to check for components when finding the
	 * filename of a component
	 *
	 * The array is indexed by a class prefix. This class prefix is prepended
	 * to the names of classes defined in files found in the component include
	 * path.
	 *
	 * @return array an array containing the paths to check for components when
	 *                finding the filename of a component.
	 */
	public function &getComponentIncludePaths()
	{
		return $this->component_include_paths;
	}

	// }}}
	// {{{ public function hasComponent()

	public function hasComponent($shortname)
	{
		if (!isset($this->has_component_cache[$shortname])) {
			$component = new AdminComponent();
			$component->setDatabase($this->db);

			$this->has_component_cache[$shortname] =
				$component->loadFromShortname($shortname);
		}

		return $this->has_component_cache[$shortname];
	}

	// }}}
	// {{{ public function isMultipleInstanceAdmin()

	/**
	 * Helper method to check to see if this is the admin for all instances of a
	 * multi-instance site.
	 *
	 * @returns boolean true if it is the admin for multiple instances, false if
	 *                   it is not.
	 */
	public function isMultipleInstanceAdmin()
	{
		return ($this->hasModule('SiteMultipleInstanceModule') &&
			$this->getInstanceId() === null);
	}

	// }}}
	// {{{ public function userHasAccess()

	public function userHasAccess($shortname)
	{
		return $this->session->user->hasAccessByShortname($shortname);
	}

	// }}}
	// {{{ public function setMemcacheInstanceValues()

	/**
	 * Convience method to ensure the memcache module is configured correctly
	 * for the given instance, which is necessary for caching calls in a
	 * multiple instance admin.
	 *
	 * @param SiteInstance $instance
	 */
	public function setMemcacheInstanceValues(SiteInstance $instance = null)
	{
		if ($this->hasModule('SiteMemcacheModule')) {
			$cache = $this->getModule('SiteMemcacheModule');

			// server and app_ns can be instance specific by using the
			// InstanceConfigSetting table, so reset them with the instance's
			// config setting for those values.
			$cache->server = $this->getConfigSetting(
				'memcache.server',
				$instance
			);

			$cache->app_ns = $this->getConfigSetting(
				'memcache.app_ns',
				$instance
			);

			if ($instance instanceof SiteInstance) {
				$cache->setInstance($instance);
			}
		}
	}

	// }}}
	// {{{ protected function normalizeSource()

	protected function normalizeSource($source)
	{
		$source = parent::normalizeSource($source);

		if ($source === 'index.html' || $source === '')
			$source = $this->front_source;

		return $source;
	}

	// }}}
	// {{{ protected function resolvePage()

	protected function resolvePage($source)
	{
		$path = explode('/', $source);
		switch ($path[0]) {
		case 'smil':
			array_shift($path);
			$layout = new SiteLayout($this, SiteSMILTemplate::class);
			$page = new SiteAmazonCdnMediaManifestPage($this, $layout);
			$page->setMediaKey(mb_substr(array_shift($path), 0, -5));
			return $page;
		case 'vtt':
			array_shift($path);
			$layout = new SiteLayout($this, SiteVTTTemplate::class);
			$page = new SiteVideoTextTracksPage($this, $layout);
			$page->setMediaKey(mb_substr(array_shift($path), 0, -4));
			return $page;
		default :
			return $this->resolveAdminPage($source);
		}
	}

	// }}}
	// {{{ protected function resolveAdminPage()

	protected function resolveAdminPage($source)
	{
		$request = new AdminPageRequest($this, $source);

		$classname = $request->getClassName();
		if (!class_exists($classname)) {
			throw new AdminNotFoundException(
				sprintf(
					Admin::_("Class '%s' does not exist."),
					$classname
				)
			);
		}

		$layout = $this->resolveLayout($source);
		$page = new $classname($this, $layout);
		$page->title = $request->getTitle();

		if ($page instanceof AdminPage) {
			$page->source = $request->getSource();
			$page->component = $request->getComponent();
			$page->subcomponent = $request->getSubComponent();
		}

		if ($page->layout instanceof AdminDefaultLayout) {
			$entry = new AdminImportantNavBarEntry($this->title, '.');
			$page->layout->navbar->addEntry($entry);

			// Don't link the default sub-component navbar entry
			if ($request->getSubComponent() == $this->getDefaultSubComponent()) {
				$entry = new SwatNavBarEntry($request->getTitle(), null);
			} else {
				$entry = new SwatNavBarEntry($request->getTitle(),
					$request->getComponent());
			}

			$page->layout->navbar->addEntry($entry);
		}

		return $page;
	}

	// }}}
	// {{{ protected function resolveExceptionPage()

	/**
	 * Resolves an exception page for a particular source
	 *
	 * Sub-classes are encouraged to override this method to create different
	 * exception page instances for different sources.
	 *
	 * @param string $source the source to use to resolve the exception page.
	 *
	 * @return SitePage the exception page corresponding the given source.
	 */
	protected function resolveExceptionPage($source)
	{
		return $this->resolvePage('AdminSite/Exception');
	}

	// }}}
	// {{{ protected function getDefaultModuleList()

	/**
	 * Gets the list of default modules to load for this applicaiton
	 *
	 * @return array
	 * @see    SiteApplication::getDefaultModuleList()
	 */
	protected function getDefaultModuleList()
	{
		return array_merge(
			parent::getDefaultModuleList(),
			[
				'cookie' => SiteCookieModule::class,
				'database' => SiteDatabaseModule::class,
				'session' => AdminSessionModule::class,
				'messages' => SiteMessagesModule::class,
				'config' => SiteConfigModule::class,
				'notifier' => SiteNotifierModule::class,
				'crypt' => SiteCryptModule::class,
			]
		);
	}

	// }}}
	// {{{ protected function initModules()

	protected function initModules()
	{
		parent::initModules();
		// set up convenience references
		$this->db = $this->database->getConnection();
	}

	// }}}
	// {{{ protected function getSecureSourceList()

	/**
	 * @see SiteApplication::getSecureSourceList()
	 */
	protected function getSecureSourceList()
	{
		$list = parent::getSecureSourceList();
		$list[] = '.*'; // all sources

		return $list;
	}

	// }}}
	// {{{ protected function configure()

	protected function configure(SiteConfigModule $config)
	{
		parent::configure($config);

		$this->addComponentIncludePath('Admin/components', 'Admin');
	}

	// }}}
	// {{{ protected function addConfigDefinitions()

	/**
	 * Adds configuration definitions to the config module of this application
	 *
	 * @param SiteConfigModule $config the config module of this application to
	 *                                  which to add the config definitions.
	 */
	protected function addConfigDefinitions(SiteConfigModule $config)
	{
		parent::addConfigDefinitions($config);
		$config->addDefinitions(Admin::getConfigDefinitions());
	}

	// }}}
}

?>
