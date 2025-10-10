export interface Type {
    code:   number;
    status: string;
    types:  TypeElement[];
    total:  number;
}

export interface TypeElement {
    type: string;
}
