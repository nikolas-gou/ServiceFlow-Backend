<?php

namespace App\Models;

class MotorCrossSectionLinks
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

    public static function fromFrontendFormat(array $frontendData): self
    {
        return new self([
            'id' => $frontendData['id'] ?? null,
            'motor_id' => $frontendData['motorID'] ?? null,
            'cross_section' => $frontendData['crossSection'] ?? null,
            'type' => $frontendData['type'] ?? 'standard'
        ]);
    }

    public function toFrontendFormat(): array
    {
        return [
            'id' => $this->id,
            'motorID' => $this->motor_id,
            'crossSection' => $this->cross_section,
            'type' => $this->type
        ];
    }
}
