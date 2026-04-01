export interface Appointment {
  appointment_uuid: string;
  customer_name: string;
  customer_lastname: string;
  phone_1: string;
  vehicle_brandname: string;
  vehicle_modelname: string;
  vehicle_year: string;
  vehicle_mileage: string;
  appointment_type: string;
  appointment_scheduled_date: string;
  dealership_name: string;
  valuator_name: string | null;
  valuator_last_name: string | null;
}

export interface AppointmentsResponse {
  status: number;
  message: string;
  data: {
    appointments: {
      data: Appointment[];
      current_page: number;
      last_page: number;
      total: number;
    };
  };
}
