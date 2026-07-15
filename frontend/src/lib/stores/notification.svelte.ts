export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface Toast {
  id: number;
  type: ToastType;
  message: string;
  duration: number;
  icon?: boolean;
}

class NotificationStore {
  toasts = $state<Toast[]>([]);
  private counter = 0;

  show(message: string, type: ToastType = 'info', duration = 4000): void {
    const id = ++this.counter;
    this.toasts = [...this.toasts, { id, type, message, duration, icon: true }];

    if (duration > 0) {
      setTimeout(() => this.dismiss(id), duration);
    }
  }

  success(message: string, duration = 4000): void {
    this.show(message, 'success', duration);
  }

  error(message: string, duration = 6000): void {
    this.show(message, 'error', duration);
  }

  warning(message: string, duration = 5000): void {
    this.show(message, 'warning', duration);
  }

  info(message: string, duration = 4000): void {
    this.show(message, 'info', duration);
  }

  dismiss(id: number): void {
    this.toasts = this.toasts.filter((t) => t.id !== id);
  }

  clear(): void {
    this.toasts = [];
  }
}

export const notifications = new NotificationStore();