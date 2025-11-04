<?php

namespace App\Repositories;

use App\Models\Image;
use PDO;

class ImageRepository
{
    private $conn;
    private $uploadPath;
    private $allowedTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/heic',
        'image/heif'
    ];
    private $maxFileSize = 10485760; // 10MB σε bytes

    public function __construct(PDO $pdo)
    {
        $this->conn = $pdo;
        // Ορισμός του upload path relative στο public directory
        $this->uploadPath = __DIR__ . '/../../public/uploads/repairs/';
    }

    /**
     * Ανέβασμα πολλαπλών εικόνων για μια επισκευή
     */
    public function uploadImages(array $files, int $repair_id): array
    {
        $uploadedImages = [];
        $errors = [];
        
        // Δημιουργία του directory για την επισκευή αν δεν υπάρχει
        $repairDir = $this->uploadPath . $repair_id;
        if (!file_exists($repairDir)) {
            if (!mkdir($repairDir, 0777, true)) {
                throw new \RuntimeException("Αδυναμία δημιουργίας φακέλου για τις εικόνες");
            }
        }

        foreach ($files as $file) {
            try {
                // Έλεγχοι αρχείου
                if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                    throw new \RuntimeException("Μη έγκυρο αρχείο");
                }

                // Έλεγχος τύπου αρχείου
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);
                
                // Fallback: Έλεγχος από το όνομα αρχείου αν το MIME type δεν είναι αξιόπιστο
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $extensionMap = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    'heic' => 'image/heic',
                    'heif' => 'image/heif'
                ];
                
                // Αν το MIME type δεν είναι στο allowed list, δοκίμασε από extension
                if (!in_array($mimeType, $this->allowedTypes)) {
                    if (isset($extensionMap[$extension])) {
                        $mimeType = $extensionMap[$extension];
                    } else {
                        throw new \RuntimeException("Μη αποδεκτός τύπος αρχείου: {$mimeType} (extension: {$extension})");
                    }
                }
                
                // Διπλός έλεγχος μετά το fallback
                if (!in_array($mimeType, $this->allowedTypes)) {
                    throw new \RuntimeException("Μη αποδεκτός τύπος αρχείου: {$mimeType}");
                }

                // Έλεγχος μεγέθους
                if ($file['size'] > $this->maxFileSize) {
                    throw new \RuntimeException("Το αρχείο υπερβαίνει το μέγιστο επιτρεπτό μέγεθος (10MB)");
                }

                // Δημιουργία μοναδικού ονόματος αρχείου
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
               
                if (!$extension) {
                    $extension = $this->getExtensionFromMimeType($mimeType);
                }
                $filename = uniqid() . '_' . time() . '.' . $extension;
                $filepath = $repairDir . '/' . $filename;

                // Μετακίνηση του αρχείου
                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    throw new \RuntimeException("Αποτυχία μεταφοράς αρχείου");
                }

                // Αποθήκευση στη βάση (relative path για να χρησιμοποιείται από το serveImage)
                $relativePath = 'uploads/repairs/' . $repair_id . '/' . $filename;
                $image = new Image([
                    'repair_id' => $repair_id,
                    'path' => $relativePath,
                    'type' => $mimeType,
                    'size' => $file['size']
                ]);

                $imageId = $this->save($image);
                $image->id = $imageId;
                $uploadedImages[] = $image;

            } catch (\Exception $e) {
                $errors[] = "Σφάλμα στο αρχείο {$file['name']}: " . $e->getMessage();
                // Καθαρισμός τυχόν μερικώς ανεβασμένου αρχείου
                if (isset($filepath) && file_exists($filepath)) {
                    unlink($filepath);
                }
            }
        }

        // Αν υπάρχουν σφάλματα αλλά έχουν ανέβει και κάποια αρχεία επιτυχώς
        if (!empty($errors)) {
            error_log("Σφάλματα κατά το ανέβασμα εικόνων: " . implode(", ", $errors));
            if (empty($uploadedImages)) {
                throw new \RuntimeException("Αποτυχία ανεβάσματος όλων των αρχείων: " . implode(", ", $errors));
            }
        }

        $formattedData = array_map(function($image) {
            return $image->toFrontendFormat();
        }, $uploadedImages);

        return $formattedData;
    }

    /**
     * Αποθήκευση μιας εικόνας στη βάση
     */
    private function save(Image $image): int
    {
        $query = "INSERT INTO images (repair_id, path, type, size, created_at) 
                 VALUES (:repair_id, :path, :type, :size, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':repair_id', $image->repair_id);
        $stmt->bindValue(':path', $image->path);
        $stmt->bindValue(':type', $image->type);
        $stmt->bindValue(':size', $image->size);
        $stmt->execute();

        return (int) $this->conn->lastInsertId();
    }

    /**
     * Λήψη μιας εικόνας με βάση το ID
     */
    public function getById(int $id): ?Image
    {
        $query = "SELECT * FROM images WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return new Image($row);
    }

    /**
     * Λήψη όλων των εικόνων μιας επισκευής
     */
    public function getByRepairId(int $repair_id): array
    {
        $query = "SELECT * FROM images WHERE repair_id = :repair_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':repair_id', $repair_id);
        $stmt->execute();

        $images = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $images[] = new Image($row);
        }
        return $images;
    }

    /**
     * Διαγραφή μιας εικόνας
     */
    public function deleteImage(int $id): bool
    {
        try {
            // Παίρνουμε πρώτα τα στοιχεία της εικόνας
            $query = "SELECT * FROM images WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            
            $imageData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$imageData) {
                throw new \RuntimeException("Η εικόνα δεν βρέθηκε");
            }

            // Διαγραφή του αρχείου
            $basePath = __DIR__ . '/../../public/';
            $fullPath = $basePath . $imageData['path'];
            if (file_exists($fullPath)) {
                if (!unlink($fullPath)) {
                    throw new \RuntimeException("Αποτυχία διαγραφής αρχείου");
                }
            }

            // Διαγραφή από τη βάση
            $query = "DELETE FROM images WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

            return true;
        } catch (\Exception $e) {
            error_log("Error deleting image: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Βοηθητική μέθοδος για εύρεση επέκτασης από MIME type
     */
    private function getExtensionFromMimeType(string $mimeType): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/heic' => 'heic',
            'image/heif' => 'heif'
        ];
        return $map[$mimeType] ?? 'jpg';
    }
}