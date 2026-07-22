<?php

declare(strict_types=1);

class DashboardService
{
    private PDO $db;
    private HighlightRepository $highlightRepo;
    private OrganizationRepository $orgRepo;
    private HighlightMediaRepository $mediaRepo;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->highlightRepo = new HighlightRepository($db);
        $this->orgRepo = new OrganizationRepository($db);
        $this->mediaRepo = new HighlightMediaRepository($db);
    }

    public function getData(): array
    {
        $stats = [
            'total_highlights' => $this->highlightRepo->count([]),
            'achievements' => $this->highlightRepo->countByCategory('achievement'),
            'activities' => $this->highlightRepo->countByCategory('activity'),
            'organization_members' => $this->orgRepo->count(),
        ];

        $recentUploads = array_map(function ($row) {
            return [
                'id' => (int) $row['id'],
                'filename' => $row['filename'],
                'original_filename' => $row['original_filename'],
                'created_at' => $row['created_at'],
                'highlight_id' => $row['highlight_id'] !== null ? (int) $row['highlight_id'] : null,
            ];
        }, $this->mediaRepo->findRecent(10));

        return [
            'stats' => $stats,
            'recent_uploads' => $recentUploads,
        ];
    }
}