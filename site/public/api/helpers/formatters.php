<?php

declare(strict_types=1);

/**
 * Shared formatters for shaping API response payloads.
 * Converts raw DB rows into camelCase JSON the frontend expects.
 */

function formatMediaRow(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'highlight_id' => (int) $row['highlight_id'],
        'type' => $row['type'],
        'filename' => $row['filename'],
        'original_filename' => $row['original_filename'],
        'mime_type' => $row['mime_type'],
        'extension' => $row['extension'],
        'width' => $row['width'] !== null ? (int) $row['width'] : null,
        'height' => $row['height'] !== null ? (int) $row['height'] : null,
        'size_bytes' => (int) $row['size_bytes'],
        'alt_text' => $row['alt_text'] ?? null,
        'sort_order' => (int) $row['sort_order'],
        'created_at' => $row['created_at'] ?? null,
    ];
}

function formatHighlightRow(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'title' => $row['title'],
        'slug' => $row['slug'],
        'category' => $row['category'],
        'short_description' => $row['short_description'],
        'content' => $row['content'] ?? '',
        'cover_media_id' => $row['cover_media_id'] !== null ? (int) $row['cover_media_id'] : null,
        'event_date' => $row['event_date'] ?? null,
        'location' => $row['location'] ?? null,
        'youtube_url' => $row['youtube_url'] ?? null,
        'featured' => (int) $row['featured'] === 1,
        'published' => (int) $row['published'] === 1,
        'seo_title' => $row['seo_title'] ?? null,
        'seo_description' => $row['seo_description'] ?? null,
        'og_media_id' => $row['og_media_id'] !== null ? (int) $row['og_media_id'] : null,
        'created_at' => $row['created_at'] ?? null,
        'updated_at' => $row['updated_at'] ?? null,
    ];
}

function formatOrganizationRow(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'position_en' => $row['position_en'],
        'position_id' => $row['position_id'],
        'photo' => $row['photo'] ?? null,
        'biography_en' => $row['biography_en'] ?? null,
        'biography_id' => $row['biography_id'] ?? null,
        'display_order' => (int) $row['display_order'],
        'featured_slot' => $row['featured_slot'] !== null ? (int) $row['featured_slot'] : null,
        'created_at' => $row['created_at'] ?? null,
        'updated_at' => $row['updated_at'] ?? null,
    ];
}

function buildPaginationMeta(int $currentPage, int $perPage, int $totalItems): array
{
    $totalPages = $perPage > 0 ? (int) ceil($totalItems / $perPage) : 1;
    $currentPage = max(1, min($currentPage, $totalPages));

    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'total_items' => $totalItems,
        'per_page' => $perPage,
        'has_next' => $currentPage < $totalPages,
        'has_prev' => $currentPage > 1,
    ];
}