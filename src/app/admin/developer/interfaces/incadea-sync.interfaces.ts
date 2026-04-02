export interface SyncResult {
  total_fetched: number;
  total_filtered: number;
  created: number;
  updated: number;
  skipped: number;
  errors: number;
  duration_seconds: number;
  log_uuid: string;
}

export interface SyncLog {
  uuid: string;
  status: 'running' | 'completed' | 'failed';
  total_fetched: number;
  total_created: number;
  total_updated: number;
  total_skipped: number;
  total_errors: number;
  filters_applied: any;
  error_details: any[];
  started_at: string;
  finished_at: string;
  created_at: string;
}

export interface SyncConfig {
  excluded_brands: string[];
  excluded_categories: string[];
}
