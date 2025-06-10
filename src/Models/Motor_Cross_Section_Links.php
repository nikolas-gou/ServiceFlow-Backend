<?php

namespace App\Models;

class Motor_Cross_Section_Links
{
    public  $id;
    public $motor_id;
    public $cross_section;
    public $type;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->motor_id = $data['motor_id'] ?? null;
        $this->cross_section = $data['cross_section'] ?? null;
        $this->type = $data['type'] ?? "standard";
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            "motor_id" => $this->motor_id,
            'cross_section' => $this->cross_section,
            "type" => $this->type
        ];
    }
}
