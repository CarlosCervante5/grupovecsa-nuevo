export interface GetChecklist {
    status:  number;
    message: string;
    // data:    Datum[];
    data:    Checklist[];
}

export interface GralResponse{
    status:  number;
    message: string;
    errors?: string | null;
}

// export interface Datum {
export interface Checklist {
    uuid:           string;
    name:           string;
    description:    string;
    values:         Value[];
    value_type:     ValueType;
    section_name:   SectionName;
    image_path:     null;
    selected_value: null;
    created_at:     null;
}

export enum SectionName {
    CertificaciónDeVehículo = "Certificación de Vehículo",
    MecánicaYEléctrica = "Mecánica y Eléctrica",
    RevisiónExterior = "Revisión Exterior",
    RevisiónInterior = "Revisión Interior",
}

export enum ValueType {
    Date = "date",
    Number = "number",
    Select = "select",
    TextArea = "textArea",
}

export enum Value {
    Empty = "",
    NA = "n/a",
    No = "no",
    Si = "si",
}


export interface SpareParts { 
    name: String;
    // amount: Number;
    quantity: Number;
    // hours: Number;
    labor_time: Number;
    // sell_your_car_id: string;
    valuation_uuid: string;
}