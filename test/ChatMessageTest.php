<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
use PHPUnit\Framework\TestCase;

class ChatMessageTest extends TestCase
{
    use MockDbTrait;

    public function testSendValidMessageSucceeds(): void
    {
        $middle = new MessageMiddle($this->mockConn(stmtExecute: true));
        $result = $middle->sendMessage(1, 2, 'Hello!');
        $this->assertTrue($result['success']);
    }

    public function testSendEmptyMessageFails(): void
    {
        $middle = new MessageMiddle($this->mockConn());
        $result = $middle->sendMessage(1, 2, '   ');
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('empty', $result['error']);
    }

    public function testSendMessageToSelfFails(): void
    {
        $middle = new MessageMiddle($this->mockConn());
        $result = $middle->sendMessage(5, 5, 'Hello me!');
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('yourself', $result['error']);
    }

    public function testSendMessageWithNonNumericIdFails(): void
    {
        $middle = new MessageMiddle($this->mockConn());
        $result = $middle->sendMessage('abc', 2, 'Hi');
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid', $result['error']);
    }

    public function testFilterWithInvalidValueFails(): void
    {
        $middle = new MessageMiddle($this->mockConn());
        $result = $middle->filterMessages(1, 5);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
    }

    public function testFilterUnreadMessages(): void
    {
        $rows   = [['MessageID' => 1, 'IsRead' => 0], ['MessageID' => 2, 'IsRead' => 0]];
        $middle = new MessageMiddle($this->mockConn(stmtExecute: true, numRows: 2, fetchAll: $rows));
        $result = $middle->filterMessages(1, 0);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testAddMessageReturnsTrueOnSuccess(): void
    {
        $data = new MessageData($this->mockConn(stmtExecute: true));
        $this->assertTrue($data->addMessage(1, 2, 'Test message'));
    }

    public function testAddMessageReturnsFalseOnDbFailure(): void
    {
        $data = new MessageData($this->mockConn(stmtExecute: false));
        $this->assertFalse($data->addMessage(1, 2, 'Test message'));
    }

    public function testListMessagesReturnsArray(): void
    {
        $rows = [['MessageID' => 1, 'MessageText' => 'Hey'], ['MessageID' => 2, 'MessageText' => 'Hi']];
        $data = new MessageData($this->mockConn(stmtExecute: true, numRows: 2, fetchAll: $rows));
        $this->assertCount(2, $data->listMessages(1));
    }

    public function testFindMessageReturnsRowWhenFound(): void
    {
        $row  = ['MessageID' => 7, 'MessageText' => 'Found!', 'IsRead' => 0];
        $data = new MessageData($this->mockConn(stmtExecute: true, numRows: 1, fetchData: $row));
        $found = $data->findMessage(7);
        $this->assertIsArray($found);
        $this->assertEquals('Found!', $found['MessageText']);
    }

    public function testFindMessageReturnsNullWhenNotFound(): void
    {
        $data = new MessageData($this->mockConn(stmtExecute: true, numRows: 0));
        $this->assertNull($data->findMessage(999));
    }

    public function testGetMessagesViaMiddleReturnsArray(): void
    {
        $rows   = [['MessageID' => 3, 'MessageText' => 'Howdy']];
        $middle = new MessageMiddle($this->mockConn(stmtExecute: true, numRows: 1, fetchAll: $rows));
        $this->assertIsArray($middle->getMessages(1));
    }
}
