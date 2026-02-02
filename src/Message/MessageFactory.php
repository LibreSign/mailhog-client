<?php
declare(strict_types=1);

namespace LibreSign\Mailpit\Message;

use LibreSign\Mailpit\Message\Mime\MimePartCollection;

use function quoted_printable_decode;

class MessageFactory
{
    /**
     * @param mixed[] $mailpitResponse
     */
    public static function fromMailpitResponse(array $mailpitResponse): Message
    {
        $mimeParts = MimePartCollection::fromMailpitResponse($mailpitResponse['MIME']['Parts'] ?? []);
        $headers = Headers::fromMailpitResponse($mailpitResponse);

        return new Message(
            $mailpitResponse['ID'],
            Contact::fromString($headers->get('From')),
            ContactCollection::fromString($headers->get('To', '')),
            ContactCollection::fromString($headers->get('Cc', '')),
            ContactCollection::fromString($headers->get('Bcc', '')),
            $headers->get('Subject', ''),
            !$mimeParts->isEmpty()
                ? $mimeParts->getBody()
                : static::decodeBody($headers, $mailpitResponse['Content']['Body']),
            !$mimeParts->isEmpty() ? $mimeParts->getAttachments() : [],
            $headers
        );
    }

    private static function decodeBody(Headers $headers, string $body): string
    {
        if ($headers->get('Content-Transfer-Encoding') === 'quoted-printable') {
            return quoted_printable_decode($body);
        }

        return $body;
    }
}
