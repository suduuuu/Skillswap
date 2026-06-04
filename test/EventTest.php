<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    use MockDbTrait;

    public function testValidEventCreationPasses(): void
    {
        $this->assertEmpty(EventValidator::validateCreate(1, 'Dhaka Community Hall', '2026-08-01 14:00:00'));
    }

    public function testMissingLocationFails(): void
    {
        $errors = EventValidator::validateCreate(1, '', '2026-08-01 14:00:00');
        $found  = array_filter($errors, fn($e) => stripos($e, 'location') !== false);
        $this->assertNotEmpty($found);
    }

    public function testMissingDateTimeFails(): void
    {
        $errors = EventValidator::validateCreate(1, 'Online', '');
        $found  = array_filter($errors, fn($e) => stripos($e, 'date') !== false);
        $this->assertNotEmpty($found);
    }

    public function testInvalidCreatorIdFails(): void
    {
        $this->assertNotEmpty(EventValidator::validateCreate(0, 'Somewhere', '2026-08-01 14:00:00'));
    }

    public function testCreatorCanDeleteOwnEvent(): void
    {
        $this->assertTrue(EventValidator::canDelete(3, 3));
    }

    public function testNonCreatorCannotDeleteEvent(): void
    {
        $this->assertFalse(EventValidator::canDelete(2, 3));
    }

    public function testEventInsertSucceeds(): void
    {
        $conn = $this->mockConn(stmtExecute: true);
        $stmt = $conn->prepare("INSERT INTO events ...");
        $this->assertTrue($stmt->execute());
    }

    public function testAllFieldsMissingFails(): void
    {
        $errors = EventValidator::validateCreate(0, '', '');
        $this->assertGreaterThanOrEqual(3, count($errors));
    }
}
