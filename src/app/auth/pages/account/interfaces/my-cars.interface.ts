export interface VehicleUser {
    code:   number;
    status: string;
    vehicles: Vehicles[];
}

export interface Vehicles {
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
    plates:          null;
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
    carmodel_id:     number;
    vehiclebody_id:  number;
    branch_id:       number;
    client_id:       number;
    created_at:      Date;
    updated_at:      Date;
    carmodel:        Carmodel;
    branch:          Branch;
    vehicle_images:  VehicleImage[];
}

export interface Branch {
    id:         number;
    name:       string;
    state_id:   number;
    created_at: Date;
    updated_at: Date;
    state:      State;
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
    vehicle_id: number;
    external_website?: string;
    created_at: Date;
    updated_at: Date;
}
