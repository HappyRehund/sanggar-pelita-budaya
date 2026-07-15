<?php

declare(strict_types=1);

class PortfolioService
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

    public function create(array $data): array
    {
        $this->validatePortfolioFields($data, null);

        $this->db->beginTransaction();
        try {
            $id = $this->portfolioRepo->insert($data);
            $row = $this->portfolioRepo->findById($id);
            $this->db->commit();
            return formatPortfolioRow($row);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): array
    {
        $existing = $this->portfolioRepo->findById($id);
        if (!$existing) {
            not_found_response('Portfolio not found');
        }

        $this->validatePortfolioFields($data, $id);

        $this->db->beginTransaction();
        try {
            $this->portfolioRepo->update($id, $data);
            $row = $this->portfolioRepo->findById($id);
            $this->db->commit();
            return formatPortfolioRow($row);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        $existing = $this->portfolioRepo->findById($id);
        if (!$existing) {
            not_found_response('Portfolio not found');
        }

        $this->db->beginTransaction();
        try {
            $media = $this->mediaRepo->findByPortfolioId($id);

            foreach ($media as $file) {
                $this->uploadService->delete($file['filename']);
            }

            $this->portfolioRepo->delete($id);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getById(int $id): array
    {
        $row = $this->portfolioRepo->findById($id);
        if (!$row) {
            not_found_response('Portfolio not found');
        }

        $portfolio = formatPortfolioRow($row);
        $media = $this->mediaRepo->findByPortfolioId($id);
        $portfolio['cover'] = $this->findMediaById($media, $row['cover_image_id']);
        $portfolio['og_image'] = $this->findMediaById($media, $row['og_image_id']);
        $portfolio['gallery'] = $this->filterGallery($media);
        return $portfolio;
    }

    public function getBySlug(string $slug): array
    {
        $row = $this->portfolioRepo->findBySlug($slug);
        if (!$row) {
            not_found_response('Portfolio not found');
        }

        $portfolio = $this->getById((int) $row['id']);

        $related = $this->portfolioRepo->findRelated(
            (int) $row['id'],
            $row['category'],
            RELATED_PORTFOLIO_LIMIT
        );
        $portfolio['related'] = array_map('formatPortfolioRow', $related);

        return $portfolio;
    }

    public function list(array $filters, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min($perPage, 100));

        $total = $this->portfolioRepo->count($filters);
        $rows = $this->portfolioRepo->find($filters, $page, $perPage);

        $items = [];
        foreach ($rows as $row) {
            $item = formatPortfolioRow($row);
            $coverMedia = $row['cover_image_id'] !== null
                ? $this->mediaRepo->findById((int) $row['cover_image_id'])
                : null;
            $item['cover'] = $coverMedia ? formatMediaRow($coverMedia) : null;
            $items[] = $item;
        }

        return [
            'items' => $items,
            'pagination' => buildPaginationMeta($page, $perPage, $total),
        ];
    }

    public function listFeatured(int $limit): array
    {
        $rows = $this->portfolioRepo->findFeatured($limit);
        $items = [];
        foreach ($rows as $row) {
            $item = formatPortfolioRow($row);
            $coverMedia = $row['cover_image_id'] !== null
                ? $this->mediaRepo->findById((int) $row['cover_image_id'])
                : null;
            $item['cover'] = $coverMedia ? formatMediaRow($coverMedia) : null;
            $items[] = $item;
        }
        return $items;
    }

    public function listGalleryImages(): array
    {
        $rows = $this->portfolioRepo->findAllGalleryImages();
        return array_map('formatMediaRow', $rows);
    }

    private function validatePortfolioFields(array $data, ?int $ignoreId): void
    {
        $validator = new ValidationService();
        $validator->required('title', $data['title'] ?? null);
        $validator->required('slug', $data['slug'] ?? null);
        $validator->slug('slug', $data['slug'] ?? '');
        $validator->required('category', $data['category'] ?? null);
        $validator->inEnum('category', $data['category'] ?? '', ['achievement', 'activity']);
        $validator->required('short_description', $data['short_description'] ?? null);
        $validator->optionalUrl('youtube_url', $data['youtube_url'] ?? null);
        $validator->date('event_date', $data['event_date'] ?? null);

        if ($validator->hasErrors()) {
            $validator->failOrContinue();
        }

        if ($this->portfolioRepo->slugExists($data['slug'], $ignoreId)) {
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

    private function filterGallery(array $media): array
    {
        $gallery = array_filter($media, fn($m) => $m['type'] === 'gallery');
        return array_map('formatMediaRow', array_values($gallery));
    }
}