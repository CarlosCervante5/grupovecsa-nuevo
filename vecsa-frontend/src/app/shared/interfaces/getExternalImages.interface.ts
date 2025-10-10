export interface GetExternalImages {
    status:  number;
    message: string;
    data:    Datum[];
}

export interface Datum {
    uuid:       string;
    sort_id:    number;
    name:       string;
    image_path: string;
    group_name: string;
    created_at: Date;
}
