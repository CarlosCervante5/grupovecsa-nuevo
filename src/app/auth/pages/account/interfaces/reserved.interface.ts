export interface Reserveds {
    code:   number;
    status: string;
    choices: Reserved[];
}

export interface Reserved {
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
    vehicle:     Vehicle;
}

export interface Vehicle {
    id:              number;
    name:            string;
    description:     string;
    vin:             string;
    location:        string;
    yearModel:       number;
    purchaseDate:    string;
    price:           number;
    priceList:       number;
    salePrice:       number;
    type:            string;
    carline:         string;
    cylinders:       number;
    colorInt:        string;
    colorExt:        string;
    status:          string;
    plates:          string;
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
    created_at:      string;
    updated_at:      string;
}

export interface UnReserved {
    code: number;
    status: string;
    message: string;
}