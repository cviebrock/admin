<?php
/**
 * @package Admin
 * @copyright silverorange 2004
 */
require_once("Admin/AdminPage.php");

/**
 * Base class for a standard admin index page.
 * This class is intended to be a convenience class. For a fully custom page
 * inherit from AdminPage directly instead.
 */
abstract class AdminIndex extends AdminPage {

	protected $ui;

	public function display() {
		$view = $this->ui->getWidget('view');
		$view->model = $this->getTableStore();

		$form = $this->ui->getWidget('indexform');
		$form->action = $this->source;

		$root = $this->ui->getRoot();
		$root->display();
	}

	/**
	 * Retrieve data to display.
	 * This method is called to load data to be displayed in the table view.
	 * Sub-classes should implement this method and perform whatever actions
	 * are necessary to obtain the data.
	 * @return SwatTableStore A new SwatTableStore containing the data.
	 */
	abstract protected function getTableStore();

	public function process() {
		$form = $this->ui->getWidget('indexform');
		$view = $this->ui->getWidget('view');
		$actions = $this->ui->getWidget('actions', true);

		if (!$form->process())
			return;

		if (count($view->checked_items) == 0)
			return;

		if ($actions != null) {
			if ($actions->selected == null)
				return;

			$this->processActions();
		}
	}

	/**
	 * Process the actions.
	 * This method is called to perform whatever processing is required in 
	 * response to actions. Sub-classes should implement this method.
	 * Widgets can be accessed through the $ui class variable.
	 */
	protected function processActions() {

	}
}

?>