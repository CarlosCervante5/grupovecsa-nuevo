export interface ReceptionForm {
    status:         string;
    code:           string;
    message:        string;
    reception_form: ReceptionFormClass;
}

export interface ReceptionFormClass {
    date:                string;
    hour:                string;
    salesAdvisor:        string;
    brand:               string;
    departureTime:       string;
    visitType:           string;
    visitFirsTime:       string;
    department:          string;
    otherDepartment:     string;
    howFindOut:          string;
    commentaryLead:      string;
    clientName:          string;
    clientAge:           string;
    clientPhone:         string;
    clientEmail:         string;
    preferredMedium:     string;
    model:               string;
    year:                string;
    version:             string;
    color:               string;
    accessories:         string;
    brandSecondOption:   string;
    modelSecondOption:   string;
    versionSecondOption: string;
    colorSecondOption:   string;
    testDrive:           string;
    receivedQuote:       string;
    FAndI:               string;
    leaveCarOnAccount:   string;
    customersCurrentCar: string;
    updated_at:          Date;
    created_at:          Date;
    id:                  number;
}
