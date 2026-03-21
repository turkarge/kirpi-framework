<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\Support\TestCase;
use Core\Validation\Validator;
use Core\Exception\ValidationException;

class ValidationTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    public function test_required_rule(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(['name' => ''], ['name' => 'required']);
    }

    public function test_email_rule(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            ['email' => 'not-an-email'],
            ['email' => 'required|email']
        );
    }

    public function test_min_rule(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            ['password' => '123'],
            ['password' => 'required|min:8']
        );
    }

    public function test_max_rule(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            ['name' => str_repeat('a', 300)],
            ['name' => 'required|max:255']
        );
    }

    public function test_confirmed_rule(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            ['password' => 'secret123', 'password_confirmation' => 'different'],
            ['password' => 'required|confirmed']
        );
    }

    public function test_valid_data_passes(): void
    {
        $data = $this->validator->validate([
            'name'                  => 'Ramazan',
            'email'                 => 'ramazan@kirpi.dev',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ], [
            'name'     => 'required|string|min:3|max:50',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $this->assertEquals('Ramazan', $data['name']);
        $this->assertEquals('ramazan@kirpi.dev', $data['email']);
    }

    public function test_multiple_errors(): void
    {
        try {
            $this->validator->validate(
                ['name' => '', 'email' => 'invalid'],
                ['name' => 'required', 'email' => 'required|email']
            );

            $this->fail('Expected ValidationException');

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('name', $errors);
            $this->assertArrayHasKey('email', $errors);
        }
    }

    public function test_nullable_rule(): void
    {
        $data = $this->validator->validate(
            ['name' => 'Kirpi', 'bio' => null],
            ['name' => 'required', 'bio' => 'nullable']
        );

        $this->assertEquals('Kirpi', $data['name']);
    }

    public function test_in_rule(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            ['status' => 'invalid'],
            ['status' => 'required|in:active,inactive,pending']
        );
    }

    public function test_integer_rule(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            ['age' => 'not-a-number'],
            ['age' => 'required|integer']
        );
    }
}