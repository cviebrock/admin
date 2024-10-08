<?php

/**
 * Page of an administration application.
 *
 * @copyright 2004-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class AdminPage extends SitePage
{
    public const RELOCATE_URL_FIELD = '_admin_relocate_url';

    /**
     * Source of this page.
     *
     * @var string
     */
    public $source;

    /**
     * Component name of this page.
     *
     * @var string
     */
    public $component;

    /**
     * Subcomponent name of this page.
     *
     * @var string
     */
    public $subcomponent;

    /**
     * Title of this page.
     *
     * @var string
     */
    public $title;

    /**
     * Reference to the navbar object.
     *
     * Officially the navbar now lives in the layout object, but this
     * reference is very useful for backwards compatibility with
     * exisitng code.
     *
     * @var AdminNavBar
     */
    public $navbar;

    /**
     * The user-interface of this page.
     *
     * @var AdminUI
     */
    protected $ui;

    public function __construct(
        SiteApplication $app,
        ?SiteLayout $layout = null,
        array $arguments = []
    ) {
        parent::__construct($app, $layout, $arguments);

        // see comment above on navbar class var
        if (isset($this->layout->navbar)) {
            $this->navbar = $this->layout->navbar;
        }

        $this->ui = new AdminUI();
    }

    public function getRelativeURL()
    {
        $url = $this->source . '?';

        foreach ($_GET as $name => $value) {
            if ($name != 'source') {
                $url .= $name . '=' . $value . '&';
            }
        }

        $url = mb_substr($url, 0, -1);

        return $url;
    }

    public function getRefererURL()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        }
        $source_exp = explode('/', $this->source);

        return $source_exp[0];
    }

    public function getComponentName()
    {
        return $this->component;
    }

    public function getComponentTitle()
    {
        return $this->title;
    }

    protected function createLayout()
    {
        return new AdminDefaultLayout($this->app, AdminDefaultTemplate::class);
    }

    // init phase

    /**
     * Initialize the page.
     *
     * Initializes {@link AdminPage::initInternal()} and {@link * AdminPage::$ui}. Sub-classes should implement
     * {@link SitePage::initInternal()} to perform their own
     * initialization.
     */
    public function init()
    {
        parent::init();
        $this->initInternal();
        $this->ui->init();
    }

    /**
     * Initialize the page.
     *
     * Sub-classes should implement this method to initialize the page. At
     * this point the {@link AdminPage::$ui} has been constructed but has not
     * been initialized.
     */
    protected function initInternal() {}

    // process phase

    /**
     * Process the page.
     *
     * Sub-classes should implement this method to process the page.
     * Sub-classes should call parent::process first which calls
     * {@link AdminPage::$ui->process()}.
     * Called after {@link AdminPage::init()}.
     */
    public function process()
    {
        parent::process();
        if ($this->formsAreAuthenticated()) {
            $this->ui->process();
            $this->processInternal();
        }
    }

    /**
     * Authenticate any forms on the page.
     *
     * This happens before {@link AdminPage::$ui} has been processed.
     */
    protected function formsAreAuthenticated()
    {
        $forms = $this->ui->getRoot()->getDescendants('SwatForm');
        foreach ($forms as $form) {
            if (!$form->isAuthenticated()) {
                $message = new SwatMessage(Admin::_('There is a problem with ' .
                    'the information submitted.'), 'warning');

                $message->secondary_content =
                    Admin::_('In order to ensure your security, we were ' .
                    'unable to process your request. Please try again.');

                $this->app->messages->add($message);

                // if one form is not authenticated, we don't need to check more
                return false;
            }
        }

        return true;
    }

    /**
     * Processes the page.
     *
     * Sub-classes should implement this method to process the page. At
     * this point the {@link AdminPage::$ui} has already been processed.
     */
    protected function processInternal() {}

    // build phase

    public function build()
    {
        parent::build();

        $this->buildInternal();

        $this->layout->data->title =
            SwatString::minimizeEntities($this->title) . ' - ' .
            SwatString::minimizeEntities($this->app->title);

        $this->layout->startCapture('content');
        $this->display();
        $this->layout->endCapture();
    }

    /**
     * Build the page for display.
     *
     * Sub-classes should implement this method to initialize elements of
     * the page. This method is called at the beginning of {@link * AdminPage::build()}. This is useful to do database queries that are
     * only needed for {@link AdminPage::display()} and not {@link * AdminPage::process()}, while initialization needed for both display
     * and process should be included in {@link AdminPage::init()}.
     */
    protected function buildInternal() {}

    protected function buildMessages()
    {
        try {
            $message_display = $this->ui->getWidget('message_display');
            foreach ($this->app->messages->getAll() as $message) {
                $message_display->add($message);
            }
        } catch (SwatWidgetNotFoundException $e) {
            // ignore
        }
    }

    /**
     * Display the page.
     *
     * Sub-classes should implement this method to display the contents of
     * the page. Called after {@link AdminPage::init()}
     */
    protected function display()
    {
        if ($this->ui !== null) {
            $this->ui->display();
        }
    }

    // finalize phase

    public function finalize()
    {
        parent::finalize();
        $this->layout->addHtmlHeadEntrySet(
            $this->ui->getRoot()->getHtmlHeadEntrySet()
        );
    }
}
