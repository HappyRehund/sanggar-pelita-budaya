<script lang="ts">
  import { onMount } from 'svelte';
  import { dashboardApi } from '$lib/api';
  import { t } from '$lib/i18n/index.svelte';
  import { router } from '$lib/router.svelte';
  import { notifications } from '$lib/stores/notification.svelte';
  import { uploadUrl } from '$lib/utils';
  import type { DashboardData } from '$lib/types';
  import { FolderOpen, Users, Award, Sparkles, CheckCircle, FileEdit, Plus, LayoutDashboard, PanelBottom } from '@lucide/svelte';

  let data = $state<DashboardData | null>(null);
  let loading = $state(true);

  onMount(async () => {
    try {
      data = await dashboardApi.getData();
    } catch {
      notifications.error(t('toast_error'));
    } finally {
      loading = false;
    }
  });

  const statCards = $derived(data ? [
    { icon: FolderOpen, label: t('admin_stat_total_portfolio'), value: data.stats.total_portfolio, color: 'accent' },
    { icon: Award, label: t('admin_stat_achievements'), value: data.stats.achievements, color: 'gold' },
    { icon: Sparkles, label: t('admin_stat_activities'), value: data.stats.activities, color: 'red' },
    { icon: Users, label: t('admin_stat_members'), value: data.stats.organization_members, color: 'blue' },
    { icon: CheckCircle, label: t('admin_stat_published'), value: data.stats.published_portfolio, color: 'green' },
    { icon: FileEdit, label: t('admin_stat_drafts'), value: data.stats.draft_portfolio, color: 'gray' },
  ] : []);

  const quickActions = [
    { icon: Plus, label: t('admin_action_create_portfolio'), path: '/admin/portfolio?new=true' },
    { icon: Users, label: t('admin_action_add_member'), path: '/admin/organization?new=true' },
    { icon: LayoutDashboard, label: t('admin_action_update_hero'), path: '/admin/hero' },
    { icon: PanelBottom, label: t('admin_action_edit_footer'), path: '/admin/footer' },
  ];
</script>

<div class="dashboard">
  <h1 class="dashboard__title">{t('admin_dashboard_title')}</h1>

  {#if loading}
    <div class="dashboard__stats">
      {#each Array(6) as _, i (i)}
        <div class="stat-card stat-card--skeleton"></div>
      {/each}
    </div>
  {:else if data}
    <div class="dashboard__stats">
      {#each statCards as card (card.label)}
        <div class="stat-card stat-card--{card.color}">
          <div class="stat-card__icon"><card.icon size={22} strokeWidth={1.5} /></div>
          <div class="stat-card__body">
            <span class="stat-card__value">{card.value}</span>
            <span class="stat-card__label">{card.label}</span>
          </div>
        </div>
      {/each}
    </div>

    {#if data.recent_uploads.length > 0}
      <div class="dashboard__section">
        <h2 class="dashboard__section-title">{t('admin_recent_uploads')}</h2>
        <div class="upload-list">
          {#each data.recent_uploads.slice(0, 5) as upload (upload.id)}
            <div class="upload-item">
              <img src={uploadUrl(upload.filename)} alt={upload.original_filename} class="upload-item__thumb" loading="lazy" />
              <div class="upload-item__info">
                <span class="upload-item__name">{upload.original_filename}</span>
                <span class="upload-item__size">{upload.filename}</span>
              </div>
            </div>
          {/each}
        </div>
      </div>
    {/if}

    <div class="dashboard__section">
      <h2 class="dashboard__section-title">{t('admin_quick_actions')}</h2>
      <div class="quick-actions">
        {#each quickActions as action (action.path)}
          <button class="quick-action" onclick={() => router.go(action.path)}>
            <div class="quick-action__icon"><action.icon size={20} /></div>
            <span class="quick-action__label">{action.label}</span>
          </button>
        {/each}
      </div>
    </div>
  {/if}
</div>

<style>
  .dashboard__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h2);
    font-weight: var(--fw-semibold);
    margin-bottom: var(--sp-6);
  }

  .dashboard__stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--sp-4);
    margin-bottom: var(--sp-8);
  }

  .stat-card {
    display: flex;
    align-items: center;
    gap: var(--sp-4);
    padding: var(--sp-5);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
  }

  .stat-card--skeleton {
    height: 5rem;
    background: var(--color-gray-100);
    animation: shimmer 1.6s var(--ease-smooth) infinite;
    border: 1px solid var(--color-border);
  }

  @keyframes shimmer {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
  }

  .stat-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-md);
    flex-shrink: 0;
  }

  .stat-card--accent .stat-card__icon { background: rgba(158, 42, 43, 0.08); color: var(--color-accent); }
  .stat-card--gold .stat-card__icon { background: rgba(201, 162, 39, 0.12); color: var(--color-gold-dark); }
  .stat-card--red .stat-card__icon { background: rgba(196, 69, 69, 0.08); color: var(--color-red-soft); }
  .stat-card--blue .stat-card__icon { background: rgba(74, 107, 138, 0.08); color: var(--color-info); }
  .stat-card--green .stat-card__icon { background: rgba(74, 124, 78, 0.08); color: var(--color-success); }
  .stat-card--gray .stat-card__icon { background: var(--color-surface-alt); color: var(--color-gray-500); }

  .stat-card__value {
    font-family: var(--font-serif);
    font-size: var(--fs-h2);
    font-weight: var(--fw-semibold);
    display: block;
    line-height: 1;
  }

  .stat-card__label {
    font-size: var(--fs-caption);
    color: var(--color-text-muted);
  }

  .dashboard__section {
    margin-bottom: var(--sp-8);
  }

  .dashboard__section-title {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
    margin-bottom: var(--sp-4);
  }

  .upload-list {
    display: flex;
    flex-direction: column;
    gap: var(--sp-2);
  }

  .upload-item {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    padding: var(--sp-3);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
  }

  .upload-item__thumb {
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-sm);
    object-fit: cover;
    flex-shrink: 0;
  }

  .upload-item__name {
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    display: block;
  }

  .upload-item__size {
    font-size: var(--fs-caption);
    color: var(--color-text-subtle);
  }

  .quick-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--sp-4);
  }

  .quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-3);
    padding: var(--sp-5);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    transition: box-shadow var(--duration-fast) var(--ease-smooth), transform var(--duration-fast) var(--ease-out);
  }

  .quick-action:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
  }

  .quick-action__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-full);
    background: rgba(158, 42, 43, 0.08);
    color: var(--color-accent);
  }

  .quick-action__label {
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    text-align: center;
  }

  @media (max-width: 768px) {
    .dashboard__stats { grid-template-columns: repeat(2, 1fr); }
    .quick-actions { grid-template-columns: repeat(2, 1fr); }
  }
</style>