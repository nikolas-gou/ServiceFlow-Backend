<?php

namespace App\Models;

use App\Models\Motor_Cross_Section_Links;

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
    public $connectionism;
    public $volt;
    public $poles;
    public $how_many_coils_with;
    public $type_of_step;
    public $type_of_motor;
    public $type_of_volt;
    public $created_at;
    public $customer_id;
    public $motor_cross_section_links;

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
        $this->connectionism = $data['connectionism'] ?? "simple";
        $this->volt = $data['volt'] ?? "380VY";
        $this->poles = $data['poles'] ?? null;
        $this->how_many_coils_with = $data['how_many_coils_with'] ?? 1;
        $this->type_of_step = $data['type_of_step'] ?? "standard";
        $this->type_of_motor = $data['type_of_motor'] ?? "el_motor";
        $this->type_of_volt = $data['type_of_volt'] ?? "3-phase";
        $this->created_at = $data['created_at'] ?? "";
        $this->customer_id = $data['customer_id'] ?? null;
        $this->motor_cross_section_links = $data['motor_cross_section_links'] ?? null;
    }

    public static function fromFrontendFormat(array $frontendData): self
    {
        $dbData = [
            'id' => $frontendData['id'] ?? null,
            'serial_number' => $frontendData['serialNumber'] ?? null,
            'manufacturer' => $frontendData['manufacturer'] ?? '',
            'kw' => $frontendData['kw'] ?? null,
            'hp' => $frontendData['hp'] ?? null,
            'rpm' => $frontendData['rpm'] ?? '1490',
            'step' => $frontendData['step'] ?? null,
            'half_step' => $frontendData['halfStep'] ?? null,
            'helper_step' => $frontendData['helperStep'] ?? null,
            'helper_half_step' => $frontendData['helperHalfStep'] ?? null,
            'spiral' => $frontendData['spiral'] ?? null,
            'half_spiral' => $frontendData['halfSpiral'] ?? null,
            'helper_spiral' => $frontendData['helperSpiral'] ?? null,
            'helper_half_spiral' => $frontendData['helperHalfSpiral'] ?? null,
            'connectionism' => $frontendData['connectionism'] ?? 'simple',
            'volt' => $frontendData['volt'] ?? '380VY',
            'poles' => $frontendData['poles'] ?? '6',
            'how_many_coils_with' => $frontendData['howManyCoilsWith'] ?? '1',
            'type_of_step' => $frontendData['typeOfStep'] ?? 'standard',
            'type_of_motor' => $frontendData['typeOfMotor'] ?? 'el_motor',
            'type_of_volt' => $frontendData['typeOfVolt'] ?? '3-phase',
            'created_at' => $frontendData['createdAt'] ?? null,
            'customer_id' => $frontendData['customerID'] ?? null,
            'motor_cross_section_links' => isset($frontendData['motorCrossSectionLinks']) && is_array($frontendData['motorCrossSectionLinks'])
                ? array_map(fn($linkData) => Motor_Cross_Section_Links::fromFrontendFormat($linkData), $frontendData['motorCrossSectionLinks'])
                : []
        ];

        return new self($dbData);
    }

    public function toFrontendFormat(): array
    {
        return [
            'id' => $this->id,
            'serialNumber' => $this->serial_number,
            'manufacturer' => $this->manufacturer,
            'kw' => $this->kw,
            'hp' => $this->hp,
            'rpm' => $this->rpm,
            'step' => $this->step,
            'halfStep' => $this->half_step,
            'helperStep' => $this->helper_step,
            'helperHalfStep' => $this->helper_half_step,
            'spiral' => $this->spiral,
            'halfSpiral' => $this->half_spiral,
            'helperSpiral' => $this->helper_spiral,
            'helperHalfSpiral' => $this->helper_half_spiral,
            'connectionism' => $this->connectionism,
            'volt' => $this->volt,
            'poles' => $this->poles,
            'howManyCoilsWith' => $this->how_many_coils_with,
            'typeOfStep' => $this->type_of_step,
            'typeOfMotor' => $this->type_of_motor,
            'typeOfVolt' => $this->type_of_volt,
            'createdAt' => $this->created_at,
            'customerID' => $this->customer_id,
            'motorCrossSectionLinks' => is_array($this->motor_cross_section_links)
                ? array_map(fn($link) => $link->toFrontendFormat(), $this->motor_cross_section_links)
                : []
        ];
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
            'connectionism' => $this->connectionism,
            'volt' => $this->volt,
            'poles' => $this->poles,
            'how_many_coils_with' => $this->how_many_coils_with,
            'type_of_step' => $this->type_of_step,
            "type_of_motor" => $this->type_of_motor,
            "type_of_volt" => $this->type_of_volt,
            'created_at' => $this->created_at,
            'customer_id' => $this->customer_id,
            "motor_cross_section_links" => $this->motor_cross_section_links ? $this->motor_cross_section_links->toArray() : null
        ];
    }
}
