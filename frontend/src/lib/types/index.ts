export type ApiResponse<T> = {
  success: true;
  message: string;
  data: T;
} | {
  success: false;
  message: string;
  errors?: Record<string, string>;
};

export type User = {
  id: number;
  username: string;
  fullname: string;
};

export type Lang = 'id' | 'en';