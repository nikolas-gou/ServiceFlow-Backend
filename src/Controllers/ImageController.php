<?php

namespace App\Controllers;

use App\Repositories\ImageRepository;
use App\Helpers\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ImageController
{
    private $imageRepository;

    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    // Upload πολλαπλών εικόνων για μια επισκευή
    public function uploadImages(Request $request, Response $response, array $args): Response
    {
        try {
            $repairId = (int)$args['repairId'];

            // Έλεγχος για files[] (με brackets) ή files (χωρίς brackets)
            // Σημείωση: όταν στέλνεις files[] από FormData, το PHP το αποθηκεύει ως 'files'
            $filesArray = null;
            if (isset($_FILES['files'])) {
                $filesArray = $_FILES['files'];
            } else {
                // Δοκίμασε όλα τα keys του $_FILES
                foreach ($_FILES as $key => $value) {
                    if (strpos($key, 'file') !== false) {
                        $filesArray = $value;
                        break;
                    }
                }
            }

            if (!$filesArray) {
                return ResponseHelper::badRequest($response, 'Δεν βρέθηκαν αρχεία για upload');
            }

            // Προετοιμασία των files
            $files = [];
            if (is_array($filesArray['tmp_name'])) {
                // Πολλαπλά αρχεία (files[] ή files)
                foreach ($filesArray['tmp_name'] as $key => $tmp_name) {
                    $errorCode = is_array($filesArray['error']) ? $filesArray['error'][$key] : $filesArray['error'];
                    
                    // Έλεγχος για upload errors
                    if ($errorCode !== UPLOAD_ERR_OK) {
                        // Skip this file - error will be handled by validation
                        continue;
                    }
                    
                    $files[] = [
                        'name' => is_array($filesArray['name']) ? $filesArray['name'][$key] : $filesArray['name'],
                        'type' => is_array($filesArray['type']) ? $filesArray['type'][$key] : $filesArray['type'],
                        'tmp_name' => $tmp_name,
                        'error' => $errorCode,
                        'size' => is_array($filesArray['size']) ? $filesArray['size'][$key] : $filesArray['size']
                    ];
                }
            } else if ($filesArray['error'] === UPLOAD_ERR_OK) {
                // Μονό αρχείο
                $files[] = $filesArray;
            }

            // Στην περιπτωση που υπαρχουν λαθη κατα το ανεβασμα
            if (empty($files)) {
                return ResponseHelper::validationError($response, ['Δεν βρέθηκαν έγκυρα αρχεία για upload. Ελέγξτε το μέγεθος των αρχείων και τον τύπο τους.']);
            }

            // Upload των εικόνων
            $uploadedImages = $this->imageRepository->uploadImages($files, $repairId);

            return ResponseHelper::success($response, $uploadedImages, 'Εικόνες ανεβασμένες επιτυχώς', 201);
        } catch (\Exception $e) {
            error_log("Image upload error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return ResponseHelper::serverError($response, 'Σφάλμα κατά το ανέβασμα των φωτογραφιών: ' . $e->getMessage());
        }
    }

    /**
     * Λήψη όλων των εικόνων μιας επισκευής
     */
    public function getImagesForRepair(Request $request, Response $response, array $args): Response
    {
        try {
            $repairId = (int)$args['repairId'];
            $images = $this->imageRepository->getByRepairId($repairId);

            // Μετατροπή των εικόνων σε frontend format
            $responseData = array_map(function($image) {
                return $image->toFrontendFormat();
            }, $images);

            return ResponseHelper::success($response, $responseData, 'Εικόνες λήφθηκαν επιτυχώς');

        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Σφάλμα κατά τη λήψη των εικόνων' . $e->getMessage());
        }
    }

    /**
     * Σερβίρισμα μιας εικόνας
     */
    public function serveImage(Request $request, Response $response, array $args): Response
    {
        try {
            $imageId = (int)$args['id'];
            $image = $this->imageRepository->getById($imageId);

            if (!$image) {
                return ResponseHelper::notFound($response, 'Η εικόνα δεν βρέθηκε');
            }

            // Κατασκευή του πλήρους path
            $basePath = __DIR__ . '/../../public/';
            $fullPath = $basePath . $image->path;

            if (!file_exists($fullPath)) {
                return ResponseHelper::notFound($response, 'Το αρχείο της εικόνας δεν βρέθηκε');
            }

            // Προσδιορισμός MIME type
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($fullPath);

            // Διάβασμα και επιστροφή του αρχείου
            $fileContent = file_get_contents($fullPath);
            
            return ResponseHelper::binary($response, $fileContent, $mimeType, 200, [
                'Content-Length' => filesize($fullPath),
                'Cache-Control' => 'public, max-age=31536000'
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Σφάλμα κατά τη λήψη της εικόνας' . $e->getMessage());
        }
    }

    public function deleteImages(Request $request, Response $response): Response
    {
        try {
            $filesToDelete = json_decode($request->getBody()->getContents(), true);
            
            foreach ($filesToDelete as $fileToDelete) {
                $this->imageRepository->deleteImage($fileToDelete);
            }

            return ResponseHelper::success($response, null, 'Εικόνες διαγράφηκαν επιτυχώς');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Σφάλμα κατά τη διαγραφή των εικόνων' . $e->getMessage());
        }
    }
}