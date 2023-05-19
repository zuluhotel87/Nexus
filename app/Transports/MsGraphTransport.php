<?php

declare(strict_types=1);

namespace App\Transports;

use App\Models\Attachment;
use Dcblogdev\MsGraph\Facades\MsGraph;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

use function collect;
use function storage_path;

class MsGraphTransport extends AbstractTransport
{
    /**
     * {@inheritDoc}
     */
    protected function doSend($message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $to = collect($email->getTo())->map(static function ($email) {
            return $email->getAddress();
        })->all();

        $emailObject = MsGraph::emails()
            ->to($to)
            ->subject($email->getSubject())
            ->body($email->getHtmlBody());

        if ($email->getCc()) {
            $cc = collect($email->getCc())->map(static function ($email) {
                return $email->getAddress();
            })->all();

            $emailObject->cc($cc);
        }

        if ($email->getBcc()) {
            $bcc = collect($email->getBcc())->map(static function ($email) {
                return $email->getAddress();
            })->all();

            $emailObject->bcc($bcc);
        }

        if ($email->getAttachments()) {
            $attachments = [];

            foreach ($email->getAttachments() as $attachment) {
                $hashName = $attachment->getName();
                $attachment = Attachment::firstWhere('hash_name', $hashName);
                $filepath = storage_path(Attachment::STORAGE_PATH) . '/' . Attachment::PATH_ORDERS . '/' . $hashName;

                $attachments[] = [
                    'filepath' => $filepath,
                    'name' => $attachment->file_name,
                ];
            }

            $emailObject->attachments($attachments);
        }

        $emailObject->send();
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'microsoft-graph';
    }
}
