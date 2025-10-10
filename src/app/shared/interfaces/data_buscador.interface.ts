export interface MinMaxPrices {
    code:   number;
    min:    number;
    max:    number;
    status: string;
}


export interface DataBuscador {
    code:     number;
    status:   string;
    vehicles: Vehicles;
}

export interface Vehicles {
    current_page:   number;
    data:           Vehicle[];
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

export interface Vehicle {
    id:              number;
    name:            string;
    description:     string;
    vin:             string;
    location:        null;
    yearModel:       number;
    purchaseDate:    Date;
    price:           number;
    priceList:       number;
    salePrice:       number;
    type:            string;
    carline:         string;
    cylinders:       number;
    colorInt:        string;
    colorExt:        string;
    status:          string;
    plates:          null | string;
    transmission:    string;
    inventoryDays:   number;
    km:              number;
    numKeys:         number;
    studs:           string;
    spareTire:       string;
    hydraulicJack:   string;
    extinguiser:     string;
    reflectives:     string;
    handbook:        string;
    insurancePolicy: string;
    powerCables:     string;
    promotion:       null;
    priceBond?:       null;
    priceOffer:      null | number;
    carmodel_id:     number;
    vehiclebody_id:  number;
    branch_id:       number;
    client_id:       number;
    created_at:      Date;
    updated_at:      Date;
    carmodel:        Carmodel;
    vehiclebody:     VehicleBody;
    branch:          Branch;
    vehicle_images:  VehicleImage[];
    shields:         Shield[];
    choices:         Choice[];
}

export interface Branch {
    id:           number;
    name:         string;
    state_id?:    number;
    created_at:   Date;
    updated_at:   Date;
    description?: null;
    state: State;
}

export interface VehicleBody {
    id:           number;
    name:         string;    
    description:  string | null;
    created_at:   Date;
    updated_at:   Date;
}

export interface State {
    id:          number;
    iso:         string;
    name:        string;
    description: string;
    created_at:  Date;
    updated_at:  Date;
}

export interface Carmodel {
    id:          number;
    name:        string;
    description: null;
    brand_id:    number;
    created_at:  Date;
    updated_at:  Date;
    brand:       Brand;
}

export interface Brand {
    id:          number;
    name:        string;
    description: null;
    location:    null;
    contact:     null;
    picture:     null;
    created_at:  Date;
    updated_at:  Date;
}

export interface VehicleImage {
    id:         number;
    path:       string;
    external_website: string;
    vehicle_id: number;
    created_at: Date | null;
    updated_at: Date | null;
}

export interface Link {
    url:    null | string;
    label:  string;
    active: boolean;
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
