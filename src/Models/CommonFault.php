<?php

namespace App\Models;

class CommonFault
{
    public $id;
    public $name;
    public $description;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
    }

    public static function fromFrontendFormat(array $frontendData): self
    {
        return new self([
            'id' => $frontendData['id'] ?? null,
            'name' => $frontendData['name'] ?? '',
        ]);
    }

    public function toFrontendFormat(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description
        ];
    }
}
