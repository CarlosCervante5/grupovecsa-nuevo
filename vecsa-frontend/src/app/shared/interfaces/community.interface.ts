export interface UploadVideo {
    status:  string;
    code:    string;
    message: string;
    event:   Event;
}

export interface Event {
    title:      string;
    status:     string;
    type:       string;
    event_date: Date;
    path:       string;
    updated_at: Date;
    created_at: Date;
    id:         number;
}

export interface UploadEventImages {
    status:  string;
    code:    string;
    message: string;
    event:   Event;
}

export interface Event {
    id:           number;
    title:        string;
    subtitle:     string;
    description:  string;
    path:         string;
    brand:        string;
    type:         string;
    status:       string;
    event_date:   Date;
    detail:       null;
    created_at:   Date;
    updated_at:   Date;
    event_images: EventImage[];
}

export interface EventImage {
    id:         number;
    path:       string;
    event_id:   number;
    created_at: Date;
    updated_at: Date;
}

export interface CreateEvent {
    status:  string;
    code:    string;
    message: string;
    event:   Event;
}

export interface DeleteEvent {
    code:   number;
    status: string;
    message: string;
}
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

export interface Gral{
    status:      string;
    code:        string;
    message?:     string;
}

 export interface Calendar extends Gral {
    events: Calendary[];
}

export interface Calendary {
    id:         number;
    title:      string;
    description:string;
    path:       string;
    brand:      string;
    type:       string;
    status:     string;
    event_date: string;
    detail:     string;
    created_at: string;
    updated_at: string;
    event_images: EventImage[];
}

export interface DeleteEventImage {
    status:  string;
    code:    number;
    message: string;
  }

