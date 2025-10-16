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

            if (!isset($_FILES['files'])) {
                return ResponseHelper::badRequest($response, 'Δεν βρέθηκαν αρχεία για upload');
            }

            // Προετοιμασία των files
            $files = [];
            if (is_array($_FILES['files']['tmp_name'])) {
                // Πολλαπλά αρχεία
                foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
                        $files[] = [
                            'name' => $_FILES['files']['name'][$key],
                            'type' => $_FILES['files']['type'][$key],
                            'tmp_name' => $tmp_name,
                            'error' => $_FILES['files']['error'][$key],
                            'size' => $_FILES['files']['size'][$key]
                        ];
                    }
                }
            } else if ($_FILES['files']['error'] === UPLOAD_ERR_OK) {
                // Μονό αρχείο
                $files[] = $_FILES['files'];
            }

            // Στην περιπτωση που υπαρχουν λαθη κατα το ανεβασμα
            if (empty($files)) {
                return ResponseHelper::validationError($response, ['Δεν βρέθηκαν έγκυρα αρχεία για upload']);
            }

            // Upload των εικόνων
            $uploadedImages = $this->imageRepository->uploadImages($files, $repairId);

            return ResponseHelper::success($response, $uploadedImages, 'Εικόνες ανεβασμένες επιτυχώς', 201);
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Σφάλμα κατά το ανέβασμα των φωτογραφιών' . $e->getMessage());
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
}