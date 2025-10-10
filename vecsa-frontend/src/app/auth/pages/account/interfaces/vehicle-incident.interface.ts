export interface VehicleIncident {
    status:   string;
    code:     string;
    message:  string;
    incident: Incident;
}

export interface Incident {
    name:        string;
    description: string;
    status:      string;
    client_id:   number;
    vehicle_id:  number;
    updated_at:  Date;
    created_at:  Date;
    id:          number;
}
