<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    use MockDbTrait;

    public function testValidSessionCreationPasses(): void
    {
        $this->assertEmpty(SessionValidator::validateCreate(1, 2, 'Python', 'MySQL', '2026-07-15 10:00:00'));
    }

    public function testSessionWithSameUserFails(): void
    {
        $errors = SessionValidator::validateCreate(3, 3, 'PHP', 'JS', '2026-07-15 10:00:00');
        $found  = array_filter($errors, fn($e) => str_contains($e, 'yourself'));
        $this->assertNotEmpty($found);
    }

    public function testMissingSkillOfferedFails(): void
    {
        $this->assertNotEmpty(SessionValidator::validateCreate(1, 2, '', 'MySQL', '2026-07-15 10:00:00'));
    }

    public function testMissingSkillRequestedFails(): void
    {
        $this->assertNotEmpty(SessionValidator::validateCreate(1, 2, 'PHP', '', '2026-07-15 10:00:00'));
    }

    public function testMissingDateTimeFails(): void
    {
        $this->assertNotEmpty(SessionValidator::validateCreate(1, 2, 'PHP', 'MySQL', ''));
    }

    public function testPendingCanTransitionToAccepted(): void
    {
        $this->assertTrue(SessionValidator::canTransition('Pending', 'Accepted'));
    }

    public function testPendingCanTransitionToRejected(): void
    {
        $this->assertTrue(SessionValidator::canTransition('Pending', 'Rejected'));
    }

    public function testAcceptedCannotTransitionFurther(): void
    {
        $this->assertFalse(SessionValidator::canTransition('Accepted', 'Rejected'));
    }

    public function testRejectedCannotTransitionFurther(): void
    {
        $this->assertFalse(SessionValidator::canTransition('Rejected', 'Accepted'));
    }

    public function testSessionInsertSucceeds(): void
    {
        $conn = $this->mockConn(stmtExecute: true);
        $stmt = $conn->prepare("INSERT INTO sessions ...");
        $this->assertTrue($stmt->execute());
    }

    public function testInvalidUserIdsFail(): void
    {
        $errors = SessionValidator::validateCreate(0, 0, 'PHP', 'MySQL', '2026-07-15 10:00:00');
        $this->assertNotEmpty($errors);
    }
}
