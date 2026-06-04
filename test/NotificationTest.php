<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    use MockDbTrait;

    public function testCreateNotificationSucceeds(): void
    {
        $conn   = $this->mockConn(stmtExecute: true);
        $helper = new NotificationHelper($conn);
        $this->assertTrue($helper->create(1, 'swap_request', 'Alice sent you a swap request!'));
    }

    public function testCreateNotificationWithInvalidUserFails(): void
    {
        $helper = new NotificationHelper($this->mockConn());
        $this->assertFalse($helper->create(0, 'swap_request', 'Some message'));
    }

    public function testCreateNotificationWithEmptyTypeFails(): void
    {
        $helper = new NotificationHelper($this->mockConn());
        $this->assertFalse($helper->create(1, '', 'Some message'));
    }

    public function testCreateNotificationWithEmptyMessageFails(): void
    {
        $helper = new NotificationHelper($this->mockConn());
        $this->assertFalse($helper->create(1, 'swap_request', ''));
    }

    public function testMarkAllReadSucceeds(): void
    {
        $helper = new NotificationHelper($this->mockConn(stmtExecute: true));
        $this->assertTrue($helper->markAllRead(1));
    }

    public function testMarkAllReadWithInvalidUserFails(): void
    {
        $helper = new NotificationHelper($this->mockConn());
        $this->assertFalse($helper->markAllRead(-1));
    }

    public function testGetUnreadReturnsRows(): void
    {
        $rows = [
            ['notification_id' => 1, 'type' => 'swap_request', 'is_read' => 0],
            ['notification_id' => 2, 'type' => 'session',      'is_read' => 0],
        ];
        $helper = new NotificationHelper($this->mockConn(stmtExecute: true, numRows: 2, fetchAll: $rows));
        $result = $helper->getUnread(1);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testGetUnreadWithInvalidUserReturnsEmpty(): void
    {
        $helper = new NotificationHelper($this->mockConn());
        $result = $helper->getUnread(0);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testCreateSessionNotificationType(): void
    {
        $helper = new NotificationHelper($this->mockConn(stmtExecute: true));
        $this->assertTrue($helper->create(2, 'session', 'Your session was accepted!'));
    }
}
