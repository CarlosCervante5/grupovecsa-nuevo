export interface DataTransmissions {
    code:   number;
    status: string;
    transmissions:  Transmission[];
    total:  number;
}

export interface Transmission {
    transmission: string;
}
