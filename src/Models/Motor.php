<?php
namespace App\Models;

class Motor {
    public  $id;
    public $serial_number;
    public $manufacturer;
    public $kw;
    public $hp;
    public $rpm;
    public $step;
    public $halfStep;
    public $helperStep;
    public $helperHalfStep;
    public $spiral;
    public $halfSpiral;
    public $helperSpiral;
    public $helperHalfSpiral;
    public $cross_section;
    public $halfCross_section;
    public $helperCross_section;
    public $helperHalfCross_section;
    public $connectionism;
    public $volt;
    public $poles;
    public $typeOfStep;
    public $typeOfMotor;
    public $typeOfVolt;
    public $created_at;
    public $customerID;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->serial_number = $data['serial_number'] ?? null;
        $this->manufacturer = $data['manufacturer'] ?? null;
        $this->kw = $data['kw'] ?? null;
        $this->hp = $data['hp'] ?? null;
        $this->rpm = $data['rpm'] ?? null;
        $this->step = $data['step'] ?? null;
        $this->halfStep = $data['halfStep'] ?? null;
        $this->helperStep = $data['helperStep'] ?? null;
        $this->helperHalfStep = $data['helperHalfStep'] ?? null;
        $this->spiral = $data['spiral'] ?? null;
        $this->halfSpiral = $data['halfSpiral'] ?? null;
        $this->helperSpiral = $data['helperSpiral'] ?? null;
        $this->helperHalfSpiral = $data['helperHalfSpiral'] ?? null;
        $this->cross_section = $data['cross_section'] ?? null;
        $this->halfCross_section = $data['halfCross_section'] ?? null;
        $this->helperCross_section = $data['helperCross_section'] ?? null;
        $this->helperHalfCross_section = $data['helperHalfCross_section'] ?? null;
        $this->connectionism = $data['connectionism'] ?? "simple";
        $this->volt = $data['volt'] ?? "380VY";
        $this->poles = $data['poles'] ?? null;
        $this->typeOfStep = $data['typeOfStep'] ?? "standard";
        $this->typeOfMotor = $data['typeOfMotor'] ?? "el_motor";
        $this->typeOfVolt = $data['typeOfVolt'] ?? "3-phase";
        $this->created_at = $data['created_at'] ?? null;
        $this->customerID = $data['customerID'] ?? null;
    }

    public function isValid(): bool {
        return !empty(\trim($this->manufacturer)) && !empty(\trim($this->manufacturer));
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'serial_number' => $this->serial_number,
            'manufacturer' => $this->manufacturer,
            'kw' => $this->kw,
            'hp' => $this->hp,
            'rpm' => $this->rpm,
            'step' => $this->step,
            'halfStep' => $this->halfStep,
            'helperStep' => $this->helperStep,
            'helperHalfStep' => $this->helperHalfStep,
            'spiral' => $this->spiral,
            'halfSpiral' => $this->halfSpiral,
            'helperSpiral' => $this->helperSpiral,
            'helperHalfSpiral' => $this->helperHalfSpiral,
            'cross_section' => $this->cross_section,
            'halfCross_section' => $this->halfCross_section,
            'helperCross_section' => $this->helperCross_section,
            'helperHalfCross_section' => $this->helperHalfCross_section,
            'connectionism' => $this->connectionism,
            'volt' => $this->volt,
            'poles' => $this->poles,
            'typeOfStep' => $this->typeOfStep,
            "typeOfMotor" => $this->typeOfMotor,
            "typeOfVolt" => $this->typeOfVolt,
            'created_at' => $this->created_at,
            'customerID' => $this->customerID,
        ];
    }
}