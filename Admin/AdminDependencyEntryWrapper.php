<?php

/**
 *
 * @package   Admin
 * @copyright 2006-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class AdminDependencyEntryWrapper extends SwatDBRecordsetWrapper
{


	protected function init()
	{
		parent::init();
		$this->row_wrapper_class = 'AdminDependencyEntry';
	}

}

?>
