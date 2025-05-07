<?php

namespace Modules\Core\Support;

use Modules\Core\Support\MailQueue;

use Modules\Core\Support\PDF\PDFFactory;

use Exception;
use Illuminate\Support\Facades\Mail;
use Modules\Core\Support\PDF\PDFFactory;

class MailQueue
{
    protected $error;

    public function create($object, $input)
    {
        return $object->mailQueue()->create([
            'from'       => json_encode(['email' => $object->user->email, 'name' => $object->user->name]),
            'to'         => json_encode($input['to']),
            'cc'         => json_encode(($input['cc']) ?: []),
            'bcc'        => json_encode(($input['bcc']) ?: []),
            'subject'    => $input['subject'],
            'body'       => $input['body'],
            'attach_pdf' => $input['attach_pdf'],
        ]);
    }

    public function send($id)
    {
        $mail = \Modules\Core\Models\MailQueue::find($id);

        if ($this->sendMail(
            $mail->from,
            $mail->to,
            $mail->cc,
            $mail->bcc,
            $mail->subject,
            $mail->body,
            $this->getAttachmentPath($mail)
        )
        ) {
            $mail->sent = 1;
            $mail->save();

            return true;
        }

        return false;
    }

    public function getError()
    {
        return $this->error;
    }

    private function sendMail($from, $to, $cc, $bcc, $subject, $body, $attachmentPath = null)
    {
        try {
            $htmlTemplate = (view()->exists('email_templates.html')) ? 'email_templates.html' : 'templates.emails.html';

            Mail::send([$htmlTemplate, 'templates.emails.text'], ['body' => $body], function ($message) use ($from, $to, $cc, $bcc, $subject, $attachmentPath): void {
                $from = json_decode($from, true);
                $to   = json_decode($to, true);
                $cc   = json_decode($cc, true);
                $bcc  = json_decode($bcc, true);

                $message->from($from['email'], $from['name']);
                $message->subject($subject);

                foreach ($to as $toRecipient) {
                    $message->to(trim($toRecipient));
                }

                foreach ($cc as $ccRecipient) {
                    if ($ccRecipient !== '') {
                        $message->cc(trim($ccRecipient));
                    }
                }

                foreach ($bcc as $bccRecipient) {
                    if ($bccRecipient !== '') {
                        $message->bcc(trim($bccRecipient));
                    }
                }

                if (config('ip.mailReplyToAddress')) {
                    $message->replyTo(config('ip.mailReplyToAddress'));
                }

                if ($attachmentPath) {
                    $message->attach($attachmentPath);
                }
            });

            if ($attachmentPath && file_exists($attachmentPath)) {
                unlink($attachmentPath);
            }

            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }
    }

    private function getAttachmentPath($mail)
    {
        if ($mail->attach_pdf) {
            $object = $mail->mailable;

            $pdfPath = base_path('storage/' . $object->pdf_filename);

            $pdf = PDFFactory::create();

            $pdf->save($object->html, $pdfPath);

            return $pdfPath;
        }
    }
}
