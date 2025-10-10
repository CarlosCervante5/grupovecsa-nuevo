export interface GetDetailValuation {
    status:  number;
    message: string;
    data:    Data;
}

export interface Data {
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
    trade_in:                     null;
    preparation:                  null;
    direct_purchase:              null;
    vehicle_interest:             null;
    vehicle_interest_vin:         null;
    comments:                     null;
    created_at:                   Date;
    vehicle:                      DataVehicle | null;
    dealership:                   Dealership;
    appointment:                  Appointment;
    technician:                   Technician[];
    spare_parts:                  SparePart[];
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
    vehicle:             Vehicle;
    customer:            Customer;
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

export interface Dealership {
    name:        string;
    location:    string;
    description: null;
    created_at:  Date;
}

export interface Repair {
    uuid:        string;
    description: string;
    cost:        null;
    status:      string;
    image_path:  null | string;
    created_at:  Date;
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
    uuid:           string;
    nickname:       string;
    email:          string;
    created_at:     Date;
    user_profile:   UserProfile;
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

export interface DataVehicle {
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
    version:        Dealership;
    body:           Body;
    specification:  Specification;
}

export interface Body {
    name:       string;
    image_path: null;
    created_at: Date;
    year?:      number;
}

export interface Specification {
    keys_number:       number;
    wheel_locks:       null;
    spare_wheel:       null;
    hydraulic_jack:    null;
    fire_extinguisher: null;
    reflectors:        null;
    jumper_cables:     null;
    engine_type:       string;
    plates:            string;
    country_of_origin: string;
    auto_start_stop:   string;
    tools:             null;
    antenna:           null;
    stud_wrench:       null;
    security_film:     null;
    warranty_policy:   null;
    warranty_manual:   null;
    intake_engine:     string;
    vehicle_id:        number;
    created_at:        Date;
}