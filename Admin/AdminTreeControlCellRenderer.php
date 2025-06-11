<?php

/**
 * Cell renderer for renderering tree details links
 *
 * This cell renderer also displays a could of children in the details
 * link title attribute.
 *
 * @package   Admin
 * @copyright 2004-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class AdminTreeControlCellRenderer extends SwatImageLinkCellRenderer
{


	/**
	 * The number of children the item this renderer is rendering for has
	 *
	 * @var integer
	 */
	public $childcount = 0;




	public function render()
	{
		if (!$this->visible)
			return;

		$this->width = 22;
		$this->height = 22;

		if ($this->childcount == 0) {
			$this->title = Admin::_('View Details');
			$this->alt = Admin::_('Details');
			$this->image = 'packages/admin/images/admin-generic-document.png';
		} else {
			$this->title = sprintf(Admin::ngettext(
				'View Details (%s sub-item)',
				'View Details (%s sub-items)', $this->childcount),
				SwatString::numberFormat($this->childcount));

			$this->alt = Admin::_('Details');
			$this->image =
				'packages/admin/images/admin-document-with-contents.png';
		}

		parent::render();
	}


}

?>
