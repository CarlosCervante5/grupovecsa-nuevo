export interface IncidentData {
    status:    string;
    code:      string;
    uno:       number;
    incidents: Incident[];
}

export interface Incident {
    id:          number;
    name:        string;
    description: string;
    status:      string;
    client_id:   number;
    vehicle_id:  number;
    created_at:  Date;
    updated_at:  Date;
    vehicle:     Vehicle;
    client:      Client;
    service:     Service;
}

export interface Client {
    id:         number;
    phone1:     number;
    phone2:     null;
    curp:       null;
    points:     number;
    user_id:    number;
    source_id:  number;
    created_at: Date;
    updated_at: Date;
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