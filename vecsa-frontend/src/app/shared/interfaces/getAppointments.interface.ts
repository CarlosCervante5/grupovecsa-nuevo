export interface ValuationAppointments {
    status:  number;
    message: string;
    data:    Data;
}

export interface Data {
    current_page:   number;
    data:           VehicleValuations[];
    first_page_url: string;
    from:           number;
    last_page:      number;
    last_page_url:  string;
    links:          Link[];
    next_page_url:  null;
    path:           string;
    per_page:       number;
    prev_page_url:  null;
    to:             number;
    total:          number;
}

export interface VehicleValuations {
    uuid:                         string;
    trade_in_offer:               null;
    sale_price:                   null;
    intellimotors_trade_in_offer: null;
    intellimotors_sale_price:     null;
    labor_cost:                   null;
    spare_parts_cost:             null;
    body_work_painting_cost:      null;
    spm_take_offer:               null;
    final_offer:                  null;
    status:                       string;
    status_parts:                 string;
    status_repairs:               string;
    trade_in:                     null;
    preparation:                  null;
    direct_purchase:              null;
    vehicle_interest:             null;
    vehicle_interest_vin:         null;
    comments:                     null;
    vehicle_id:                   null;
    dealership_id:                number;
    appointment_id:               number;
    created_at:                   Date;
    appointment:                  Appointment;
    repairs:                      Repair[];
}

export interface Appointment {
    uuid:                string;
    type:                string;
    scheduled_date:      string;
    attendance_date:     null;
    departure_date:      null;
    dealership_name:     string;
    status:              string;
    prev_appointment_id: null;
    created_at:          Date;
    customer:            Customer;
    vehicle:             Vehicle;
}

export interface Customer {
    uuid:              string;
    honorific:         null;
    bp_id:             null;
    crm_id:            null;
    rfc:               null;
    tax_regime:        null;
    name:              string;
    last_name:         string;
    age:               null;
    birthday:          null;
    gender:            null;
    phone_1:           null;
    phone_2:           null;
    phone_3:           null;
    cellphone:         null;
    email_1:           string;
    email_2:           null;
    contact_method:    null;
    zip_code:          null;
    address:           null;
    state:             null;
    city:              null;
    district:          null;
    neighborhood:      null;
    marital_status:    null;
    educational_level: null;
    origin_agency:     null;
    picture:           null;
    created_at:        Date;
}

export interface Vehicle {
    uuid:           string;
    name:           null;
    description:    null;
    vin:            null;
    mileage:        number;
    type:           null;
    cylinders:      null;
    interior_color: null;
    exterior_color: null;
    transmission:   null;
    fuel_type:      null;
    drive_train:    null;
    brand_name:     string;
    model_name:     string;
    line_name:      null;
    year:           string;
    version_name:   null;
    body_name:      null;
    created_at:     Date;
}

export interface Repair {
    uuid:        string;
    description: string;
    cost:        null;
    status:      string;
    image_path:  null | string;
    created_at:  Date;
}

export interface Link {
    url:    null | string;
    label:  string;
    active: boolean;
}

export interface AppointmentResponse {
    status:  number;
    message: string;
    data:    DataAM;
}

export interface DataAM {
    appointments: Appointments;
}

export interface Appointments {
    current_page:   number;
    data:           DatDates[];
    first_page_url: string;
    from:           null;
    last_page:      number;
    last_page_url:  string;
    links:          LinkA[];
    next_page_url:  null;
    path:           string;
    per_page:       number;
    prev_page_url:  null;
    to:             null;
    total:          number;
}

export interface DatDates {
    customer_name:              string;
    customer_lastname:          string;
    vehicle_brandname:          string;
    vehicle_modelname:          string;
    vehicle_mileage:            number;
    vehicle_year:               string;
    appointment_scheduled_date: string;
    dealership_name:            string;
    appointment_type:           string;
    phone_1:                    string;
    appointment_uuid:           string;
    valuator_name:              string;
    valuator_last_name:         string;
    valuator_uuid:              string;
}

export interface DatoTables{
    brand:              string;
    model:              string;
    name:               string;
    last_name:          string;
    phone1:             string;
    date:               string;
    dealership_name:    string;
    appointment_type:   string;
}

export interface LinkA {
    url:    null | string;
    label:  string;
    active: boolean;
}

export interface ValuatorsResponse {
    status:  number;
    message: string;
    data:    DataValuator;
}

export interface DataValuator {
    users: User[];
}

export interface User {
    uuid:       string;
    nickname:   string;
    email:      string;
    created_at: Date;
    user_profile: UserProfile
}

export interface UserProfile {
    name:       string;
    last_name:  string;
    gender:     null;
    phone_1:    null;
    phone_2:    null;
    picture:    null;
    location:   null;
    created_at: Date;
}
