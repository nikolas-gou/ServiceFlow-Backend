<?php

namespace App\Models;

class Image
{
    public $id;
    public $repair_id;
    public $path;
    public $type;
    public $size;
    public $created_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->repair_id = $data['repair_id'] ?? null;
        $this->path = $data['path'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->size = $data['size'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'repair_id' => $this->repair_id,
            'path' => $this->path,
            'type' => $this->type,
            'size' => $this->size,
            'created_at' => $this->created_at
        ];
    }

    public function toFrontendFormat(): array
    {
        return [
            'id' => $this->id,
            'repairID' => $this->repair_id, // Μετατροπή σε camelCase για το frontend
            'path' => $this->path,
            'type' => $this->type,
            'size' => $this->size,
            'createdAt' => $this->created_at // Μετατροπή σε camelCase για το frontend
        ];
    }

    public static function fromFrontendFormat(array $data): self
    {
        return new self([
            'id' => $data['id'] ?? null,
            'repair_id' => $data['repairID'] ?? null, // Μετατροπή από camelCase
            'path' => $data['path'] ?? null,
            'type' => $data['type'] ?? null,
            'size' => $data['size'] ?? null,
            'created_at' => $data['createdAt'] ?? null // Μετατροπή από camelCase
        ]);
    }
}