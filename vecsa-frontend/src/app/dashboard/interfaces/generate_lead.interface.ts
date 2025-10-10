export interface GenerateLead {
    status:  string;
    code:    string;
    message: string;
    lead:    Lead;
}

export interface Lead {
    name:       string;
    surname:    null;
    email:      string;
    phone:      number;
    message:    string;
    updated_at: Date;
    created_at: Date;
    id:         number;
    vehicles:   any[];
}
