<?php
/**
 * ZdenekGebauer\MailParser\Parser
 */

namespace ZdenekGebauer\MailParser;

/**
 * e-mail message
 */
class Parser {

	/**
	 * @var array headers of message
	 */
	private $headers = [];

	/**
	 * @var Email sender of message
	 */
	private $sender;

	/**
	 * @var Email[] recipients
	 */
	private $recipients = [];

	/**
	 * @var Email[] CC recipients
	 */
	private $ccRecipients = [];

	/**
	 * @var Email[] BCC recipients
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
	 * @var string boundary mark
	 */
	private $boundary = '';

	/**
	 * @var bool flag multipart message
	 */
	private $isMultipart = false;

	/**
	 * @var Attachment[] attached files
	 */
	private $attachments = [];

	/**
	 * @var string full content of message
	 */
	private $rawBody = '';

	/**
	 * constructor
	 * @param string $mail content of message including headers
	 */
	public function __construct($mail) {
		$mail = str_replace("\r\n", "\n", $mail);
		$tmp = explode("\n\n", $mail, 2);

		$this->parseHeaders($tmp[0]);
		if (isset($tmp[1])) {
			$this->rawBody = $tmp[1];
			//var_dump(htmlspecialchars($this->rawBody));
			$this->parseBody();
		}
	}

	/**
	 * returns parsed message
	 * @return Message
	 */
	public function getMessage() {
		$message = new Message(
			$this->headers,
			$this->bodyText,
			$this->bodyHtml,
			$this->sender,
			$this->recipients,
			$this->attachments,
			$this->ccRecipients,
			$this->bccRecipients
		);
		return $message;
	}

	/**
	 * parse headers of message
	 * @param string $headers
	 */
	private function parseHeaders($headers) {
		// some headers are multiline, indented with tab or space
		$headerLines = explode("\n", str_replace(["\n\t", "\n "], ' ', $headers));
		foreach ($headerLines as $line) {
			list($name, $value) = explode(':', $line, 2);
			$name = strtolower($name);
			$elements = imap_mime_header_decode(trim($value));
			switch ($name) {
				case 'from':
				case 'to':
				case 'cc':
				case 'bcc':
					$this->parseAddress($name, $elements);
					break;
				case 'subject':
					$this->parseSubject($name, $elements);
					break;
				case 'content-type':
					$this->checkMultipart($value);
					$this->headers[$name] = $elements[0]->text;
					break;
				default:
					$this->headers[$name] = trim($value);
			}
		}
	}

	/**
	 * parse e-mail addresses from header
	 * @param string $name
	 * @param array $elements
	 */
	private function parseAddress($name, array $elements) {
		$value = '';
		foreach ($elements as $element) {
			$value .= imap_utf8($element->text);
		}
		$items = array_map('trim', explode(',', $value));
		foreach ($items as $item) {
			$tmp = explode(' ', $item);
			$address = array_pop($tmp);
			switch ($name) {
				case 'from':
					$this->sender = new Email($address, join(' ', $tmp));
					break;
				case 'cc':
					$this->ccRecipients[] = new Email($address, join(' ', $tmp));
					break;
				case 'bcc':
					$this->bccRecipients[] = new Email($address, join(' ', $tmp));
					break;
				default:
					$this->recipients[] = new Email($address, join(' ', $tmp));
			}
		}
	}

	/**
	 * parse subject from header
	 * @param string $name
	 * @param array $elements
	 */
	private function parseSubject($name, array $elements) {
		$value = '';
		$count = count($elements);
		for ($i = 0; $i < $count; $i++) {
			$value .= imap_utf8($elements[$i]->text);
		}
		$this->headers[$name] = $value;
	}

	/**
	 * parse boundary mark from multipart message
	 * @param string $value
	 */
	private function checkMultipart($value) {
		if (strpos($value, 'multipart/') !== false) {
			$this->isMultipart = true;
			preg_match('/boundary=(.*)$/i', $value, $matches);
			$this->boundary = (isset($matches[1]) ? trim($matches[1], '"') : null);
		}
	}

