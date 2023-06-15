<?php

namespace App\Controller;

use App\Exception\EncryptionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\DumpManagement;
use App\Service\Encryption;

class DownloadController extends AbstractController
{

    public function __construct(
        private readonly DumpManagement $dumpService,
        private readonly Encryption $encryption
    ) {
    }

    #[Route('/download', name: 'app_download')]
    public function index(Request $request): JsonResponse | BinaryFileResponse
    {
        $encryptedData = $request->getContent();

        try {
            $decryptedData = $this->encryption->decrypt($encryptedData);
        } catch (EncryptionException $exception) {
            return $this->json([
                'message' => 'Access denied'
            ], 503);
        } catch (\Exception $exception) {
            return $this->json([
                'message' => 'Not found'
            ], 404);
        }

        $decryptedData = json_decode($decryptedData, true);
        $file = $this->dumpService->getDumpFileByUuid($decryptedData['dumpuuid']);
        if ($file === null) {
            return $this->json([
                'message' => 'Not found'
            ], 404);
        } else {
            return $this->file($file);
        }
    }
}
