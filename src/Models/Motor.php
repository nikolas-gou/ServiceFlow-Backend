<?php
namespace App\Models;

class Motor {
    public  $id;
    public $serial_number;
    public $manufacturer;
    public $kw;
    public $hp;
    public $rpm;
    // main step
    public $step;
    public $halfStep;
    //  1-phase
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
        $this->serial_number = $data['serial_number'] ?? "";
        $this->manufacturer = $data['manufacturer'] ?? "";
        $this->kw = $data['kw'] ?? "";
        $this->hp = $data['hp'] ?? "";
        $this->rpm = $data['rpm'] ?? "";
        $this->step = $data['step'] ?? "";
        $this->halfStep = $data['halfStep'] ?? "";
        $this->helperStep = $data['helperStep'] ?? "";
        $this->helperHalfStep = $data['helperHalfStep'] ?? "";
        $this->spiral = $data['spiral'] ?? "";
        $this->halfSpiral = $data['halfSpiral'] ?? "";
        $this->helperSpiral = $data['helperSpiral'] ?? "";
        $this->helperHalfSpiral = $data['helperHalfSpiral'] ?? "";
        $this->cross_section = $data['cross_section'] ?? "";
        $this->halfCross_section = $data['halfCross_section'] ?? "";
        $this->helperCross_section = $data['helperCross_section'] ?? "";
        $this->helperHalfCross_section = $data['helperHalfCross_section'] ?? "";
        $this->connectionism = $data['connectionism'] ?? "";
        $this->volt = $data['volt'] ?? "";
        $this->poles = $data['poles'] ?? "";
        $this->typeOfStep = $data['typeOfStep'] ?? "";
        $this->typeOfMotor = $data['typeOfMotor'] ?? "";
        $this->typeOfVolt = $data['typeOfVolt'] ?? "";
        $this->created_at = $data['created_at'] ?? "";
        $this->customerID = $data['customerID'] ?? "";
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