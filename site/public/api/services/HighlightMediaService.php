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

        if ($type !== 'cover') {
            validation_error_response(['type' => 'Media type must be cover.']);
        }

        $stored = $this->uploadService->store($file, 'highlights');

        $this->db->beginTransaction();
        try {
            $mediaId = $this->mediaRepo->insert([
                'highlight_id' => $highlightId,
                'type' => 'cover',
                'filename' => $stored['filename'],
                'original_filename' => $stored['original_filename'],
                'mime_type' => $stored['mime_type'],
                'extension' => $stored['extension'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'size_bytes' => $stored['size_bytes'],
                'alt_text' => $altText,
                'sort_order' => 0,
            ]);

            $this->highlightRepo->setCoverMedia($highlightId, $mediaId);

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
            }

            $this->mediaRepo->delete($mediaId);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        $this->uploadService->delete($media['filename']);
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