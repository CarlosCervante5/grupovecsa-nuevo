export interface Services {
    code:    number;
    status:  string;
    services: Service[];
}

export interface Service {
    id:          number;
    name:        string;
    description: string;
    amount:      number;
    points:      number;
    interchange: number;
    created_at:  Date;
    updated_at:  Date;
}

// Start Quotes Services Customer
export interface ServicesClient {
    code:     number;
    status:   string;
    vehicles: VehicleElement[];
}

export interface VehicleElement {
    vehicle: VehicleVehicle;
    quotes:  Quote[];
}

export interface Quote {
    id:          number;
    type:        string;
    vin:         string;
    status:      string;
    quoteDate:   Date;
    client_id:   number;
    brand_id:    number;
    carmodel_id: number;
    created_at:  Date;
    updated_at:  Date;
    services:    Service[];
}

export interface Service {
    id:          number;
    name:        string;
    description: string;
    amount:      number;
    points:      number;
    interchange: number;
    created_at:  Date;
    updated_at:  Date;
    pivot:       Pivot;
}

export interface Pivot {
    quote_id:   number;
    service_id: number;
}

export interface VehicleVehicle {
    id:               number;
    name:             string;
    yearModel:        number;
    km:               number;
    transmission:     string;
    carmodel:         Carmodel;
    vehicle_images:   VehicleImage[];
    description?:     string;
    vin?:             string;
    location?:        null;
    purchaseDate?:    Date;
    price?:           number;
    priceList?:       number;
    salePrice?:       number;
    type?:            string;
    carline?:         string;
    cylinders?:       number;
    colorInt?:        string;
    colorExt?:        string;
    status?:          string;
    plates?:          null;
    inventoryDays?:   number;
    numKeys?:         number;
    studs?:           string;
    spareTire?:       string;
    hydraulicJack?:   string;
    extinguiser?:     string;
    reflectives?:     string;
    handbook?:        string;
    insurancePolicy?: string;
    powerCables?:     string;
    promotion?:       null;
    carmodel_id?:     number;
    vehiclebody_id?:  number;
    branch_id?:       number;
    client_id?:       number;
    created_at?:      Date;
    updated_at?:      Date;
}

export interface Carmodel {
    brand:        Brand;
    id?:          number;
    name?:        string;
    description?: null;
    brand_id?:    number;
    created_at?:  Date;
    updated_at?:  Date;
}

export interface Brand {
    name:         string;
    id?:          number;
    description?: string;
    location?:    string;
    contact?:     number;
    picture?:     string;
    created_at?:  Date;
    updated_at?:  Date;
}

export interface VehicleImage {
    id:         number;
    path:       string;
    vehicle_id: number;
    external_website?: string;
    created_at: Date;
    updated_at: Date;
}
// End Quotes Services Customer

// Start Get client information by user_id
export interface ClientServices {
    code:    number;
    status:  string;
    client: ClientByUser;
}

export interface ClientByUser {
    id: number;
    phone1: number;
    phone2: number;
    curp: string;
    points: number;
    user_id: number;
    source_id: number;
    created_at: Date;
    updated_at: Date;
}
// End Get client information by user_id

// Start Register Quote
export interface RegisterQuote {
    status:  string;
    code:    string;
    message: string;
    quote:   Quote;
}

export interface Quote {
    id:          number;
    type:        string;
    vin:         string;
    status:      string;
    quoteDate:   Date;
    client_id:   number;
    brand_id:    number;
    carmodel_id: number;
    updated_at:  Date;
    created_at:  Date;
}
// End Register Quote

// Start Register Service a Quote
export interface RegisterServiceQuote {
    status:  string;
    code:    string;
    message: string;
    quote:   Quote;
}

export interface Quote {
    id:          number;
    type:        string;
    vin:         string;
    status:      string;
    quoteDate:   Date;
    client_id:   number;
    brand_id:    number;
    carmodel_id: number;
    created_at:  Date;
    updated_at:  Date;
    services:    Service[];
}

export interface Service {
    id:          number;
    name:        string;
    description: string;
    amount:      number;
    points:      number;
    interchange: number;
    created_at:  Date;
    updated_at:  Date;
    pivot:       Pivot;
}

export interface Pivot {
    quote_id:   number;
    service_id: number;
}
// End Register Service a Quote
