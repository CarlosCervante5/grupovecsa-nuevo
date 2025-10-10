export interface Leads {
    status:  string;
    code:    string;
    message: string;
    lead:    Lead;
}

export interface Lead {
    name:             string;
    email:            string;
    phone:            string;
    brand:            string;
    type:             string;
    appointment_date: Date;
    description:      string;
    updated_at:       Date;
    created_at:       Date;
    id:               number;
}
