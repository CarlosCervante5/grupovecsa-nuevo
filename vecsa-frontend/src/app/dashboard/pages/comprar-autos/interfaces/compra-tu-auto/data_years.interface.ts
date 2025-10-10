export interface DataYears {
    code:   number;
    status: string;
    years:  Year[];
    total:  number;
}

export interface Year {
    yearModel: number;
}
