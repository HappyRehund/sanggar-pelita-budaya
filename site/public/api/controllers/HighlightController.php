<?php

declare(strict_types=1);

class HighlightController
{
    private HighlightService $highlightService;
    private HighlightMediaService $mediaService;

    public function __construct()
    {
        $db = getPDO();
        $this->highlightService = new HighlightService($db);
        $this->mediaService = new HighlightMediaService($db);
    }

    /* ---- Highlights CRUD --------------------------------------------- */

    public function list(array $params): void
    {
        $query = $_GET;
        $filters = [
            'category' => $query['category'] ?? 'all',
            'search' => trim($query['search'] ?? ''),
            'sort' => $query['sort'] ?? 'newest',
        ];

        $page = (int) ($query['page'] ?? 1);
        $perPage = (int) ($query['per_page'] ?? HIGHLIGHTS_PER_PAGE);

        $result = $this->highlightService->list($filters, $page, $perPage);
        success_response($result, 'Highlights list');
    }

    public function getById(array $params): void
    {
        $id = (int) $params['id'];
        $highlight = $this->highlightService->getById($id);
        success_response($highlight, 'Highlights detail');
    }

    public function getBySlug(array $params): void
    {
        $slug = $params['slug'];
        $highlight = $this->highlightService->getBySlug($slug);
        success_response($highlight, 'Highlights detail');
    }

    public function create(array $params): void
    {
        require_auth();
        require_csrf();

        $input = $this->extractBody();
        $input = $this->normalizeHighlightInput($input);
        $highlight = $this->highlightService->create($input);
        success_response($highlight, 'Highlights created');
    }

    public function update(array $params): void
    {
        require_auth();
        require_csrf();

        $id = (int) $params['id'];
        $input = $this->extractBody();
        $input = $this->normalizeHighlightInput($input);
        $highlight = $this->highlightService->update($id, $input);
        success_response($highlight, 'Highlights updated');
    }

    public function delete(array $params): void
    {
        require_auth();
        require_csrf();

        $id = (int) $params['id'];
        $this->highlightService->delete($id);
        success_response(null, 'Highlights deleted');
    }

    public function galleryImages(array $params): void
    {
        $items = $this->highlightService->listGalleryImages();
        success_response($items, 'Gallery images');
    }

    /* ---- Highlights Media -------------------------------------------- */

    public function uploadMedia(array $params): void
    {
        require_auth();
        require_csrf();

        $highlightId = (int) $params['id'];
        $file = get_uploaded_file('file');
        if ($file === null) {
            error_response('No file uploaded.', 400);
        }

        $type = $_POST['type'] ?? 'gallery';
        $altText = $_POST['alt_text'] ?? null;

        $media = $this->mediaService->upload($highlightId, $file, $type, $altText);
        success_response($media, 'Upload successful');
    }

    public function deleteMedia(array $params): void
    {
        require_auth();
        require_csrf();

        $mediaId = (int) $params['id'];
        $this->mediaService->delete($mediaId);
        success_response(null, 'Media deleted');
    }

    public function reorderMedia(array $params): void
    {
        require_auth();
        require_csrf();

        $input = get_json_input();
        $orderMap = $input['order'] ?? [];
        if (empty($orderMap) || !is_array($orderMap)) {
            validation_error_response(['order' => 'Order map is required.']);
        }

        $this->mediaService->reorder($orderMap);
        success_response(null, 'Media reordered');
    }

    public function listMedia(array $params): void
    {
        $highlightId = (int) $params['id'];
        $media = $this->mediaService->findByHighlightId($highlightId);
        success_response($media, 'Highlights media');
    }

    /* ---- Helpers ---------------------------------------------------- */

    private function extractBody(): array
    {
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($content_type, 'application/json')) {
            return get_json_input();
        }
        if (str_contains($content_type, 'multipart/form-data')) {
            $parsed = parse_request_body();
            return $parsed['post'];
        }
        return $_POST;
    }

    private function normalizeHighlightInput(array $input): array
    {
        return [
            'title' => trim($input['title'] ?? ''),
            'slug' => trim($input['slug'] ?? ''),
            'category' => $input['category'] ?? '',
            'short_description' => trim($input['short_description'] ?? ''),
            'event_date' => $input['event_date'] ?? null,
            'location' => trim($input['location'] ?? ''),
            'youtube_url' => trim($input['youtube_url'] ?? ''),
            'seo_title' => trim($input['seo_title'] ?? '') ?: null,
            'seo_description' => trim($input['seo_description'] ?? '') ?: null,
            'cover_media_id' => isset($input['cover_media_id']) ? (int) $input['cover_media_id'] : null,
        ];
    }
}