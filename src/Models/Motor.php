<?php

namespace App\Models;

class Motor
{
    public  $id;
    public $serial_number;
    public $manufacturer;
    public $kw;
    public $hp;
    public $rpm;
    public $step;
    public $half_step;
    public $helper_step;
    public $helper_half_step;
    public $spiral;
    public $half_spiral;
    public $helper_spiral;
    public $helper_half_spiral;
    public $cross_section;
    public $half_cross_section;
    public $helper_cross_section;
    public $helper_half_cross_section;
    public $connectionism;
    public $volt;
    public $poles;
    public $type_of_step;
    public $type_of_motor;
    public $type_of_volt;
    public $created_at;
    public $customer_id;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->serial_number = $data['serial_number'] ?? null;
        $this->manufacturer = $data['manufacturer'] ?? "";
        $this->kw = $data['kw'] ?? null;
        $this->hp = $data['hp'] ?? null;
        $this->rpm = $data['rpm'] ?? null;
        $this->step = $data['step'] ?? null;
        $this->half_step = $data['half_step'] ?? null;
        $this->helper_step = $data['helper_step'] ?? null;
        $this->helper_half_step = $data['helper_half_step'] ?? null;
        $this->spiral = $data['spiral'] ?? null;
        $this->half_spiral = $data['half_spiral'] ?? null;
        $this->helper_spiral = $data['helper_spiral'] ?? null;
        $this->helper_half_spiral = $data['helper_half_spiral'] ?? null;
        $this->cross_section = $data['cross_section'] ?? null;
        $this->half_cross_section = $data['half_cross_section'] ?? null;
        $this->helper_cross_section = $data['helper_cross_section'] ?? null;
        $this->helper_half_cross_section = $data['helper_half_cross_section'] ?? null;
        $this->connectionism = $data['connectionism'] ?? "simple";
        $this->volt = $data['volt'] ?? "380VY";
        $this->poles = $data['poles'] ?? null;
        $this->type_of_step = $data['type_of_step'] ?? "standard";
        $this->type_of_motor = $data['type_of_motor'] ?? "el_motor";
        $this->type_of_volt = $data['type_of_volt'] ?? "3-phase";
        $this->created_at = $data['created_at'] ?? "";
        $this->customer_id = $data['customer_id'] ?? null;
    }

    public function isValid(): bool
    {
        return !empty(\trim($this->manufacturer)) && !empty(\trim($this->manufacturer));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'serial_number' => $this->serial_number,
            'manufacturer' => $this->manufacturer,
            'kw' => $this->kw,
            'hp' => $this->hp,
            'rpm' => $this->rpm,
            'step' => $this->step,
            'half_step' => $this->half_step,
            'helper_step' => $this->helper_step,
            'helper_half_step' => $this->helper_half_step,
            'spiral' => $this->spiral,
            'half_spiral' => $this->half_spiral,
            'helper_spiral' => $this->helper_spiral,
            'helper_half_spiral' => $this->helper_half_spiral,
            'cross_section' => $this->cross_section,
            'half_cross_section' => $this->half_cross_section,
            'helper_cross_section' => $this->helper_cross_section,
            'helper_half_cross_section' => $this->helper_half_cross_section,
            'connectionism' => $this->connectionism,
            'volt' => $this->volt,
            'poles' => $this->poles,
            'type_of_step' => $this->type_of_step,
            "type_of_motor" => $this->type_of_motor,
            "type_of_volt" => $this->type_of_volt,
            'created_at' => $this->created_at,
            'customer_id' => $this->customer_id,
        ];
    }
}
