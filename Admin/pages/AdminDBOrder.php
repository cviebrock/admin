<?php

/**
 * DB admin ordering page
 *
 * An ordering page with DB error checking.
 *
 * @package   Admin
 * @copyright 2004-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class AdminDBOrder extends AdminOrder
{
	// process phase


	protected function saveData()
	{
		try {
			$transaction = new SwatDBTransaction($this->app->db);
			$this->saveDBData();
			$transaction->commit();

		} catch (SwatDBException $e) {
			$transaction->rollback();

			$message = new SwatMessage(
				Admin::_('A database error has occured. The item was not saved.'),
				'system-error');

			$this->app->messages->add($message);
			$e->process();

		} catch (SwatException $e) {
			$message = new SwatMessage(
				Admin::_('An error has occured. The item was not saved.'),
				'system-error');

			$this->app->messages->add($message);
			$e->process();
		}
	}



	protected function saveDBData()
	{
		$this->saveIndexes();
	}

}

?>
