<?php

namespace App\Notification;

use App\Entity\Comment;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\Button\InlineKeyboardButton;
use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\InlineKeyboardMarkup;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\ChatNotificationInterface;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class CommentReviewNotification extends Notification implements EmailNotificationInterface, ChatNotificationInterface
{
    private $comment;
    private $reviewUrl;

    public function __construct(Comment $comment, string $reviewUrl)
    {
        $this->comment = $comment;
        $this->reviewUrl = $reviewUrl;

        parent::__construct('New comment posted');
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $message = EmailMessage::fromNotification($this, $recipient, $transport);
        $message->getMessage()
            ->htmlTemplate('emails/comment_notification.html.twig')
            ->context(['comment' => $this->comment])
        ;

        return $message;
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        if (preg_match('{\b(great|awesome)\b}i', $this->comment->getText())) {
            return ['email', 'chat/telegram'];
        }

        $this->importance(Notification::IMPORTANCE_LOW);

        return ['email'];
    }

    public function asChatMessage(RecipientInterface $recipient, string $transport = null): ?ChatMessage
    {
        if ('telegram' !== $transport) {
            return null;
        }

        $message = ChatMessage::fromNotification($this, $recipient, $transport);
        $message->subject($this->getSubject());
        $message->options((new TelegramOptions())
            ->chatId('@twigy_telegram_bot')
            ->parseMode('MarkdownV2')
            ->disableWebPagePreview(true)
            ->disableNotification(true)
            ->replyMarkup((new InlineKeyboardMarkup())
                ->inlineKeyboard([
                    (new InlineKeyboardButton('Visit symfony.com'))
                        ->url('https://symfony.com/'),
                ])));

        return $message;
    }
}
