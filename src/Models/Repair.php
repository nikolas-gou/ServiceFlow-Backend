<?php
namespace App\Models;

class Repair {
    public $id;
    public $motorID;
    public $customerID;
    public $repair_status;
    public $description;
    public $cost;
    public $created_at;
    public $isArrived;
    public $estimatedIsComplete;
    public $customer;
    public $motor;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->motorID = $data['motorID'] ?? null;
        $this->customerID = $data['customerID'] ?? null;
        $this->repair_status = $data['repair_status'] ?? '';
        $this->created_at = $data['created_at'] ?? "";
        $this->isArrived = $data['isArrived'] ?? "";
        $this->description = $data['description'] ?? '';
        $this->cost = $data['cost'] ?? '';
        $this->estimatedIsComplete = $data['estimatedIsComplete'] ?? '';
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'motorID' => $this->motorID,
            'customerID' => $this->customerID,
            'repair_status' => $this->repair_status,
            'created_at' => $this->created_at,
            'isArrived' => $this->isArrived,
            'estimatedIsComplete' => $this->estimatedIsComplete,
            'description' => $this->description,
            'cost' => $this->cost,
            'customer' => $this->customer ? $this->customer->toArray() : null,
            'motor' => $this->motor ? $this->motor->toArray() : null
        ];
    }
}