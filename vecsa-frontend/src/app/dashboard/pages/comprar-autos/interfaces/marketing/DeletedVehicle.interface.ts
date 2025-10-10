export interface DeletedVehicle {
    status:           string;
    deleted_vehicles: DeletedVehicleElement[];
    total:            number;
}

export interface DeletedVehicleElement {
    id:              number;
    name:            string;
    description:     string;
    vin:             string;
    location:        string;
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
    priceOffer:      null;
    carmodel_id:     number;
    vehiclebody_id:  number;
    branch_id:       number;
    created_at:      Date;
    updated_at:      Date;
    deleted_at:      Date;
    vehicle_images:  any[];
}
