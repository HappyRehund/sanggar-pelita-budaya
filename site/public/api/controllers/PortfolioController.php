<?php

declare(strict_types=1);

class PortfolioController
{
    private PortfolioService $portfolioService;
    private PortfolioMediaService $mediaService;

    public function __construct()
    {
        $db = getPDO();
        $this->portfolioService = new PortfolioService($db);
        $this->mediaService = new PortfolioMediaService($db);
    }

    /* ---- Portfolio CRUD --------------------------------------------- */

    public function list(array $params): void
    {
        $query = $_GET;
        $filters = [
            'category' => $query['category'] ?? 'all',
            'search' => trim($query['search'] ?? ''),
            'sort' => $query['sort'] ?? 'newest',
        ];

        if (isset($query['published'])) {
            $filters['published'] = filter_var($query['published'], FILTER_VALIDATE_BOOL);
        } elseif (!is_authenticated()) {
            $filters['published'] = true;
        }

        if (isset($query['featured'])) {
            $filters['featured'] = filter_var($query['featured'], FILTER_VALIDATE_BOOL);
        }

        $page = (int) ($query['page'] ?? 1);
        $perPage = (int) ($query['per_page'] ?? PORTFOLIO_PER_PAGE);

        $result = $this->portfolioService->list($filters, $page, $perPage);
        success_response($result, 'Portfolio list');
    }

    public function getById(array $params): void
    {
        $id = (int) $params['id'];
        $portfolio = $this->portfolioService->getById($id);
        success_response($portfolio, 'Portfolio detail');
    }

    public function getBySlug(array $params): void
    {
        $slug = $params['slug'];
        $portfolio = $this->portfolioService->getBySlug($slug);
        success_response($portfolio, 'Portfolio detail');
    }

    public function create(array $params): void
    {
        require_auth();
        require_csrf();

        $input = $this->extractBody();
        $input = $this->normalizePortfolioInput($input);
        $portfolio = $this->portfolioService->create($input);
        success_response($portfolio, 'Portfolio created');
    }

    public function update(array $params): void
    {
        require_auth();
        require_csrf();

        $id = (int) $params['id'];
        $input = $this->extractBody();
        $input = $this->normalizePortfolioInput($input);
        $portfolio = $this->portfolioService->update($id, $input);
        success_response($portfolio, 'Portfolio updated');
    }

    public function delete(array $params): void
    {
        require_auth();
        require_csrf();

        $id = (int) $params['id'];
        $this->portfolioService->delete($id);
        success_response(null, 'Portfolio deleted');
    }

    public function featured(array $params): void
    {
        $limit = (int) ($_GET['limit'] ?? FEATURED_PORTFOLIO_LIMIT);
        $items = $this->portfolioService->listFeatured($limit);
        success_response($items, 'Featured portfolio');
    }

    public function galleryImages(array $params): void
    {
        $items = $this->portfolioService->listGalleryImages();
        success_response($items, 'Gallery images');
    }

    /* ---- Portfolio Media --------------------------------------------- */

    public function uploadMedia(array $params): void
    {
        require_auth();
        require_csrf();

        $portfolioId = (int) $params['id'];
        $file = get_uploaded_file('file');
        if ($file === null) {
            error_response('No file uploaded.', 400);
        }

        $type = $_POST['type'] ?? 'gallery';
        $altText = $_POST['alt_text'] ?? null;

        $media = $this->mediaService->upload($portfolioId, $file, $type, $altText);
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
        $portfolioId = (int) $params['id'];
        $media = $this->mediaService->findByPortfolioId($portfolioId);
        success_response($media, 'Portfolio media');
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

    private function normalizePortfolioInput(array $input): array
    {
        return [
            'title' => trim($input['title'] ?? ''),
            'slug' => trim($input['slug'] ?? ''),
            'category' => $input['category'] ?? '',
            'short_description' => trim($input['short_description'] ?? ''),
            'content' => $input['content'] ?? '',
            'event_date' => $input['event_date'] ?? null,
            'location' => trim($input['location'] ?? ''),
            'youtube_url' => trim($input['youtube_url'] ?? ''),
            'featured' => !empty($input['featured']) ? 1 : 0,
            'published' => !empty($input['published']) ? 1 : 0,
            'seo_title' => trim($input['seo_title'] ?? '') ?: null,
            'seo_description' => trim($input['seo_description'] ?? '') ?: null,
            'cover_image_id' => isset($input['cover_image_id']) ? (int) $input['cover_image_id'] : null,
            'og_image_id' => isset($input['og_image_id']) ? (int) $input['og_image_id'] : null,
        ];
    }
}