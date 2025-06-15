<?php

namespace App\Models;

class Repair
{
    public $id;
    public $motor_id;
    public $customer_id;
    public $repair_status;
    public $description;
    public $cost;
    public $created_at;
    public $is_arrived;
    public $estimated_is_complete;
    public $customer;
    public $motor;
    public $repair_fault_links = [];

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->motor_id = $data['motor_id'] ?? null;
        $this->customer_id = $data['customer_id'] ?? null;
        $this->repair_status = $data['repair_status'] ?? '';
        $this->created_at = $data['created_at'] ?? "";
        $this->is_arrived = $data['is_arrived'] ?? "";
        $this->description = $data['description'] ?? '';
        $this->cost = $data['cost'] ?? '';
        $this->estimated_is_complete = $data['estimated_is_complete'] ?? '';
        $this->repair_fault_links = $data['repair_fault_links'] ?? [];
        $this->customer = $data['customer'] ?? null;
        $this->motor = $data['motor'] ?? null;
    }



    public static function fromFrontendFormat(array $frontendData): self
    {
        $dbData = [
            'id' => $frontendData['id'] ?? null,
            'motor_id' => $frontendData['motorID'] ?? null,
            'customer_id' => $frontendData['customerID'] ?? null,
            'repair_status' => $frontendData['repairStatus'] ?? '',
            'created_at' => $frontendData['createdAt'] ?? '',
            'is_arrived' => $frontendData['isArrived'] ?? '',
            'description' => $frontendData['description'] ?? '',
            'cost' => $frontendData['cost'] ?? '',
            'estimated_is_complete' => $frontendData['estimatedIsComplete'] ?? '',
            'repair_fault_links' => array_map(
                fn($item) => Repair_Fault_Links::fromFrontendFormat($item),
                $frontendData['repairFaultLinks'] ?? []
            ),
            'customer' => $frontendData['customer'] ? Customer::fromFrontendFormat($frontendData['customer']) : null,
            'motor' => $frontendData['motor'] ? Motor::fromFrontendFormat($frontendData['motor']) : null
        ];

        return new self($dbData);
    }

    public function toFrontendFormat(): array
    {
        return [
            'id' => $this->id,
            'motorID' => $this->motor_id,
            'customerID' => $this->customer_id,
            'repairStatus' => $this->repair_status,
            'createdAt' => $this->created_at,
            'isArrived' => $this->is_arrived,
            'estimatedIsComplete' => $this->estimated_is_complete,
            'description' => $this->description,
            'cost' => $this->cost,
            'customer' => $this->customer ? $this->customer->toFrontendFormat() : null,
            'motor' => $this->motor ? $this->motor->toFrontendFormat() : null,
            'repairFaultLinks' => array_map(function ($link) {
                return $link->toFrontendFormat();
            }, $this->repair_fault_links)
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'motor_id' => $this->motor_id,
            'customer_id' => $this->customer_id,
            'repair_status' => $this->repair_status,
            'created_at' => $this->created_at,
            'is_arrived' => $this->is_arrived,
            'estimated_is_complete' => $this->estimated_is_complete,
            'description' => $this->description,
            'cost' => $this->cost,
            'customer' => $this->customer ? $this->customer->toArray() : null,
            'motor' => $this->motor ? $this->motor->toArray() : null,
            'repair_fault_links' => array_map(function ($link) {
                return $link->toArray();
            }, $this->repair_fault_links)
        ];
    }
}
