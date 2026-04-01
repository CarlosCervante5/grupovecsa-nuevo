export interface Quotation {
  uuid: string;
  status: string;
  created_at: string;
  appointment: {
    customer: { name: string; last_name: string };
    vehicle: { brand_name: string; model_name: string; year: string; mileage: string };
  } | null;
  vehicle: {
    brand: { name: string } | null;
    line: { name: string } | null;
    model: { name: string } | null;
    year: string;
  } | null;
}

export interface QuotationsResponse {
  status: number;
  message: string;
  data: {
    data: Quotation[];
    current_page: number;
    last_page: number;
    total: number;
  };
}
