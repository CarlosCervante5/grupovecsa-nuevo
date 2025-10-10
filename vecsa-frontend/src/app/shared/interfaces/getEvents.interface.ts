export interface GetEvents {
    code:   number;
    status: string;
    events: Evento[];
}

export interface Evento {
    id:         number;
    title:      string;
    subtitle:      string;
    path:       string;
    brand:      null;
    type:       string;
    status:     string;
    event_date: Date;
    detail:     null;
    event_images: EventImage[];
    created_at: Date;
    updated_at: Date;
}

export interface EventImage {
    id:         number;
    path:       string;
    event_id:   number;
    created_at: Date;
    updated_at: Date;
  }

export interface MyEvents {
    status:  number;
    message: string;
    data:    Data;
}
export interface Data {
    events:  Events[];
}

export interface Events {
    uuid:        string;
    begin_date:  Date;
    end_date:    Date;
    name:        string;
    description: string;
    type:        string;
    image_path:  string;
    created_at:  Date;
    multimedia: Multimedia[];
}

export interface Multimedia {
    uuid:            string;
    sort_id:         number;
    name:            null;
    description:     null;
    multimedia_path: string;
    created_at:      Date;
}