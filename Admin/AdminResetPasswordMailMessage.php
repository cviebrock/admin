<?php

/**
 * Email that is sent to an admin uuser when they request a new password
 *
 * To send a password reset message:
 * <code>
 * $password_link = 'AdminSite/ResetPassword?password_tag=foo';
 * $email = new AdminResetPasswordMailMessage($app, $user, $password_link,
 *     'My Application Title');
 *
 * $email->smtp_server = 'example.com';
 * $email->from_address = 'service@example.com';
 * $email->from_name = 'Customer Service';
 * $email->subject = 'Reset Your Password';
 *
 * $email->send();
 * </code>
 *
 * @package   Admin
 * @copyright 2007-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class AdminResetPasswordMailMessage extends SiteMultipartMailMessage
{


	/**
	 * The user this reset password mail message is intended for
	 *
	 * @var AdminUser
	 */
	protected $admin_user;

	/**
	 * The URL of the application page that performs that password reset
	 * action
	 *
	 * @var string
	 */
	protected $password_link;




	/**
	 * Creates a new reset password email
	 *
	 * @param AdminApplication $app the application sending the mail message.
	 * @param AdminUser $user the user for which to create the email.
	 * @param string $password_link the URL of the application page that
	 *                               performs the password reset.
	 */
	public function __construct(
		AdminApplication $app,
		AdminUser $user,
		$password_link
	) {
		parent::__construct($app);

		$this->password_link = $password_link;
		$this->admin_user = $user;
	}




	/**
	 * Sends this mail message
	 */
	public function send()
	{
		if ($this->admin_user->email === null)
			throw new AdminException('User requires an email address to '.
				'reset password. Make sure email is loaded on the user '.
				'object.');

		if ($this->admin_user->name === null)
			throw new AdminException('User requires a fullname to reset '.
				'password. Make sure name is loaded on the user object.');

		$this->to_address = $this->admin_user->email;
		$this->to_name = $this->admin_user->name;
		$this->text_body = $this->getTextBody();
		$this->html_body = $this->getHtmlBody();

		parent::send();
	}




	/**
	 * Gets the plain-text content of this mail message
	 *
	 * @return string the plain-text content of this mail message.
	 */
	protected function getTextBody()
	{
		return sprintf($this->getFormattedBody(
			"%s\n\n%s\n\n%%s%s\n\n%s\n%s\n\n%s\n%s",
			$this->password_link),
			$this->getTextInstanceNote());
	}




	/**
	 * Gets the HTML content of this mail message
	 *
	 * @return string the HTML content of this mail message.
	 */
	protected function getHtmlBody()
	{
		return sprintf($this->getFormattedBody(
			'<p>%s</p><p>%s</p>%%s<p>%s</p><p>%s<br />%s</p><p>%s<br />%s</p>',
			sprintf('<a href="%1$s">%1$s</a>', $this->password_link)),
			$this->getHtmlInstanceNote());
	}




	protected function getHtmlInstanceNote()
	{
		$instance_note = '';

		if ($this->app->hasModule('SiteMultipleInstanceModule')) {
			$site_instance =
				$this->app->getModule('SiteMultipleInstanceModule');

			if (count($this->admin_user->instances) > 1) {
				$instance_note.= '<p>'.Admin::_(
					'Notice: Your admin password will also be updated '.
					'for the following sites on which your admin account is '.
					'used:').
					'</p><p><ul>';

				foreach ($this->admin_user->instances as $instance) {
					if ($instance->id !== $site_instance->getId()) {
						$sql = sprintf('select value from InstanceConfigSetting
							where instance = %s and name = \'site.title\'',
							$this->app->db->quote($instance->id, 'integer'));

						$title = SwatDB::queryOne($this->app->db, $sql, 'text');
						$instance_note.=
							'<li>'.SwatString::minimizeEntities($title).'</li>';
					}
				}

				$instance_note.= '</ul></p>';
			}
		}

		return $instance_note;
	}




	protected function getTextInstanceNote()
	{
		$instance_note = '';

		if ($this->app->hasModule('SiteMultipleInstanceModule')) {
			$site_instance =
				$this->app->getModule('SiteMultipleInstanceModule');

			if (count($this->admin_user->instances) > 1) {
				$instance_note.= Admin::_(
					'Notice: Your admin password will also be updated '.
					'for the following sites on which your admin account is '.
					'used:')."\n\n";

				foreach ($this->admin_user->instances as $instance) {
					if ($instance->id !== $site_instance->getId()) {
						$sql = sprintf('select value from InstanceConfigSetting
							where instance = %s and name = \'site.title\'',
							$this->app->db->quote($instance->id, 'integer'));

						$title = SwatDB::queryOne($this->app->db, $sql, 'text');
						$instance_note.= " - ".$title."\n";
					}
				}

				$instance_note.= "\n";
			}
		}

		return $instance_note;
	}




	protected function getFormattedBody($format_string, $formatted_link)
	{
		return sprintf($format_string,
			sprintf(Admin::_('This email is in response to your recent '.
			'request for a new password for your %s account. Your password '.
			'has not yet been changed. Please click on the following link '.
			'and follow the steps to change your account password:'),
				$this->app->config->site->title),

			$formatted_link,

			Admin::_('Clicking on the above link will take you to a page that '.
			'requires you to enter in and confirm a new password. Once you '.
			'have chosen and confirmed your new password you will be taken to '.
			'your account page.'),

			Admin::_('Why did I get this email?'),

			Admin::_('When someone forgets their password the best way '.
			'for us to verify their identity is to send an email to the '.
			'address listed in their account. By clicking on the link above '.
			'you are verifying that you requested a new password for your '.
			'account.'),

			Admin::_('I did not request a new password:'),

			sprintf(Admin::_('If you did not request a new password from %s '.
			'then someone may have accidentally entered your email when '.
			'requesting a new password. Have no fear! Your account '.
			'information is safe. Simply ignore this email and continue '.
			'using your existing password.'),
				$this->app->config->site->title));
	}


}

?>
