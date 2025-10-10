export interface Brands {
    code: number;
    status: string;
    brands: Branch[];
}

export interface Branch {
    id: number;
    name: string;
    description: null;
    location: null;
    contact: null;
    picture: null;
    created_at: Date;
    updated_at: Date;
}