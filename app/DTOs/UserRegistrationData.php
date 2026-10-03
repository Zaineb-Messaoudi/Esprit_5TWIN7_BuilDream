<?php

namespace App\DTOs;

readonly class UserRegistrationData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone_number = null,
        public ?string $address = null,
        public string $role = 'user',
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            phone_number: $request->validated('phone_number'),
            address: $request->validated('address'),
            role: $request->validated('role', 'user'),
        );
    }
}
