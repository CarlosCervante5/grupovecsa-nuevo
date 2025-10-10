export interface UserSettings {
    code: number;
    status: string;
    user: UserData;
}

export interface UserData {
    id: number;
    name: string;
    surname: string;
    email: string;
    picture: string | null;
    gender: string;
    created_at: Date;
    updated_at: Date;
    clients: Client[];
}

export interface Client {
    id: number;
    phone1: number;
    phone2: null;
    curp: string;
    points: number;
    user_id: number;
    source_id: number;
    created_at: Date;
    updated_at: Date;
}

export interface UserUpdate {
    status:  string;
    code:    string;
    message: string;
    user:    UserUpdateData;
}

export interface UserUpdateData {
    id:         number;
    name:       string;
    surname:    string;
    email:      string;
    picture:    null;
    gender:     string;
    created_at: Date;
    updated_at: Date;
}

export interface UserByEmail {
    code:   number;
    status: string;
    user:   User;
}

export interface User {
    id:         number;
    name:       string;
    surname:    string;
    email:      string;
    picture:    null;
    gender:     string;
    created_at: Date;
    updated_at: Date;
    clients:    any[];
}

