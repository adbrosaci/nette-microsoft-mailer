<?php declare(strict_types = 1);

namespace Adbros\MicrosoftMailer;

use GuzzleHttp\Psr7\Utils;
use Microsoft\Graph\Generated\Models\BodyType;
use Microsoft\Graph\Generated\Models\EmailAddress;
use Microsoft\Graph\Generated\Models\FileAttachment;
use Microsoft\Graph\Generated\Models\ItemBody;
use Microsoft\Graph\Generated\Models\Message as MicrosoftMessage;
use Microsoft\Graph\Generated\Models\Recipient;
use Microsoft\Graph\Generated\Users\Item\SendMail\SendMailPostRequestBody;
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Authentication\Oauth\ClientCredentialContext;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\Utils\Strings;
use Throwable;

class MicrosoftMailer implements Mailer
{

	protected GraphServiceClient $client;

	public function __construct(
		protected readonly string $tenantId,
		protected readonly string $clientId,
		protected readonly string $clientSecret,
		protected readonly string $defaultSender,
	)
	{
	}

	public function getGraphServiceClient(): GraphServiceClient
	{
		if (!isset($this->getGraphServiceClient)) {
			$this->client = $this->createGraphServiceClient();
		}

		return $this->client;
	}

	public function send(Message $mail, ?bool $saveToSentItems = null): void
	{
		$message = new MicrosoftMessage();

		// subject
		$message->setSubject($mail->getSubject());

		// recipients
		$message->setToRecipients($this->processRecipients($mail, 'To'));
		$message->setCcRecipients($this->processRecipients($mail, 'Cc'));
		$message->setBccRecipients($this->processRecipients($mail, 'Bcc'));

		// body
		$message->setBody($this->processBody($mail));

		// attachments
		$message->setAttachments($this->processAttachments($mail));

		$sendMailRequestBody = new SendMailPostRequestBody();
		$sendMailRequestBody->setMessage($message);

		if ($saveToSentItems !== null) {
			$sendMailRequestBody->setSaveToSentItems($saveToSentItems);
		}

		if ($mail->getReturnPath() !== null) {
			$sender = $mail->getReturnPath();
		} elseif (is_array($mail->getHeader('From'))) {
			$sender = key($mail->getHeader('From'));
			assert(is_string($sender));
		} else {
			$sender = $this->defaultSender;
		}

		try {
			$this->getGraphServiceClient()
				->users()
				->byUserId($sender)
				->sendMail()
				->post($sendMailRequestBody)
				->wait();
		} catch (Throwable $e) {
			throw new SendException($e->getMessage());
		}
	}

	/**
	 * @return array<Recipient>
	 */
	protected function processRecipients(Message $message, string $recipientType): array
	{
		$emails = (array) $message->getHeader($recipientType);

		$recipients = [];

		foreach ($emails as $email => $name) {
			$from = new EmailAddress();
			$from->setAddress($email);
			$from->setName(is_string($name) ? $name : null);

			$recipient = new Recipient();
			$recipient->setEmailAddress($from);

			$recipients[] = $recipient;
		}

		return $recipients;
	}

	protected function processBody(Message $message): ItemBody
	{
		$itemBody = new ItemBody();

		if ($message->getHtmlBody() !== '') {
			$itemBody->setContent($message->getHtmlBody());

			$bodyType = new BodyType(BodyType::HTML);
			$itemBody->setContentType($bodyType);
		} else {
			$itemBody->setContent($message->getBody());

			$bodyType = new BodyType(BodyType::TEXT);
			$itemBody->setContentType($bodyType);
		}

		return $itemBody;
	}

	/**
	 * @return array<FileAttachment>
	 */
	protected function processAttachments(Message $message): array
	{
		$attachments = [];

		foreach ($message->getAttachments() as $part) {
			assert(is_string($part->getHeader('Content-Disposition')));
			$name = Strings::match($part->getHeader('Content-Disposition'), '#filename="(.*)"#');
			assert(is_array($name) && count($name) === 2);

			$attachment = new FileAttachment();
			$attachment->setName($name[1]);
			assert(is_string($part->getHeader('Content-Type')));
			$attachment->setContentType($part->getHeader('Content-Type'));
//			$attachment->setOdataType('#microsoft.graph.fileAttachment');
			$attachment->setContentBytes(Utils::streamFor(base64_encode($part->getBody())));
			$attachment->setIsInline(false);

			$attachments[] = $attachment;
		}

		return $attachments;
	}

	protected function createGraphServiceClient(): GraphServiceClient
	{
		$tokenRequestContext = new ClientCredentialContext(
			$this->tenantId,
			$this->clientId,
			$this->clientSecret,
		);

		return new GraphServiceClient(
			$tokenRequestContext,
			[],
		);
	}

}
