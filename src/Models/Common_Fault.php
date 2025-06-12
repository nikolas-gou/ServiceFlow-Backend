<?php

namespace App\Models;

class Common_Fault
{
    public $id;
    public $name;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
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
        ];
    }
}
