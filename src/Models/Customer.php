<?php

namespace App\Models;

class Customer
{
    public $id;
    public $type;
    public $name;
    public $email;
    public $phone;
    public $created_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->type = $data['type'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->created_at = $data['created_at'] ?? null;
    }

    public function isValid(): bool
    {
        return !empty(\trim($this->name)) && !empty(\trim($this->phone));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'created_at' => $this->created_at
        ];
    }
}
