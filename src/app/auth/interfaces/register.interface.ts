export interface Register {
    status:  string;
    code:    string;
    message: string;
    user:    User;
}

export interface User {
    name:       string;
    surname:    string;
    email:      string;
    gender:     string;
    updated_at: Date;
    created_at: Date;
    id:         number;
    roles:      Role[];
}

export interface Role {
    id:         number;
    name:       string;
    guard_name: string;
    created_at: Date;
    updated_at: Date;
    pivot:      Pivot;
}

export interface Pivot {
    model_id:   number;
    role_id:    number;
    model_type: string;
}

export interface Client {    
    code: string;
    status: string;
    message: string;
    client?: any;
}