	/**
	 * convert text to UTF-8
	 * @param string $fromCharset
	 * @param string $string
	 * @return string
	 */
	private function toUtf8($fromCharset, $string) {

		$isUtf8 = in_array(strtolower($fromCharset), ['default', 'utf-8']);
		return $isUtf8 ? $string : iconv($fromCharset, 'UTF-8//TRANSLIT', $string);
	}

	/**
	 * parse body of message
	 */
	private function parseBody() {
		if (!$this->isMultipart) {

			$charset = '';
			if (preg_match('/charset=(.*)[;|$]/Ui', $this->headers['content-type'], $matches) > 0) {
				$charset = trim($matches[1], '"');
			}
			$isHtml = (strpos($this->headers['content-type'], 'text/html') !== false);
			$this->parseBodyText($this->rawBody, $this->headers['content-transfer-encoding'], $charset, $isHtml);
			return;
		}
		$parts = $this->getPartsByBoundary($this->boundary);
		foreach ($parts as $part) {
			$this->parseBodyPart($part);
		}
	}

	/**
	 * split body by boundary mark
	 * @param string $boundary
	 * @return string[]
	 */
	private function getPartsByBoundary($boundary) {
		preg_match('#--' . $boundary . '(.*)--' . $boundary . '--#mis', $this->rawBody, $matches);
		$parts = explode('--' . $boundary, $matches[1]);
		$parts = array_map('trim', $parts);
		array_filter($parts);
		return $parts;
	}

	/**
	 * recursively parse part of body
	 * @param array $part
	 */
	private function parseBodyPart($part) {
		list ($header, $content) = explode("\n\n", $part, 2);
		$header = str_replace("\n ", '', $header);
		preg_match('/Content-Type: (.*)$/mi', $header, $matches);
		$contentType = trim($matches[1]);

		if (strpos($contentType, 'multipart/') !== false) {
			preg_match('/boundary=(.*)/i', $contentType, $matches);
			$boundary = trim($matches[1], '"');
			$parts = $this->getPartsByBoundary($boundary);
			foreach ($parts as $part) {
				$this->parseBodyPart($part);
			}
		} else {
			$this->parseBodyContent($header, $content, $contentType);
		}
	}

	/**
	 * parse content of part of body
	 * @param string $header
	 * @param string $content
	 * @param string $contentType
	 */
	private function parseBodyContent($header, $content, $contentType) {
		$charset = '';
		if (preg_match('/charset=(.*);/Ui', $contentType . ';', $matches) > 0) {
			$charset = trim($matches[1], '"');
		}
		$transferEncoding = '';
		if (preg_match('/Content-Transfer-Encoding: (.*)$/mi', $header, $matches) > 0) {
			$transferEncoding = trim($matches[1]);
		}
		$disposition = '';
		if (preg_match('/Content-Disposition: (.*)$/mi', $header, $matches) > 0) {
			$disposition = trim($matches[1]);
		}
		if (strpos($disposition, 'attachment;') !== false) {
			$tmp = explode(';', $contentType);
			$attachContentType = $tmp[0];
			preg_match('/name="(.*)"/i', $contentType, $matches);
			$this->attachments[] = new Attachment($matches[1], $attachContentType, $transferEncoding);
			return;
		}

		$isHtml = strpos($contentType, 'text/html') !== false;
		if ($disposition === '' && ($isHtml || (strpos($contentType, 'text/plain') !== false))) {
			$this->parseBodyText($content, $transferEncoding, $charset, $isHtml);
		}
	}

	/**
	 * parse text from content of part
	 * @param string $content
	 * @param string $transferEncoding
	 * @param string $charset
	 * @param bool $isHtml
	 */
	private function parseBodyText($content, $transferEncoding, $charset, $isHtml) {
		if ($transferEncoding === 'base64') {
			$content = base64_decode($content);
		}
		if ($transferEncoding === 'quoted-printable') {
			$content = quoted_printable_decode($content);
		}
		$content = $this->toUtf8($charset, $content);
		if ($isHtml) {
			$this->bodyHtml = $content;
		} else {
			$this->bodyText = $content;
		}
	}

}
