<?php

declare(strict_types=1);

class PortfolioMediaService
{
    private PDO $db;
    private PortfolioRepository $portfolioRepo;
    private PortfolioMediaRepository $mediaRepo;
    private UploadService $uploadService;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->portfolioRepo = new PortfolioRepository($db);
        $this->mediaRepo = new PortfolioMediaRepository($db);
        $this->uploadService = new UploadService();
    }

    public function upload(int $portfolioId, array $file, string $type, ?string $altText = null): array
    {
        $portfolio = $this->portfolioRepo->findById($portfolioId);
        if (!$portfolio) {
            not_found_response('Portfolio not found');
        }

        $validTypes = ['cover', 'gallery', 'og'];
        if (!in_array($type, $validTypes, true)) {
            validation_error_response(['type' => 'Media type must be cover, gallery, or og.']);
        }

        $stored = $this->uploadService->store($file, 'portfolio');

        $this->db->beginTransaction();
        try {
            $sortOrder = $type === 'gallery'
                ? $this->mediaRepo->nextSortOrder($portfolioId)
                : 0;

            $mediaId = $this->mediaRepo->insert([
                'portfolio_id' => $portfolioId,
                'type' => $type,
                'filename' => $stored['filename'],
                'original_filename' => $stored['original_filename'],
                'mime_type' => $stored['mime_type'],
                'extension' => $stored['extension'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'size_bytes' => $stored['size_bytes'],
                'alt_text' => $altText,
                'sort_order' => $sortOrder,
            ]);

            if ($type === 'cover') {
                $this->portfolioRepo->setCoverImage($portfolioId, $mediaId);
            } elseif ($type === 'og') {
                $this->portfolioRepo->setOgImage($portfolioId, $mediaId);
            }

            $row = $this->mediaRepo->findById($mediaId);
            $this->db->commit();
            return formatMediaRow($row);
        } catch (Throwable $e) {
            $this->db->rollBack();
            $this->uploadService->delete($stored['filename']);
            throw $e;
        }
    }

    public function delete(int $mediaId): void
    {
        $media = $this->mediaRepo->findById($mediaId);
        if (!$media) {
            not_found_response('Media not found');
        }

        $this->db->beginTransaction();
        try {
            $portfolioId = (int) $media['portfolio_id'];

            if ($media['type'] === 'cover') {
                $this->portfolioRepo->setCoverImage($portfolioId, null);
            } elseif ($media['type'] === 'og') {
                $this->portfolioRepo->setOgImage($portfolioId, null);
            }

            $this->mediaRepo->delete($mediaId);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        $this->uploadService->delete($media['filename']);
    }

    public function reorder(array $orderMap): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($orderMap as $mediaId => $sortOrder) {
                $this->mediaRepo->updateSortOrder((int) $mediaId, (int) $sortOrder);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findByPortfolioId(int $portfolioId): array
    {
        $portfolio = $this->portfolioRepo->findById($portfolioId);
        if (!$portfolio) {
            not_found_response('Portfolio not found');
        }
        $rows = $this->mediaRepo->findByPortfolioId($portfolioId);
        return array_map('formatMediaRow', $rows);
    }
}