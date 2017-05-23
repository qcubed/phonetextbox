<?php

/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Control;

use QCubed as Q;
use QCubed\Exception\Caller;
use QCubed\Project\Application;
use QCubed\Project\Control\TextBox;
use QCubed\Type;

require_once (dirname(__DIR__) . "/i18n/i18n-lib.inc.php");

// we need a better way of reconfiguring JS assets
if (!defined('QCUBED_PHONETEXTBOX_ASSETS_URL')) {
    define('QCUBED_PHONETEXTBOX_ASSETS_URL', QCUBED_BASE_URL . '/phonetextbox/assets');
}

/**
 * Class PhoneTextBox
 *
 * This text box validates based on the North American phone format of (xxx) xxx-xxxx, and reformats the phone if it's entered differently.
 *
 * Blank items are allowed. If the user does not enter anything, then the area code will be removed so that it will be blank
 *
 * Usage:
 * $txtPhone = new \QCubed\Control\PhoneTextBox ($this);
 * $txtPhone->DefaultAreaCode = "650";
 * $txtPhone->Name = 'Home Phone';
 * $txtPhone->Text = $this->objPeople->HomePhone;
 *
 * @property string $DefaultAreaCode The default area code to prefill with.
 *
 * @authors	Michael Ho, Shannon Pekary, Alex Weinstein
 * @package QCubed\Control
 */
class PhoneTextBox extends TextBox {

	/** @var string */
	protected $strDefaultAreaCode = null;	// set this to the default area code to enter in the box when the field is entered.

    /**
     * PhoneTextBox constructor.
     * @param Q\Project\Control\ControlBase|Q\Project\Control\FormBase $objParentObject
     * @param null $strControlId
     */
	public function __construct($objParentObject, $strControlId = null) {
		parent::__construct($objParentObject, $strControlId);
		
		$this->addJavascriptFile(QCUBED_PHONETEXTBOX_ASSETS_URL . "/js/qcubed.phonetextbox.js");
	}


    /**
     * @return null|array
     */
	protected function makeJqOptions() {
		$jqOptions = null;
		if (!is_null($val = $this->DefaultAreaCode)) {$jqOptions['defaultAreaCode'] = $val;}
		return $jqOptions;
	}

    /**
     * @return string
     */
	public function getEndScript() {
        $strId = $this->getJqControlId();
        $jqOptions = $this->makeJqOptions();
        $strFunc = $this->getJqSetupFunction();

        if ($strId !== $this->ControlId && Application::isAjax()) {
            // If events are not attached to the actual object being drawn, then the old events will not get
            // deleted during redraw. We delete the old events here. This must happen before any other event processing code.
            Application::executeControlCommand($strId, 'off', Application::PRIORITY_HIGH);
        }

        // Attach the javascript widget to the html object
        if (empty($jqOptions)) {
            Application::executeControlCommand($strId, $strFunc, Application::PRIORITY_HIGH);
        } else {
            Application::executeControlCommand($strId, $strFunc, $jqOptions, Application::PRIORITY_HIGH);
        }

        return parent::getEndScript();
	}


    /**
     * @return string
     */
	public function getJqSetupFunction() {
		return 'phoneTextBox';
	}

    /**
     * @return bool
     */
	public function validate() {
		if (parent::validate()) {
			$this->strText = trim ($this->strText);
			if ($this->strText != "") {
				if ($this->strDefaultAreaCode) {
					$pattern = "(\\(||\\[)?\\d{3}(\\)||\\])?[-\\s.]+\\d{3}[-\\s.]+\\d{4}( x\\d+)?$"; // standard phone
				} else {
					$pattern = "((\\(||\\[)?\\d{3}(\\)||\\])?[-\\s.]+)?\\d{3}[-\\s.]+\\d{4}( x\\d+)?$"; // optional area code
				}
					
				if (! preg_match("/$pattern/", $this->strText)) {
					$this->ValidationError = Q\Phonetextbox\t("Invalid phone number");
					return false;
				}
			}
		} else
			return false;

		$this->strValidationError = "";
		return true;
	}

    /**
     * @param string $strName
     * @param string $mixValue
     * @return void
     * @throws Caller
     */
	public function __set($strName, $mixValue) {
		switch ($strName) {
			case "DefaultAreaCode":
				try {
                    $this->strDefaultAreaCode = Type::Cast($mixValue, Type::STRING);
				} catch (Caller $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
            break;

			case "Text":
			case "Value":
				parent::__set($strName, $mixValue);
				// Reformat after a change. Can't detect this kind of change just in JavaScript.
				Application::executeControlCommand($this->getJqControlId(), $this->getJqSetupFunction(), 'checkChanged', Application::PRIORITY_LOW);
				break;


			default:
				try {
					parent::__set($strName, $mixValue);
				} catch (Caller $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
		}
	}

    /**
     * @param string $strName
     * @return mixed
     * @throws Caller
     */
	public function __get($strName) {
		switch ($strName) {
			case "DefaultAreaCode": return $this->strDefaultAreaCode;

			default:
				try {
					return parent::__get($strName);
				} catch (Caller $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
		}
	}

    /**
	 * @return Q\ModelConnector\Param[]
	 */
	public static function getModelConnectorParams() {
		return array_merge(parent::GetModelConnectorParams(), array(
			new Q\ModelConnector\Param (get_called_class(), 'DefaultAreaCode', '', Type::STRING)
		));
	}
}
