<?php

require_once 'Admin/pages/AdminDBEdit.php';
require_once 'Admin/AdminUI.php';
require_once 'SwatDB/SwatDB.php';

/**
 * Edit page for AdminGroups component
 *
 * @package Admin
 * @copyright silverorange 2004
 */
class AdminGroupsEdit extends AdminDBEdit
{
	// {{{ private properties

	private $fields;

	// }}}

	// init phase
	// {{{ protected function initInternal()

	protected function initInternal()
	{
		parent::initInternal();

		$this->ui->loadFromXML(dirname(__FILE__).'/edit.xml');

		$this->fields = array('title');

		$user_list = $this->ui->getWidget('users');
		$user_list->options = SwatDB::getOptionArray($this->app->db, 
			'adminusers', 'name', 'id', 'name');

		$component_list = $this->ui->getWidget('components');
		$component_list->tree = SwatDB::getGroupedOptionArray($this->app->db, 
			'admincomponents', 'title', 'id',
			'adminsections', 'title', 'id', 'section',
			'adminsections.displayorder, adminsections.title,
			admincomponents.displayorder,  admincomponents.title');
	}

	// }}}

	// process phase
	// {{{ protected function saveDBData()

	protected function saveDBData($id)
	{
		$values = $this->ui->getValues(array('title'));

		if ($id === null)
			$id = SwatDB::insertRow($this->app->db, 'admingroups', $this->fields,
				$values, 'integer:id');
		else
			SwatDB::updateRow($this->app->db, 'admingroups', $this->fields,
				$values, 'integer:id', $id);

		$user_list = $this->ui->getWidget('users');

		SwatDB::updateBinding($this->app->db, 'adminuser_admingroup', 
			'groupnum', $id, 'usernum', $user_list->values, 'adminusers', 'id');

		$component_list = $this->ui->getWidget('components');

		SwatDB::updateBinding($this->app->db, 'admincomponent_admingroup', 
			'groupnum', $id, 'component', $component_list->values, 'admincomponents', 'id');

		$msg = new SwatMessage(
			sprintf(Admin::_('Group “%s” has been saved.'),
			$values['title']), SwatMessage::NOTIFICATION);

		$this->app->messages->add($msg);
	}

	// }}}

	// build phase
	// {{{ protected function loadDBData()

	protected function loadDBData($id)
	{
		$row = SwatDB::queryRowFromTable($this->app->db, 'admingroups', 
			$this->fields, 'integer:id', $id);

		if ($row === null)
			return $this->app->replacePageNoAccess();

		$this->ui->setValues(get_object_vars($row));

		$user_list = $this->ui->getWidget('users');
		$user_list->values = SwatDB::queryColumn($this->app->db, 
			'adminuser_admingroup', 'usernum', 'groupnum', $id);

		$component_list = $this->ui->getWidget('components');
		$component_list->values = SwatDB::queryColumn($this->app->db, 
			'admincomponent_admingroup', 'component', 'groupnum', $id);
	}

	// }}}
}

?>