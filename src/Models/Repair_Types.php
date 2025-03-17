<?php
namespace App\Models;

class Repair_Types {
    public $id;
    public $name;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}