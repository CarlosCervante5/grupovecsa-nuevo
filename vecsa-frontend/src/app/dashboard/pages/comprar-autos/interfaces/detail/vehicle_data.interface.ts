export interface DetailResponse {
    status:  number;
    message: string;
    errors: string | null;
    data:    Vehicle;
}

export interface FullDetailResponse {
    status:  number;
    message: string;
    errors: string | null;
    data:    UpdateVehicle;
}

export interface RecommendedResponse {
    status:  number;
    message: string;
    errors: string | null;
    data:    Vehicle[];
}
export interface SearchResponse {
    status:  number;
    message: string;
    errors: string | null;
    data:    Data;
}

export interface MinMaxResponse {
    status: number;
    message: string;
    data:  MinMax;
}

export interface MinMax {
    max_price: number;
    min_price: number;
}
export interface BrandsResponse {
    status:  number;
    message: string;
    errors: string | null;
    data:    VehicleBrands;
}

export interface VehicleBrands {
    vehicle_brands: Brand [];
}

export interface LinesResponse {
    status:  number;
    message: string;
    errors: string | null;
    data:    BrandLines;
}

export interface BrandLines {
    brand_lines: Line [];
}

export interface ModelsResponse {
    status:  number;
    message: string;
    errors: string | null;
    data:    LineModels;
}

export interface LineModels {
    line_models: Model [];
}

export interface VersionsResponse {
    status:  number;
    message: string;
    errors: string | null;
    data:    ModelVersions;
}

export interface ModelVersions {
    model_versions: Version [];
}

export interface BodiesResponse {
    status:  number;
    message: string;
    errors: string | null;
    data:    VehicleBodies;
}

export interface VehicleBodies {
    vehicle_bodies: Body [];
}

export interface VehicleUpdateResponse {
    status:  number;
    message: string;
    errors: string | null;
    data:    Vehicle;
}

export interface FiltersResponse {
    status: number;
    message: string;
    data:  Filters;
}

export interface LoadVehiclesResponse {
    status: number;
    message: string;
    data:  CsvError [];
}

export interface CsvError{
    row: number,
    attribute: string,
    errors: string []
}

export interface Filters {
    categories:      string[];
    brands:          string[];
    lines:           string[];
    models:          string[];
    versions:        string[];
    bodies:          string[];
    transmissions:   string[];
    interior_colors: string[];
    exterior_colors: string[];
    years:           number[];
    locations:       string[];
    max_price:       number;
    min_price:       number;
}

export interface Data {
    current_page: number,
    data: Vehicle[],
    first_page_url: string,
    from: number,
    last_page: number,
    last_page_url: string,
    links: Links[],
    next_page_url: string | null,
    path: string,
    per_page: number,
    prev_page_url: string | null,
    to: number,
    total: number
}
export interface Links {
    url: string | null,
    label: string,
    active: boolean
}

export interface Vehicle {
    uuid:           string;
    name:           string;
    description:    string;
    vin:            string;
    purchase_date:  Date;
    sale_price:     number;
    list_price:     number;
    offer_price:    number;
    mileage:        number;
    type:           string;
    category:       string;
    cylinders:      number;
    interior_color: string;
    exterior_color: string;
    transmission:   string;
    drive_train:    string;
    page_status:    string;
    created_at:     Date;
    brand:          Brand;
    line:           Line;
    model:          Model;
    version:        Version;
    body:           Body;
    dealership:     Dealership;
    specification:  Specification | null;
    first_image:     Image;
    images:         Image[];
}

export interface VehicleForm {
    uuid:           string;
    name:           string;
    description:    string;
    vin:            string;
    purchase_date:  Date;
    sale_price:     number;
    list_price:     number;
    offer_price:    number;
    mileage:        number;
    type:           string;
    category:       string;
    cylinders:      number;
    interior_color: string;
    exterior_color: string;
    transmission:   string;
    drive_train:    string;
    page_status:    string;
    created_at:     string;
    brand:          string;
    line:           string;
    model:          string;
    version:        string;
    body:           string;
    location:       string;
}

export interface UpdateVehicle {
    uuid:           string;
    name:           string;
    description:    string;
    vin:            string;
    purchase_date:  Date;
    sale_price:     number;
    list_price:     number;
    offer_price:    number;
    mileage:        number;
    type:           string;
    category:       string;
    cylinders:      number;
    interior_color: string;
    exterior_color: string;
    transmission:   string;
    fuel_type:      string;
    drive_train:    string;
    page_status:    string;
    created_at:     Date;
    brand:          Brand;
    line:           Line;
    model:          Model;
    version:        Version;
    body:           Body;
    dealership:     Dealership;
    specification:  Specification;
    first_image:     Image;
    images:         Image[];
}

export interface Brand {
    name:       string;
    image_path: string;
    created_at: Date;
}

export interface Line {
    name:       string;
    image_path: string;
    created_at: Date;
}

export interface Model {
    name:       string;
    year:       number;
    image_path: string;
    created_at: Date;
}

export interface Version {
    name:        string;
    description: string;
    created_at:  Date;
}

export interface Body {
    name:       string;
    image_path: string;
    created_at: Date;
}
export interface Dealership {
    name:        string;
    location:    string;
    description: string;
    created_at:  Date;
}

export interface Specification {
    keys_number:       number;
    wheel_locks:       string;
    spare_wheel:       string;
    hydraulic_jack:    string;
    fire_extinguisher: string;
    reflectors:        string;
    jumper_cables:     string;
    engine_type:       string;
    plates:            string;
    country_of_origin: string;
    auto_start_stop:   string;
    intake_engine:     string;
    vehicle_id:        number;
    created_at:        Date;
}

export interface Image {
    service_image_url: string;
    created_at:        Date;
}

export interface ImageCarousel {
    path: string;
}

export interface Client {
    id:         number;
    phone1:     number;
    phone2:     null;
    curp:       string;
    points:     number;
    user_id:    number;
    source_id:  number;
    created_at: Date;
    updated_at: Date;
    user:       User;
}

export interface User {
    id:         number;
    name:       string;
    surname:    null;
    email:      string;
    picture:    null;
    gender:     string;
    created_at: Date;
    updated_at: Date;
}

export interface VehicleImage {
    id:         number;
    path:       string;
    external_website: string;
    vehicle_id: number;
    created_at: Date;
    updated_at: Date;
}
 export interface Image {      
    path:       string;    
 }
 export interface ImageOrder {
    id:          string;
    sort_id:     string;
    path:        string;
    path_public: string;
    selected: boolean;
    external_website: string | null;
 }

export interface Notification {  
    status: string;
    code: string;
    message: string;
}

export interface Shield {
    id:         number;
    name:       string;
    path:       string;
    created_at: string;
    updated_at: string;
    pivot:      Pivot;
}

export interface Pivot {
    vehicle_id: number;
    shield_id:  number;
}

export interface Choice {
    id:          number;
    amount:      number;
    namePayment: string;
    status:      string;
    reference:   string;
    amountDate:  string;
    vehicle_id:  number;
    client_id:   number;
    rewards:     null;
    created_at:  string;
    updated_at:  string;
}

export interface ImageOrderPromo {
    id:         number;
    uuid:       any;
    path:       string;
    external_website: string | null;
 }

