<?php
/**
 * ZdenekGebauer\MailParser\Attachment
 */

namespace ZdenekGebauer\MailParser;

/**
 * attachment assigned to message
 */
class Attachment {

	/**
	 * @var string filename
	 */
	private $file;

	/**
	 * @var string MIME type
	 */
	private $contentType;

	/**
	 * @var string encoding
	 */
	private $encoding;

	/**
	 * constructor
	 * @param string $file
	 * @param string $contentType
	 * @param string $encoding
	 */
	public function __construct($file, $contentType, $encoding) {
		$this->file = $file;
		$this->contentType = $contentType;
		$this->encoding = $encoding;
	}

	/**
	 * returns name of file
	 * @return string
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * returns MIME type
	 * @return string
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * returns encoding
	 * @return string
	 */
	public function getEncoding() {
		return $this->encoding;
	}
}