<?php

namespace App\Models;

use App\Models\MotorCrossSectionLinks;

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
    public $amps;
    public $poles;
    // Νέα πεδία για coils
    public $coils_count;
    public $half_coils_count;
    public $helper_coils_count;
    public $helper_half_coils_count;
    public $type_of_step;
    public $type_of_motor;
    public $type_of_volt;
    public $created_at;
    public $customer_id;
    public $motorCrossSectionLinks;

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
        $this->amps = $data['amps'] ?? null;
        $this->poles = $data['poles'] ?? null;
        // Νέα coils πεδία
        $this->coils_count = $data['coils_count'] ?? 1;
        $this->half_coils_count = $data['half_coils_count'] ?? 1;
        $this->helper_coils_count = $data['helper_coils_count'] ?? 1;
        $this->helper_half_coils_count = $data['helper_half_coils_count'] ?? 1;
        $this->type_of_step = $data['type_of_step'] ?? "standard";
        $this->type_of_motor = $data['type_of_motor'] ?? "el_motor";
        $this->type_of_volt = $data['type_of_volt'] ?? "3-phase";
        $this->created_at = $data['created_at'] ?? "";
        $this->customer_id = $data['customer_id'] ?? null;
        $this->motorCrossSectionLinks = $data['motor_cross_section_links'] ?? [];
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
            'amps' => $frontendData['amps'] ?? null,
            'poles' => $frontendData['poles'] ?? '6',
            // Νέα coils πεδία
            'coils_count' => $frontendData['coilsCount'] ?? 1,
            'half_coils_count' => $frontendData['halfCoilsCount'] ?? 1,
            'helper_coils_count' => $frontendData['helperCoilsCount'] ?? 1,
            'helper_half_coils_count' => $frontendData['helperHalfCoilsCount'] ?? 1,
            'type_of_step' => $frontendData['typeOfStep'] ?? 'standard',
            'type_of_motor' => $frontendData['typeOfMotor'] ?? 'el_motor',
            'type_of_volt' => $frontendData['typeOfVolt'] ?? '3-phase',
            'created_at' => $frontendData['createdAt'] ?? null,
            'customer_id' => $frontendData['customerID'] ?? null,
            'motor_cross_section_links' => isset($frontendData['motorCrossSectionLinks']) && is_array($frontendData['motorCrossSectionLinks'])
                ? array_map(fn($linkData) => MotorCrossSectionLinks::fromFrontendFormat($linkData), $frontendData['motorCrossSectionLinks'])
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
            'amps' => $this->amps,
            'poles' => $this->poles,
            // Νέα coils πεδία
            'coilsCount' => $this->coils_count,
            'halfCoilsCount' => $this->half_coils_count,
            'helperCoilsCount' => $this->helper_coils_count,
            'helperHalfCoilsCount' => $this->helper_half_coils_count,
            'typeOfStep' => $this->type_of_step,
            'typeOfMotor' => $this->type_of_motor,
            'typeOfVolt' => $this->type_of_volt,
            'createdAt' => $this->created_at,
            'customerID' => $this->customer_id,
            'motorCrossSectionLinks' => is_array($this->motorCrossSectionLinks)
                ? array_map(fn($link) => $link->toFrontendFormat(), $this->motorCrossSectionLinks)
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
            'amps' => $this->amps,
            'poles' => $this->poles,
            // Νέα coils πεδία
            'coils_count' => $this->coils_count,
            'half_coils_count' => $this->half_coils_count,
            'helper_coils_count' => $this->helper_coils_count,
            'helper_half_coils_count' => $this->helper_half_coils_count,
            'type_of_step' => $this->type_of_step,
            "type_of_motor" => $this->type_of_motor,
            "type_of_volt" => $this->type_of_volt,
            'created_at' => $this->created_at,
            'customer_id' => $this->customer_id,
            "motor_cross_section_links" => $this->motorCrossSectionLinks ? $this->motorCrossSectionLinks->toArray() : []
        ];
    }
}