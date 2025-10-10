export interface UpdateQuoteValuation {
    status:  number;
    message: string;
    data:    Data;
}

export interface Data {
    uuid:                         string;
    book_trade_in_offer:          string;
    book_sale_price:              string;
    intellimotors_trade_in_offer: string;
    intellimotors_sale_price:     string;
    labor_cost:                   string;
    spare_parts_cost:             string;
    body_work_painting_cost:      string;
    estimated_total:              string;
    final_offer:                  string;
    trade_in_final:               string;
    status:                       string;
    comments:                     string;
    take_type:                    string;
    created_at:                   Date;
}
