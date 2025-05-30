<?php
namespace App\Models;

class Repair {
    public $id;
    public $motorID;
    public $customerID;
    public $repair_status;
    public $created_at;
    public $customer;
    public $motor;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->motorID = $data['motorID'] ?? '';
        $this->customerID = $data['customerID'] ?? '';
        $this->repair_status = $data['repair_status'] ?? '';
        $this->created_at = $data['created_at'] ?? null;
    }

    // public function isValid(): bool {
    //     return !empty(\trim($this->name)) && !empty(\trim($this->phone));
    // }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'motorID' => $this->motorID,
            'customerID' => $this->customerID,
            'repair_status' => $this->repair_status,
            'created_at' => $this->created_at,
            'customer' => $this->customer ? $this->customer->toArray() : null,
            'motor' => $this->motor ? $this->motor->toArray() : null
        ];
    }
}