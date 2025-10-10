export interface GetAcquisitionsChecklist {
    status:  number;
    message: string;
    data:    ChecklistItem[];
  }
  
  export interface ChecklistItem {
    uuid:           string;
    name:           string;
    description:    string;
    values:         string[]; // Puede ser una lista de strings o números según el JSON | number[]
    value_type:     string; // Ejemplo: "select" o "number"
    section_name:   string;
    image_path:     string | null;
    selected_value: string | number | null;
    created_at:     string | null;
  }
  