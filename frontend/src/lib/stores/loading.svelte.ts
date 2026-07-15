class LoadingStore {
  private activeRequests = $state(new Set<string>());
  private globalCounter = $state(0);

  get isLoading(): boolean {
    return this.globalCounter > 0;
  }

  start(key?: string): void {
    if (key) {
      this.activeRequests = new Set([...this.activeRequests, key]);
    }
    this.globalCounter++;
  }

  done(key?: string): void {
    if (key) {
      const next = new Set(this.activeRequests);
      next.delete(key);
      this.activeRequests = next;
    }
    if (this.globalCounter > 0) {
      this.globalCounter--;
    }
  }

  isLoadingKey(key: string): boolean {
    return this.activeRequests.has(key);
  }

  reset(): void {
    this.activeRequests = new Set();
    this.globalCounter = 0;
  }
}

export const loading = new LoadingStore();