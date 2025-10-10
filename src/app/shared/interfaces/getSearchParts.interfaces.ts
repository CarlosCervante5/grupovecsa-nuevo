export interface GetSearchParts {
    status:  number;
    message: string;
    data:    Data;
}

export interface Data {
    current_page:   number;
    data:           Datum[];
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

export interface Datum {
    uuid:                         string;
    book_trade_in_offer:          null;
    book_sale_price:              null;
    intellimotors_trade_in_offer: null;
    intellimotors_sale_price:     null;
    labor_cost:                   null;
    spare_parts_cost:             null;
    body_work_painting_cost:      null;
    status:                       string;
    comments:                     null;
    created_at:                   Date;
    appointment:                  Appointment;
    technician:                   Technician[];
    vehicle:                      DatumVehicle;
    spare_parts:                  SparePart[];
}

export interface Appointment {
    uuid:                string;
    type:                string;
    description:         null;
    scheduled_date:      string;
    attendance_date:     null;
    departure_date:      null;
    dealership_name:     string;
    status:              string;
    prev_appointment_id: null;
    created_at:          Date;
    customer:            Customer;
    vehicle:             AppointmentVehicle;
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
    phone_1:           string;
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

export interface AppointmentVehicle {
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

export interface SparePart {
    uuid:       string;
    code:       null;
    name:       string;
    labor_time: number;
    quantity:   number;
    created_at: Date;
}

export interface Technician {
    uuid:         string;
    nickname:     string;
    email:        string;
    created_at:   Date;
    user_profile: UserProfile;
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

export interface DatumVehicle {
    uuid:           string;
    name:           null;
    description:    null;
    vin:            string;
    purchase_date:  null;
    sale_price:     null;
    list_price:     null;
    offer_price:    null;
    mileage:        number;
    type:           string;
    category:       null;
    cylinders:      number;
    interior_color: null;
    exterior_color: string;
    transmission:   string;
    fuel_type:      null;
    drive_train:    null;
    page_status:    string;
    created_at:     Date;
    brand:          Body;
    line:           Body;
    model:          Body;
    version:        Version;
    body:           Body;
}

export interface Body {
    name:       string;
    image_path: null;
    created_at: Date;
    year?:      number;
}

export interface Version {
    name:        string;
    description: null;
    created_at:  Date;
}

export interface Link {
    url:    null | string;
    label:  string;
    active: boolean;
}
