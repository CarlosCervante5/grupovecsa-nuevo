export interface Financing {
    code:       number;
    status:     string;
    message:    string;
    financings: FinancingItem[];
}

export interface FinancingItem {
    id:                   number;
    status:               string;
    price:                number;
    hitch:                number;
    year:                 string;
    brand_id:             number;
    carmodel_id:          number;
    monthly_fees:         number;
    rfc:                  string;
    civil_status:         string;
    studies_level:        string;
    economic_dependents:  number;
    has_vehicle:          string;
    street_name:          string;
    suburb:               string;
    number:               number;
    postal_code:          number;
    state_id:             number;
    municipality:         string;
    company:              string;
    employment_situation: string;
    salary:               number;
    role:                 string;
    date:                 string;
    client_id:            number;
    created_at:           string;
    updated_at:           string;
    brand:                Brand;
    carmodel:             Carmodel;
}

export interface Brand {
    id:          number;
    name:        string;
    description: string;
    location:    string;
    contact:     number;
    picture:     string;
    created_at:  string;
    updated_at:  string;
}

export interface Carmodel {
    id:          number;
    name:        string;
    description: string;
    brand_id:    number;
    created_at:  string;
    updated_at:  string;
}