export interface OrderSearchParams {
  page?: number;
  per_page?: number;
  search?: string;
  status?: string;
  date_from?: string;
  date_to?: string;
}

export interface ShipmentSearchParams {
  page?: number;
  search?: string;
  status?: string;
  carrier?: string;
}

export interface CustomerSearchParams {
  page?: number;
  search?: string;
}

export interface PointsSearchParams {
  page?: number;
  search?: string;
  customer_uuid?: string;
}

export interface PointAdjustment {
  customer_reward_uuid: string;
  points: number;
  reason: string;
  type: 'add' | 'subtract';
}

export interface CouponSearchParams {
  page?: number;
  search?: string;
  discount_type?: string;
}

export interface CouponCreate {
  code: string;
  amount: number;
  discount_type: 'percentage' | 'fixed';
  description?: string;
  usage_limit?: number;
  minimum_amount?: number;
  maximum_amount?: number;
  individual_use?: boolean;
}

export interface RedemptionSearchParams {
  page?: number;
  status?: string;
  customer_uuid?: string;
}

export interface DashboardStat {
  label: string;
  value: string | number;
  icon: string;
  color: string;
  loading: boolean;
}
