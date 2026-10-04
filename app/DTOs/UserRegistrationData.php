<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

readonly class UserRegistrationData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone_number = null,
        public ?string $address = null,
        public ?UploadedFile $profile_photo = null,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            phone_number: $request->validated('phone_number'),
            address: $request->validated('address'),
            profile_photo: $request->file('profile_photo'),
        );
    }
}
