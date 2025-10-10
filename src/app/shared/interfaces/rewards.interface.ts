export interface GralResponse{
    status:  number;
    message: string;
    errors?: string | null;
}
export interface RewardResponse {
    status:  number;
    message: string;
    data:    Reward;
}

export interface RewardsResponse {
    status:  number;
    message: string;
    data:    Reward[];
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

export interface PointsResponse {
    status:  number;
    message: string;
    data:    CustomerPoints;
}

export interface CustomerPoints {
    total_earned_points: number
}

export interface RedeemPointsResponse extends GralResponse{

    data: Cuopon;

}

export interface Cuopon {
    
    uuid: string;
    code: string;
    discount_type: string;
    amount: number;
    indivudual_use: boolean;
    usage_limit: number;
    created_at: Date;

}