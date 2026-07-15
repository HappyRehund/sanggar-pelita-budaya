<script lang="ts">
  import { t } from '$lib/i18n/index.svelte';
</script>

<div class="not-found">
  <h2>{t('not_found_title')}</h2>
  <p>{t('not_found_message')}</p>
  <a href="/">{t('not_found_back')}</a>
</div>