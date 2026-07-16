<?php

namespace App\Models;

class Connection
{
    public $id;
    public $connection_type;
    public $type_of_volt;
    public $poles;
    public $rpm;
    public $step;
    public $half_step;
    public $type_of_step;
    public $coils;
    public $caves;
    public $description;
    public $created_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->connection_type = $data['connection_type'] ?? null;
        $this->type_of_volt = $data['type_of_volt'] ?? null;
        $this->poles = $data['poles'] ?? null;
        $this->rpm = $data['rpm'] ?? null;
        $this->step = $data['step'] ?? null;
        $this->half_step = $data['half_step'] ?? null;
        $this->type_of_step = $data['type_of_step'] ?? 'standard';
        $this->coils = $data['coils'] ?? null;
        $this->caves = $data['caves'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
    }

    public static function fromFrontendFormat(array $frontendData): self
    {
        $dbData = [
            'id' => $frontendData['id'] ?? null,
            'connection_type' => $frontendData['connectionType'] ?? null,
            'type_of_volt' => $frontendData['typeOfVolt'] ?? null,
            'poles' => $frontendData['poles'] ?? null,
            'rpm' => $frontendData['rpm'] ?? null,
            'step' => $frontendData['step'] ?? null,
            'half_step' => $frontendData['halfStep'] ?? null,
            'type_of_step' => $frontendData['typeOfStep'] ?? 'standard',
            'coils' => $frontendData['coils'] ?? null,
            'caves' => $frontendData['caves'] ?? null,
            'description' => $frontendData['description'] ?? null,
            'created_at' => $frontendData['createdAt'] ?? null,
        ];

        return new self($dbData);
    }

    public function toFrontendFormat(): array
    {
        return [
            'id' => $this->id,
            'connectionType' => $this->connection_type,
            'typeOfVolt' => $this->type_of_volt,
            'poles' => $this->poles,
            'rpm' => $this->rpm,
            'step' => $this->step,
            'halfStep' => $this->half_step,
            'typeOfStep' => $this->type_of_step,
            'coils' => $this->coils,
            'caves' => $this->caves,
            'description' => $this->description,
            'createdAt' => $this->created_at,
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'connection_type' => $this->connection_type,
            'type_of_volt' => $this->type_of_volt,
            'poles' => $this->poles,
            'rpm' => $this->rpm,
            'step' => $this->step,
            'half_step' => $this->half_step,
            'type_of_step' => $this->type_of_step,
            'coils' => $this->coils,
            'caves' => $this->caves,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
