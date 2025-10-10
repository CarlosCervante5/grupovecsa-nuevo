export interface DataModels {
    code:   number;
    status: string;
    models: Model[];
}

export interface Model {
    id:          number;
    name:        string;
    description: string;
    brand_id:    number;
    created_at:  Date;
    updated_at:  Date;
}
