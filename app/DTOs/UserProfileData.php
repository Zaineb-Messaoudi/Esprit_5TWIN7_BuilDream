<?php

namespace App\DTOs;

readonly class UserProfileData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone_number = null,
        public ?string $address = null,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            phone_number: $request->validated('phone_number'),
            address: $request->validated('address'),
        );
    }
}
