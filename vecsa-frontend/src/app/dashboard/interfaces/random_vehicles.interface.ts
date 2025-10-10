export interface RandomVehiclesData {
    code:     number;
    status:   string;
    vehicles: Vehicle[];
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
    id:          number;
    iso?:        string;
    name:        string;
    description: string;
    created_at:  Date;
    updated_at:  Date;
}

export interface State {
    id:          number;
    iso:         string;
    name:        string;
    description: string;
    created_at:  Date;
    updated_at:  Date;
}

export interface VehicleImage {
    id:         number;
    path:       string;
    external_website: string;
    vehicle_id: number;
    created_at: Date;
    updated_at: Date;
}

export enum Name {
    Cholula = "cholula",
    Matriz = "matriz",
    Ninguno = "ninguno",
    Zacatelco = "zacatelco",
}

export enum Carline {
    Uno = "uno",
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

export enum Extinguiser {
    No = "no",
    Yes = "yes",
}

export enum Status {
    Active = "active",
}

export enum Transmission {
    Automatico = "automatico",
    Manual = "manual",
}

export enum Type {
    PreOwned = "pre_owned",
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