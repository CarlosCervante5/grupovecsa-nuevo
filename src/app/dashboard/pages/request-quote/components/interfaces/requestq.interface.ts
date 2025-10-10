export interface Sheetquote {
    status:      string;
    code:        string;
    message:     string;
    sheet_quote: SheetQuote;
    sheet_offer: SheetOffer;
}

export interface SheetQuote {
    body:    string;
    brand:   string;
    model:   string;
    name:    string;
    surname: string;
    email:   string;
    phone:   string;
    buyType: string;
}

export interface SheetOffer {
    status:  string;
    code:    string;
    body:    string;
    brand:   string;
    model:   string;
    name:    string;
    surname: string;
    email:   string;
    phone:   string;
    clientPriceOffer: number;
}
