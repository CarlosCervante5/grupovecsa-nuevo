export interface GetVehicleDetailParts {
    status:  number;
    message: string;
    data:    Data;
}

export interface Data {
    vehicle: Vehicle;
    parts:   Parts;
}

export interface Parts {
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
    uuid:       string;
    code:       null;
    name:       string;
    labor_time: number;
    quantity:   number;
    created_at: Date;
    suppliers:  any[];
}

export interface Link {
    url:    null | string;
    label:  string;
    active: boolean;
}

export interface Vehicle {
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
    dealership:     Dealership;
    specification:  Specification;
}

export interface Body {
    name:       string;
    image_path: null;
    created_at: Date;
    year?:      number;
}

export interface Dealership {
    name:        string;
    location?:   string;
    description: null;
    created_at:  Date;
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
