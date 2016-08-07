<?php

namespace ZdenekGebauer\MailParser;

class ParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var string
	 */
	private $dir;

	protected function setUp() {
		$this->dir = dirname(__DIR__) . '/_data/';
	}

	public function testEmailText() {
		$message = (new Parser(file_get_contents($this->dir . 'message_text.eml')))->getMessage();
		$this->assertEquals('Subject of message', $message->getSubject());
		$this->assertEquals('Sender', $message->getSender()->getName());
		$this->assertEquals('sender@example.org', $message->getSender()->getAddress());
		$recipients = $message->getRecipients();
		$this->assertEquals(1, count($recipients));
		$this->assertEquals('Recipient', $recipients[0]->getName());
		$this->assertEquals('recipient@example.org', $recipients[0]->getAddress());
		$this->assertEquals([], $message->getCcRecipients());
		$this->assertEquals([], $message->getBccRecipients());
		$this->assertContains('Lorem ipsum dolor sit amet', $message->getBodyText());
		$this->assertEquals('', $message->getBodyHtml());
		$this->assertEquals([], $message->getAttachments());
	}

	public function testEmailMultipart() {
		$message = (new Parser(file_get_contents($this->dir . 'message_attachment.eml')))->getMessage();
		$this->assertEquals('subject - příliš žluťoučký kůň úpěl ďábelské ódy', $message->getSubject());
		$this->assertEquals('Sender', $message->getSender()->getName());
		$this->assertEquals('sender@example.org', $message->getSender()->getAddress());
		$recipients = $message->getRecipients();
		$this->assertEquals(2, count($recipients));
		$this->assertEquals('žluťoučký kůň', $recipients[0]->getName());
		$this->assertEquals('to@example.org', $recipients[0]->getAddress());
		$this->assertEquals('ďábelské ódy', $recipients[1]->getName());
		$this->assertEquals('to2@example.org', $recipients[1]->getAddress());

		$ccRecipients = $message->getCcRecipients();
		$this->assertEquals(2, count($ccRecipients));
		$this->assertEquals('žluťoučký kůň 2', $ccRecipients[0]->getName());
		$this->assertEquals('cc@example.org', $ccRecipients[0]->getAddress());
		$this->assertEquals('ďábelské ódy 2', $ccRecipients[1]->getName());
		$this->assertEquals('cc2@example.org', $ccRecipients[1]->getAddress());

		$bccRecipients = $message->getBccRecipients();
		$this->assertEquals(2, count($bccRecipients));
		$this->assertEquals('žluťoučký kůň 3', $bccRecipients[0]->getName());
		$this->assertEquals('bcc@example.org', $bccRecipients[0]->getAddress());
		$this->assertEquals('ďábelské ódy 3', $bccRecipients[1]->getName());
		$this->assertEquals('bcc2@example.org', $bccRecipients[1]->getAddress());

		$this->assertContains('příliš žluťoučký kůň úpěl ďábelské ódy', $message->getBodyText());
		$this->assertContains('příliš žluťoučký kůň úpěl ďábelské ódy', $message->getBodyHtml());

		$attachments = $message->getAttachments();
		$this->assertEquals(2, count($attachments));
		$this->assertEquals('dummy.txt', $attachments[0]->getFile());
		$this->assertEquals('text/plain', $attachments[0]->getContentType());
		$this->assertEquals('base64', $attachments[0]->getEncoding());
		$this->assertEquals('phpunit.gif', $attachments[1]->getFile());
		$this->assertEquals('image/gif', $attachments[1]->getContentType());
		$this->assertEquals('base64', $attachments[1]->getEncoding());
	}

	public function testEmailQuotedPrintable() {
		$message = (new Parser(file_get_contents($this->dir . 'quoted_printable.eml')))->getMessage();
		$this->assertEquals("Testing Manuel Lemos' MIME E-mail composing and sending PHP class: HTML message", $message->getSubject());
		$this->assertEquals('mlemos', $message->getSender()->getName());
		$this->assertEquals('mlemos@acm.org', $message->getSender()->getAddress());
		$recipients = $message->getRecipients();
		$this->assertEquals(1, count($recipients));
		$this->assertEquals('Manuel Lemos', $recipients[0]->getName());
		$this->assertEquals('mlemos@linux.local', $recipients[0]->getAddress());
		$this->assertEquals([], $message->getCcRecipients());
		$this->assertEquals([], $message->getBccRecipients());
		$this->assertContains('This is an HTML message.', $message->getBodyText());
		$this->assertContains("Testing Manuel Lemos' MIME E-mail composing and sending PHP class: HTML message", $message->getBodyHtml());
		$attachments = $message->getAttachments();
		$this->assertEquals(1, count($attachments));
		$this->assertEquals('attachment.txt', $attachments[0]->getFile());
		$this->assertEquals('text/plain', $attachments[0]->getContentType());
		$this->assertEquals('base64', $attachments[0]->getEncoding());
	}

	public function testEmailBase64() {
		$message = (new Parser(file_get_contents($this->dir . 'base64.eml')))->getMessage();
		$this->assertEquals('rssheap - weekly newsletter Wednesday, August 3, 2016', $message->getSubject());
		$this->assertEquals('"rssheap"', $message->getSender()->getName());
		$this->assertEquals('do-not-reply@rssheap.com', $message->getSender()->getAddress());
		$recipients = $message->getRecipients();
		$this->assertEquals(1, count($recipients));
		$this->assertEquals('', $recipients[0]->getName());
		$this->assertEquals('zdenek.gebauer@gmail.com', $recipients[0]->getAddress());
		$this->assertEquals([], $message->getCcRecipients());
		$this->assertEquals([], $message->getBccRecipients());
		$this->assertEquals('', $message->getBodyText());
		$this->assertContains('Top articles this week', $message->getBodyHtml());
		$this->assertEquals([], $message->getAttachments());
	}


	public function testEmailBase64Attachment() {
		$message = (new Parser(file_get_contents($this->dir . 'base64_attachment.eml')))->getMessage();
		$this->assertEquals('Test', $message->getSubject());
		$this->assertEquals('Bill Jncjkq', $message->getSender()->getName());
		$this->assertEquals('jncjkq@gmail.com', $message->getSender()->getAddress());
		$recipients = $message->getRecipients();
		$this->assertEquals(1, count($recipients));
		$this->assertEquals('', $recipients[0]->getName());
		$this->assertEquals('bookmarks@jncjkq.net', $recipients[0]->getAddress());
		$this->assertEquals([], $message->getCcRecipients());
		$this->assertEquals([], $message->getBccRecipients());
		$this->assertContains('Bill Jncjkq', $message->getBodyText());
		$this->assertContains('Bill Jncjkq', $message->getBodyHtml());
		$attachments = $message->getAttachments();
		$this->assertEquals(1, count($attachments));
		$this->assertEquals('bookmarks-really-short.html', $attachments[0]->getFile());
		$this->assertEquals('text/html', $attachments[0]->getContentType());
		$this->assertEquals('base64', $attachments[0]->getEncoding());
	}

	public function testQuotedSubject() {
		$message = (new Parser(file_get_contents($this->dir . 'quoted_subject.eml')))->getMessage();
		$this->assertEquals('Re: Test: "漢字" mid "漢字" tail', $message->getSubject());
		$this->assertEquals('Jamis Buck', $message->getSender()->getName());
		$this->assertEquals('jamis@37signals.com', $message->getSender()->getAddress());
		$recipients = $message->getRecipients();
		$this->assertEquals(1, count($recipients));
		$this->assertEquals('', $recipients[0]->getName());
		$this->assertEquals('jamis@37signals.com', $recipients[0]->getAddress());
		$this->assertEquals([], $message->getCcRecipients());
		$this->assertEquals([], $message->getBccRecipients());
		$this->assertContains('Jamis', $message->getBodyText());
		$this->assertEquals('', $message->getBodyHtml());
		$this->assertEquals([], $message->getAttachments());
	}


}