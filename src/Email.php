<?php
/**
 * ZdenekGebauer\MailParser\Email
 */

namespace ZdenekGebauer\MailParser;

/**
 * e-mail address in message header
 */
class Email {

	/**
	 * @var string e-mail address
	 */
	private $address;

	/**
	 * @var string name
	 */
	private $name;

	/**
	 * constructor
	 * @param string $address
	 * @param string $name
	 */
	public function __construct($address, $name) {
		$this->address = trim($address, '<>');
		$this->name = trim($name);
	}

	/**
	 * returns e-mail address
	 * @return string
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * returns name
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
}
