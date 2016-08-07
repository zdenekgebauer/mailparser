<?php
/**
 * ZdenekGebauer\MailParser\Message
 */

namespace ZdenekGebauer\MailParser;

/**
 * e-mail message
 */
class Message {

	/**
	 * @var array headers of message
	 */
	private $headers = [];

	/**
	 * @var Email sender of message
	 */
	private $sender;

	/**
	 * @var Email[] recipents
	 */
	private $recipients = [];

	/**
	 * @var Email[] CC recipents
	 */
	private $ccRecipients = [];

	/**
	 * @var Email[] BCC recipents
	 */
	private $bccRecipients = [];

	/**
	 * @var string text of message in plaintext
	 */
	private $bodyText = '';

	/**
	 * @var string text of message in HTML
	 */
	private $bodyHtml = '';

	/**
	 * @var Attachment[] attached files
	 */
	private $attachments = [];

	/**
	 * constructor
	 * @param array $headers
	 * @param string $bodyText
	 * @param string $bodyHtml
	 * @param Email $sender
	 * @param array $recipients
	 * @param array $attachments
	 * @param array $ccRecipients
	 * @param array $bccRecipients
	 */
	public function __construct(
		array $headers, $bodyText, $bodyHtml, Email $sender, array $recipients, array $attachments,
		array $ccRecipients, array $bccRecipients
	) {
		$this->headers = $headers;
		$this->bodyText = $bodyText;
		$this->bodyHtml = $bodyHtml;
		$this->sender = $sender;
		$this->recipients = $recipients;
		$this->attachments = $attachments;
		$this->ccRecipients = $ccRecipients;
		$this->bccRecipients = $bccRecipients;
	}

	/**
	 * returns sender of message
	 * @return Email
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * returns subject of message
	 * @return string
	 */
	public function getSubject() {
		return $this->getHeader('subject');
	}

	/**
	 * returns header of message
	 * @param string $headerName
	 * @return string|null
	 */
	public function getHeader($headerName) {
		return isset($this->headers[$headerName]) ? $this->headers[$headerName] : null;
	}

	/**
	 * returns plaintext of message
	 * @return string
	 */
	public function getBodyText() {
		return $this->bodyText;
	}

	/**
	 * returns HTML content of message
	 * @return string
	 */
	public function getBodyHtml() {
		return $this->bodyHtml;
	}

	/**
	 * returns recipients
	 * @return Email[]
	 */
	public function getRecipients() {
		return $this->recipients;
	}

	/**
	 * returns CC recipients
	 * @return Email[]
	 */
	public function getCcRecipients() {
		return $this->ccRecipients;
	}

	/**
	 * returns BCC recipients
	 * @return Email[]
	 */
	public function getBccRecipients() {
		return $this->bccRecipients;
	}

	/**
	 * returns attachments
	 * @return Attachment[]
	 */
	public function getAttachments() {
		return $this->attachments;
	}
}
