<?php

/**
 * Exception page in an admin application
 *
 * @package   Admin
 * @copyright 2006-2016 silverorange
 */
class AdminAdminSiteException extends SiteXhtmlExceptionPage
{


	/**
	 * @var SwatContainer
	 */
	protected $container;



	protected function createLayout()
	{
		return new AdminDefaultLayout($this->app, AdminDefaultTemplate::class);
	}


	// init phase


	public function init()
	{
		parent::init();

		$this->container = new SwatFrame();
		$this->container->classes[] = 'admin-exception-container';
	}


	// build phase


	public function build()
	{
		parent::build();
		if (isset($this->layout->navbar)) {
			$this->layout->navbar->popEntry();
			$this->layout->navbar->popEntry();
			$this->layout->navbar->createEntry('Error');
		}
	}



	protected function display()
	{
		ob_start();

		printf('<p>%s</p>', $this->getSummary());

		echo '<p>This error has been reported.</p>';

		if ($this->exception !== null) {
			$this->exception->process(false);
		}

		$content_block = new SwatContentBlock();
		$content_block->content = ob_get_clean();
		$content_block->content_type = 'text/xml';

		$this->container->add($content_block);
		$this->container->display();
	}


	// finalize phase


	public function finalize()
	{
		parent::finalize();
		$this->layout->addHtmlHeadEntrySet(
			$this->container->getHtmlHeadEntrySet()
		);
	}

}

?>
