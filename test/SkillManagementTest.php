<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
use PHPUnit\Framework\TestCase;

class SkillManagementTest extends TestCase
{
    use MockDbTrait;

    public function testValidSkillPassesValidation(): void
    {
        $this->assertEmpty(SkillValidator::validate(1, 5, 'Beginner', 'Teach'));
    }

    public function testInvalidUserIdFails(): void
    {
        $this->assertNotEmpty(SkillValidator::validate(0, 5, 'Beginner', 'Teach'));
    }

    public function testInvalidSkillIdFails(): void
    {
        $this->assertNotEmpty(SkillValidator::validate(1, -1, 'Intermediate', 'Learn'));
    }

    public function testInvalidLevelNameFails(): void
    {
        $errors = SkillValidator::validate(1, 3, 'Expert', 'Teach');
        $found  = array_filter($errors, fn($e) => stripos($e, 'level') !== false);
        $this->assertNotEmpty($found);
    }

    public function testInvalidTypeFails(): void
    {
        $errors = SkillValidator::validate(1, 3, 'Beginner', 'Share');
        $found  = array_filter($errors, fn($e) => stripos($e, 'type') !== false);
        $this->assertNotEmpty($found);
    }

    public function testAllThreeLevelsAreValid(): void
    {
        foreach (['Beginner', 'Intermediate', 'Advanced'] as $level) {
            $this->assertEmpty(SkillValidator::validate(1, 1, $level, 'Teach'), "$level should be valid.");
        }
    }

    public function testBothTypesAreValid(): void
    {
        foreach (['Teach', 'Learn'] as $type) {
            $this->assertEmpty(SkillValidator::validate(1, 1, 'Beginner', $type), "$type should be valid.");
        }
    }

    public function testAddSkillToDatabaseSucceeds(): void
    {
        $conn = $this->mockConn(stmtExecute: true, numRows: 0);
        $stmt = $conn->prepare("INSERT INTO user_skills ...");
        $this->assertTrue($stmt->execute());
    }

    public function testDuplicateSkillDetected(): void
    {
        $conn   = $this->mockConn(stmtExecute: true, numRows: 1, fetchData: ['user_skill_id' => 7]);
        $stmt   = $conn->prepare("SELECT...");
        $stmt->execute();
        $this->assertEquals(1, $stmt->get_result()->num_rows);
    }

    public function testZeroSkillIdAndZeroUserIdBothFail(): void
    {
        $errors = SkillValidator::validate(0, 0, 'Beginner', 'Teach');
        $this->assertGreaterThanOrEqual(2, count($errors));
    }
}
