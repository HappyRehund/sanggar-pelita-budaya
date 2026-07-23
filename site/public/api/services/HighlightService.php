<?php

declare(strict_types=1);

class HighlightService
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

    public function create(array $data): array
    {
        $this->validateHighlightFields($data, null);

        $this->db->beginTransaction();
        try {
            $id = $this->highlightRepo->insert($data);
            $row = $this->highlightRepo->findById($id);
            $this->db->commit();
            return formatHighlightRow($row);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): array
    {
        $existing = $this->highlightRepo->findById($id);
        if (!$existing) {
            not_found_response('Highlight not found');
        }

        // PATCH: merge provided keys over the existing row so untouched
        // fields (notably cover_media_id) are preserved. PUT: full replace.
        $merged = $this->mergeHighlightData($existing, $data);

        $this->validateHighlightFields($merged, $id);

        $this->db->beginTransaction();
        try {
            $this->highlightRepo->update($id, $merged);
            $row = $this->highlightRepo->findById($id);
            $this->db->commit();
            return formatHighlightRow($row);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Merge a (possibly partial) update payload over the existing DB row.
     * Only keys present in $patch override $existing. Used by PATCH and
     * also safe for PUT (PUT callers pass a full payload, so every key is
     * present and overrides).
     */
    private function mergeHighlightData(array $existing, array $patch): array
    {
        $defaults = [
            'title_en' => $existing['title_en'] ?? '',
            'title_id' => $existing['title_id'] ?? '',
            'slug' => $existing['slug'] ?? '',
            'category' => $existing['category'] ?? '',
            'short_description_en' => $existing['short_description_en'] ?? '',
            'short_description_id' => $existing['short_description_id'] ?? '',
            'cover_media_id' => $existing['cover_media_id'] !== null ? (int) $existing['cover_media_id'] : null,
            'event_date' => $existing['event_date'] ?? null,
            'location' => $existing['location'] ?? '',
            'youtube_url' => $existing['youtube_url'] ?? '',
            'seo_title_en' => $existing['seo_title_en'] ?? null,
            'seo_title_id' => $existing['seo_title_id'] ?? null,
            'seo_description_en' => $existing['seo_description_en'] ?? null,
            'seo_description_id' => $existing['seo_description_id'] ?? null,
        ];

        foreach ($patch as $key => $value) {
            if (array_key_exists($key, $defaults)) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

    public function delete(int $id): void
    {
        $existing = $this->highlightRepo->findById($id);
        if (!$existing) {
            not_found_response('Highlight not found');
        }

        $this->db->beginTransaction();
        try {
            $media = $this->mediaRepo->findByHighlightId($id);

            foreach ($media as $file) {
                $this->uploadService->delete($file['filename']);
            }

            $this->highlightRepo->delete($id);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getById(int $id): array
    {
        $row = $this->highlightRepo->findById($id);
        if (!$row) {
            not_found_response('Highlight not found');
        }

        $highlight = formatHighlightRow($row);
        $media = $this->mediaRepo->findByHighlightId($id);
        $highlight['cover'] = $this->findMediaById($media, $row['cover_media_id']);
        return $highlight;
    }

    public function getBySlug(string $slug): array
    {
        $row = $this->highlightRepo->findBySlug($slug);
        if (!$row) {
            not_found_response('Highlight not found');
        }

        $highlight = $this->getById((int) $row['id']);

        $related = $this->highlightRepo->findRelated(
            (int) $row['id'],
            $row['category'],
            RELATED_HIGHLIGHTS_LIMIT
        );
        $highlight['related'] = array_map('formatHighlightRow', $related);

        return $highlight;
    }

    public function list(array $filters, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min($perPage, 100));

        $total = $this->highlightRepo->count($filters);
        $rows = $this->highlightRepo->find($filters, $page, $perPage);

        $items = [];
        foreach ($rows as $row) {
            $item = formatHighlightRow($row);
            $coverMedia = $row['cover_media_id'] !== null
                ? $this->mediaRepo->findById((int) $row['cover_media_id'])
                : null;
            $item['cover'] = $coverMedia ? formatMediaRow($coverMedia) : null;
            $items[] = $item;
        }

        return [
            'items' => $items,
            'pagination' => buildPaginationMeta($page, $perPage, $total),
        ];
    }

    private function validateHighlightFields(array $data, ?int $ignoreId): void
    {
        $validator = new ValidationService();
        $validator->required('title_en', $data['title_en'] ?? null);
        $validator->required('title_id', $data['title_id'] ?? null);
        $validator->required('slug', $data['slug'] ?? null);
        $validator->slug('slug', $data['slug'] ?? '');
        $validator->required('category', $data['category'] ?? null);
        $validator->inEnum('category', $data['category'] ?? '', ['achievement', 'activity']);
        $validator->required('short_description_en', $data['short_description_en'] ?? null);
        $validator->required('short_description_id', $data['short_description_id'] ?? null);
        $validator->optionalUrl('youtube_url', $data['youtube_url'] ?? null);
        $validator->date('event_date', $data['event_date'] ?? null);

        if ($validator->hasErrors()) {
            $validator->failOrContinue();
        }

        if ($this->highlightRepo->slugExists($data['slug'], $ignoreId)) {
            validation_error_response(['slug' => 'Slug already exists.']);
        }
    }

    private function findMediaById(array $media, ?int $id): ?array
    {
        if ($id === null) {
            return null;
        }
        foreach ($media as $file) {
            if ((int) $file['id'] === $id) {
                return formatMediaRow($file);
            }
        }
        return null;
    }
}