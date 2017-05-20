<?php
	require('qcubed.inc.php');

	class SampleForm extends \QCubed\Control\FormBase {
		protected $txtWorkPhone;
		protected $txtHomePhone;

		protected function formCreate() {
			$this->txtWorkPhone = new \QCubed\Control\PhoneTextBox($this);
			$this->txtWorkPhone->DefaultAreaCode = '650';
			$this->txtHomePhone = new \QCubed\Control\PhoneTextBox($this);
			$this->txtHomePhone->DefaultAreaCode = '650';
		}
	}

	SampleForm::run('SampleForm');
