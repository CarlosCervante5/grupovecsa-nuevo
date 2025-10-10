export interface LoadVehicles {
    code:      number;
    status:    string;
    respuesta: Respuesta;
}

export interface Respuesta {
    total:  number;
    exists: string[];
    added:  string[];
    errors: string[];
}
