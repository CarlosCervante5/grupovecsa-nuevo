export interface ClientData {
    code:   number;
    status: string;
    client: Client;
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
