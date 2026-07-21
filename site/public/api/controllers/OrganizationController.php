<?php

declare(strict_types=1);

class OrganizationController
{
    private OrganizationService $service;

    public function __construct()
    {
        $this->service = new OrganizationService(getPDO());
    }

    public function list(array $params): void
    {
        $items = $this->service->list();
        success_response($items, 'Organization members');
    }

    public function featured(array $params): void
    {
        $items = $this->service->listFeatured();
        success_response($items, 'Featured organization members');
    }

    public function getById(array $params): void
    {
        $id = (int) $params['id'];
        $member = $this->service->getById($id);
        success_response($member, 'Organization member');
    }

    public function create(array $params): void
    {
        require_auth();
        require_csrf();

        $input = $this->extractBody();
        $member = $this->service->create($this->normalizeInput($input));
        success_response($member, 'Organization member created');
    }

    public function update(array $params): void
    {
        require_auth();
        require_csrf();

        $id = (int) $params['id'];
        $input = $this->extractBody();
        $member = $this->service->update($id, $this->normalizeInput($input));
        success_response($member, 'Organization member updated');
    }

    public function delete(array $params): void
    {
        require_auth();
        require_csrf();

        $id = (int) $params['id'];
        $this->service->delete($id);
        success_response(null, 'Organization member deleted');
    }

    public function reorder(array $params): void
    {
        require_auth();
        require_csrf();

        $input = get_json_input();
        $orderMap = $input['order'] ?? [];
        if (empty($orderMap) || !is_array($orderMap)) {
            validation_error_response(['order' => 'Order map is required.']);
        }
        $this->service->reorder($orderMap);
        success_response(null, 'Members reordered');
    }

    public function uploadPhoto(array $params): void
    {
        require_auth();
        require_csrf();

        $id = (int) $params['id'];
        $file = get_uploaded_file('file');
        if ($file === null) {
            error_response('No file uploaded.', 400);
        }
        $member = $this->service->uploadPhoto($id, $file);
        success_response($member, 'Photo uploaded');
    }

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

    private function normalizeInput(array $input): array
    {
        $featuredSlot = $input['featured_slot'] ?? null;
        return [
            'name' => trim($input['name'] ?? ''),
            'position_en' => trim($input['position_en'] ?? ''),
            'position_id' => trim($input['position_id'] ?? ''),
            'biography_en' => trim($input['biography_en'] ?? '') ?: null,
            'biography_id' => trim($input['biography_id'] ?? '') ?: null,
            'display_order' => (int) ($input['display_order'] ?? 0),
            'featured_slot' => ($featuredSlot !== null && $featuredSlot !== '' && $featuredSlot !== 0)
                ? (int) $featuredSlot
                : null,
        ];
    }
}