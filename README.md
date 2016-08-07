# Parser of e-mail files

Parse single e-mail message into structured object

## Usage
<pre><code> 
$parser = new \ZdenekGebauer\MailParser\Parser(file_get_contents('message.eml'));
$message = $parser->getMessage();

echo 'Subject:', $message->getSubject(), "\n";
echo 'Sender:', $message->getSender()->getName(), $message->getSender()->getAddress(), "\n";

echo 'To:', "\n";
foreach($message->getRecipients() as $recipient) {
	echo $recipient->getName(), $recipient->getAddress(), "\n";
}

echo 'Cc:', "\n";
foreach($message->getCcRecipients() as $recipient) {
	echo $recipient->getName(), $recipient->getAddress(), "\n";
}

echo 'Bcc:', "\n";
foreach($message->getBccRecipients() as $recipient) {
	echo $recipient->getName(), $recipient->getAddress(), "\n";
}

echo 'Message text:', $message->getBodyText(), "\n";
echo 'Message HTML:', $message->getBodyHtml(), "\n";

echo 'Attachments:', "\n";
foreach($message->getAttachments() as $attachment) {
	echo $attachment->getFile(), $attachment->getContentType(), $attachment->getEncoding(), "\n";
}

</code></pre>
