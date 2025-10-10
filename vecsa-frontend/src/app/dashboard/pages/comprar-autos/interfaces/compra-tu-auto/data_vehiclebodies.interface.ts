export interface DataVehicleBody {
    code:          number;
    status:        string;
    vehiclebodies: Vehiclebody[];
    total:         number;
}

export interface Vehiclebody {
    id:          number;
    name:        string;
    description: null;
    created_at:  Date;
    updated_at:  Date;
}
