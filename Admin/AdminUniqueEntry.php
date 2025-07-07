<?php

/**
 * A unique text entry widget.
 *
 * @copyright 2004-2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class AdminUniqueEntry extends SwatEntry
{
    /**
     * Whether or not this entry is alphanumeric.
     *
     * This property affects the processing of this control.
     *
     * @var bool
     *
     * @see AdminUniqueEntry::process()
     */
    public $alphanum = true;

    public function init()
    {
        parent::init();
        $this->size = 20;
    }

    /**
     * Processes this unique entry.
     *
     * Ensures the value entered by the user is unique and if it is not unique
     * attaches an error message to the control.
     */
    public function process()
    {
        parent::process();

        if ($this->alphanum && preg_match('/[^[:alnum:]\-_]/u', $this->value)) {
            $message = Admin::_('The %s field can only contain letters and ' .
                'numbers. Spaces and other special characters are not ' .
                'allowed.');

            $this->addMessage(new SwatMessage($message, 'error'));
        }
    }
}
