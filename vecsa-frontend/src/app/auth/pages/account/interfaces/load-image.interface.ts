export interface ImageData {
    status:  string;
    code:    string;
    message: string;
    user:    User;
    errors: string[];
}

export interface User {
    id:         number;
    name:       string;
    surname:    string;
    email:      string;
    picture:    string;
    gender:     string;
    created_at: Date;
    updated_at: Date;
}
