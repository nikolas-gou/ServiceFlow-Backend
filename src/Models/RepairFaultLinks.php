<?php

namespace App\Models;

class RepairFaultLinks
{
    public $repair_id;
    public $common_fault_id;


    public function __construct(array $data = [])
    {
        $this->repair_id = $data['repair_id'] ?? '';
        $this->common_fault_id = $data['common_fault_id'] ?? '';
    }

    public static function fromFrontendFormat(array $frontendData): self
    {
        $dbData = [
            'repair_id' => $frontendData['repairID'] ?? null,
            'common_fault_id' => $frontendData['commonFaultID'] ?? '',
        ];

        return new self($dbData);
    }

    public function toFrontendFormat(): array
    {
        return [
            'repairID' => $this->repair_id,
            'commonFaultID' => $this->common_fault_id,
        ];
    }

    public function toArray(): array
    {
        return [
            'repair_id' => $this->repair_id,
            'common_fault_id' => $this->common_fault_id,
        ];
    }
}
