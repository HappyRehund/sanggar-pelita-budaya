<?php

declare(strict_types=1);

class HighlightMediaService
{
    private PDO $db;
    private HighlightRepository $highlightRepo;
    private HighlightMediaRepository $mediaRepo;
    private UploadService $uploadService;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->highlightRepo = new HighlightRepository($db);
        $this->mediaRepo = new HighlightMediaRepository($db);
        $this->uploadService = new UploadService();
    }

    public function upload(int $highlightId, array $file, string $type, ?string $altText = null): array
    {
        $highlight = $this->highlightRepo->findById($highlightId);
        if (!$highlight) {
            not_found_response('Highlight not found');
        }

        $validTypes = ['cover', 'gallery', 'og'];
        if (!in_array($type, $validTypes, true)) {
            validation_error_response(['type' => 'Media type must be cover, gallery, or og.']);
        }

        $stored = $this->uploadService->store($file, 'highlights');

        $this->db->beginTransaction();
        try {
            $sortOrder = $type === 'gallery'
                ? $this->mediaRepo->nextSortOrder($highlightId)
                : 0;

            $mediaId = $this->mediaRepo->insert([
                'highlight_id' => $highlightId,
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
                $this->highlightRepo->setCoverMedia($highlightId, $mediaId);
            } elseif ($type === 'og') {
                $this->highlightRepo->setOgMedia($highlightId, $mediaId);
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
            $highlightId = (int) $media['highlight_id'];

            if ($media['type'] === 'cover') {
                $this->highlightRepo->setCoverMedia($highlightId, null);
            } elseif ($media['type'] === 'og') {
                $this->highlightRepo->setOgMedia($highlightId, null);
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

    public function findByHighlightId(int $highlightId): array
    {
        $highlight = $this->highlightRepo->findById($highlightId);
        if (!$highlight) {
            not_found_response('Highlight not found');
        }
        $rows = $this->mediaRepo->findByHighlightId($highlightId);
        return array_map('formatMediaRow', $rows);
    }
}