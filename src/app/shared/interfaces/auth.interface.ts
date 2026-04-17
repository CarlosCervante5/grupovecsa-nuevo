    export interface GralResponse{
        status:  number;
        message: string;
        errors?: string | null;
    }
    export interface LoginResponse extends GralResponse{
        data:    Data;
    }
    export interface RegisterResponse extends GralResponse{
        data:    Data;
    }
      
    export interface LogoutResponse extends GralResponse{
    data:    null;
    }

    /** GET /api/auth/me */
    export interface AuthMeResponse extends GralResponse {
        data: {
            permissions: string[];
            /** Presente cuando hay userProfile; el cliente fusiona con `profile` en localStorage. */
            profile?: { name?: string | null; last_name?: string | null } | null;
            /** Rol principal (misma lógica que login); el cliente puede sincronizar `localStorage.role`. */
            role?: string | null;
        };
    }
    
    export interface ShowProfileResponse extends GralResponse{
    data:    ShowData;
    }
    
    export interface UpdateProfileResponse extends GralResponse{
    data:    null;
    }
    export interface ShowData {
    user:    UserL;
    role:    string;
    profile: CustomerProfile;
    }

    export interface Data {
        token: string;
        user:  UserL;
        role: string;
        profile: CustomerProfile;
        permissions?: string[];
    }

    export interface UserL {
        uuid:       string;
        nickname:      string;
        email:      string;
        created_at: Date;
      }
    export interface CustomerProfile{
        name: string,
        last_name: string,
        gender: string | null,
        email_1: string | null,
        email_2: string | null,
        phone_1: string | null,
        phone_2: string | null,
        picture: string | null,
        birthday: string| null,
        created_at: Date,
        uuid: string
    }

    export interface RecoverAccount {
        status:  string;
        code:    string;
        message: string;
        token:   string;
    }

    export interface ResetPassword extends GralResponse{
        data:    null;
    }
    
    export interface User {
        id:         number;
        name:       string;
        surname:    string;
        email:      string;
        picture:    null;
        gender:     string;
        created_at: Date;
        updated_at: Date;
    }
    
    export interface RolData {
        code:   number;
        status: string;
        rol:    Rol | null;
    }
    
    export interface Rol {
        id:         number;
        name:       string;
        guard_name: string;
        created_at: Date;
        updated_at: Date;
    }

    export interface ImageData {
        status:  string;
        code:    string;
        message: string;
        user:    UserLI;
        errors: string[];
    }

    export interface UserLI {
        id:         number;
        name:       string;
        surname:    string;
        email:      string;
        picture:    string;
        gender:     string;
        created_at: Date;
        updated_at: Date;
    }
    
    export interface Grafica {
        type : string;
        title : string;
        tags : string[];
        data : number[];
        tags_img : string[],
    }
    
    export interface DataItem{
        name: string,
        value: number
    }
    
    export interface UserGrafic{
        data: DataItem[],
        user: string,
        img: string,
    }
    
    export interface QuizzesData {
        status:  string;
        message: string;
        data: Quiz[]
    }

    export interface Quiz{
        uuid: string,
        name: string,
        description: string,
        values: string[],
        status: string,
        question_type: string,
        element_type: string,
        group_name: string,
        image_path: string,
        selected_value: string,
        created_at: Date
    }

    export interface Question{
        uuid: string,
        name: string,
        description: string,
        values: string[],
        status: string,
        question_type: string,
        element_type: string,
        group_name: string,
        image_path: string,
        selected_value: string|null,
        created_at: Date
    }

    export interface Validator{
        uuid: string,
        invalid: boolean,
        validation_message: string,
    }

    //nos trae los datos de los 10 mejores puntajes
export interface PointsResponse{
    status:  number;
    message: string;
    data:    Datum[];
}

export interface Datum {
    picture:             null;
    nickname:            string;
    total_earned_points: number;
    position:            number;
}

//puntajes del un usuario especifico

export interface CustomerPositionResponse {
    status:  number;
    message: string;
    data:    CustomerP;
}

export interface CustomerP {
    profile: ProfileC;
    months:  number[];
}

export interface ProfileC {
    uuid:         string;
    total_points: number;
    month_points: number;
    position:     number;
}


export interface DetailsReward {
    status:  number;
    message: string;
    data:    DetailR;
}

export interface DetailR {
    uuid:        string;
    status:      null;
    vehicle_id:  number;
    reward_id:   number;
    customer_id: number;
    created_at:  Date;
    customer:    Customer;
    vehicle:     Vehicle;
    reward:      Reward;
    points:      Point[];
}

export interface Point {
    uuid:               string;
    redeemed:           number;
    earned_points:      number;
    purchase_amount:    null;
    initial_mileage:    number;
    final_mileage:      number;
    detail:             string;
    image_path:         null;
    customer_reward_id: number;
    created_at:         Date;
    images:             Image[];
}

export interface Image {
    name:       string;
    image_path: string;
    point_id:   number;
    created_at: Date;
}


export interface Reward {
    uuid:        string;
    name:        string;
    description: string;
    begin_date:  Date;
    end_date:    Date;
    category:    string;
    type:        null;
    image_path:  null;
    created_at:  Date;
}

export interface Vehicle {
    uuid:           string;
    name:           null;
    description:    null;
    vin:            null;
    mileage:        number;
    type:           string;
    cylinders:      null;
    interior_color: null;
    exterior_color: null;
    transmission:   null;
    fuel_type:      null;
    drive_train:    null;
    brand_name:     null;
    model_name:     string;
    line_name:      null;
    year:           string;
    version_name:   null;
    body_name:      null;
    created_at:     Date;
}

export interface Customer {
    uuid:              string;
    honorific:         string|null;
    bp_id:             string|null;
    crm_id:            string|null;
    rfc:               string|null;
    tax_regime:        string|null;
    name:              string;
    last_name:         string;
    age:               number|null;
    birthday:          string|null;
    gender:            string|null;
    phone_1:           string|null;
    phone_2:           string|null;
    phone_3:           string|null;
    cellphone:         string|null;
    email_1:           string|null;
    email_2:           string|null;
    contact_method:    string|null;
    zip_code:          string|null;
    address:           string|null;
    state:             string|null;
    city:              string|null;
    district:          string|null;
    neighborhood:      string|null;
    marital_status:    string|null;
    educational_level: string|null;
    origin_agency:     string|null;
    picture:           string|null;
    created_at:        Date;
}
