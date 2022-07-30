<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MailerTest extends WebTestCase
{
    public function testMailerAssertions(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertEmailCount(1);
        $event = self::getMailerEvent(0);
        self::assertEmailIsQueued($event);

        $email = self::getMailerMessage(0);
        self::assertEmailHeaderSame($email, 'To', 'fabien@example.com');
        self::assertEmailTextBodyContains($email, 'Bar');
        self::assertEmailAttachmentCount($email, 1);
    }
}
