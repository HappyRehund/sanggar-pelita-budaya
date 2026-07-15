<?php

declare(strict_types=1);

class DashboardService
{
    private PDO $db;
    private PortfolioRepository $portfolioRepo;
    private OrganizationRepository $orgRepo;
    private PortfolioMediaRepository $mediaRepo;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->portfolioRepo = new PortfolioRepository($db);
        $this->orgRepo = new OrganizationRepository($db);
        $this->mediaRepo = new PortfolioMediaRepository($db);
    }

    public function getData(): array
    {
        $stats = [
            'total_portfolio' => $this->portfolioRepo->count([]),
            'achievements' => $this->portfolioRepo->countByCategory('achievement'),
            'activities' => $this->portfolioRepo->countByCategory('activity'),
            'organization_members' => $this->orgRepo->count(),
            'published_portfolio' => $this->portfolioRepo->countPublished(),
            'draft_portfolio' => $this->portfolioRepo->countDrafts(),
        ];

        $recentUploads = array_map(function ($row) {
            return [
                'id' => (int) $row['id'],
                'filename' => $row['filename'],
                'original_filename' => $row['original_filename'],
                'created_at' => $row['created_at'],
                'portfolio_id' => $row['portfolio_id'] !== null ? (int) $row['portfolio_id'] : null,
            ];
        }, $this->mediaRepo->findRecent(10));

        return [
            'stats' => $stats,
            'recent_uploads' => $recentUploads,
        ];
    }
}