export interface DataStates {
    code:   number;
    status: string;
    states: State[];
    total:  number;
}

export interface State {
    location:    string;    
}
