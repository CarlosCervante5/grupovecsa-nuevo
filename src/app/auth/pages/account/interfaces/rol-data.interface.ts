export interface RolData {
    code:   number;
    status: string;
    rol:    Rol | null;
}

export interface Rol {
    id:         number;
    name:       string;
    guard_name: string;
    created_at: Date;
    updated_at: Date;
}
