export interface DataBrands {
    code:   number;
    status: string;
    brands: Brand[];
    total:  number;
}

export interface Brand {
    id:          number;
    name:        string;
    description: null;
    location:    null;
    contact:     null;
    picture:     null;
    created_at:  Date;
    updated_at:  Date;
}
