<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
use PHPUnit\Framework\TestCase;

class UserAuthenticationTest extends TestCase
{
    public function testValidRegistrationReturnsNoErrors(): void
    {
        $errors = AuthValidator::validateRegistration([
            'first_name' => 'Alice', 'last_name' => 'Smith',
            'username'   => 'alice99', 'email'   => 'alice@example.com',
            'password'   => 'secret123', 'city'  => 'New York',
        ]);
        $this->assertEmpty($errors);
    }

    public function testMissingFirstNameFails(): void
    {
        $errors = AuthValidator::validateRegistration([
            'first_name' => '', 'last_name' => 'Smith',
            'username'   => 'alice99', 'email' => 'alice@example.com',
            'password'   => 'secret123',
        ]);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('first_name', $errors[0]);
    }

    public function testMissingLastNameFails(): void
    {
        $errors = AuthValidator::validateRegistration([
            'first_name' => 'Alice', 'last_name' => '',
            'username'   => 'alice99', 'email'   => 'alice@example.com',
            'password'   => 'secret123',
        ]);
        $this->assertNotEmpty($errors);
    }

    public function testInvalidEmailFails(): void
    {
        $errors = AuthValidator::validateRegistration([
            'first_name' => 'Alice', 'last_name' => 'Smith',
            'username'   => 'alice99', 'email'   => 'not-an-email',
            'password'   => 'secret123',
        ]);
        $found = array_filter($errors, fn($e) => stripos($e, 'email') !== false);
        $this->assertNotEmpty($found);
    }

    public function testShortPasswordFails(): void
    {
        $errors = AuthValidator::validateRegistration([
            'first_name' => 'Alice', 'last_name' => 'Smith',
            'username'   => 'alice99', 'email'   => 'alice@example.com',
            'password'   => '123',
        ]);
        $found = array_filter($errors, fn($e) => stripos($e, 'password') !== false);
        $this->assertNotEmpty($found);
    }

    public function testPasswordExactlyMinLengthPasses(): void
    {
        $errors = AuthValidator::validateRegistration([
            'first_name' => 'Alice', 'last_name' => 'Smith',
            'username'   => 'alice99', 'email'   => 'alice@example.com',
            'password'   => 'abc123',
        ]);
        $pwdErrors = array_filter($errors, fn($e) => stripos($e, 'password') !== false);
        $this->assertEmpty($pwdErrors);
    }

    public function testValidLoginInputReturnsNoErrors(): void
    {
        $errors = AuthValidator::validateLogin('user@example.com', 'mypassword');
        $this->assertEmpty($errors);
    }

    public function testEmptyEmailAndPasswordFails(): void
    {
        $errors = AuthValidator::validateLogin('', '');
        $this->assertCount(2, $errors);
    }

    public function testMalformedEmailOnLoginFails(): void
    {
        $errors = AuthValidator::validateLogin('badEmail', 'pass123');
        $this->assertNotEmpty($errors);
    }

    public function testHashedPasswordVerifiesCorrectly(): void
    {
        $plain = 'SuperSecret!99';
        $hash  = password_hash($plain, PASSWORD_DEFAULT);
        $this->assertTrue(AuthValidator::verifyPassword($plain, $hash));
    }

    public function testWrongPasswordDoesNotVerify(): void
    {
        $hash = password_hash('correct', PASSWORD_DEFAULT);
        $this->assertFalse(AuthValidator::verifyPassword('wrong', $hash));
    }

    public function testPlainTextFallbackVerification(): void
    {
        $this->assertTrue(AuthValidator::verifyPassword('devpass', 'devpass'));
    }

    public function testAllFieldsMissingReportsMultipleErrors(): void
    {
        $errors = AuthValidator::validateRegistration([]);
        $this->assertGreaterThanOrEqual(5, count($errors));
    }
}
