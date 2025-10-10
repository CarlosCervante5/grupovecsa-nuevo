// Rewards
export interface Rewards {
    code:   number;
    status: string;
    reward: Rewards;
}

// Get Reward
export interface Reward {
    code:   number;
    status: string;
    reward: RewardData;
    redemption: boolean;
}

// Post Rewards
export interface PostReward {
    code:    number;
    status:  string;
    message: string;
    rewards: RewardResponse;
}

// Update Reward
export interface UpdateReward {
    code:    number;
    status:  string;
    message: string;
    rewards: RewardResponse;
}

// Reset Reward
export interface ResetReward {
    code:    number;
    status:  string;
    message: string;
    client:  Client;
    rewards: DataRewards;
}

export interface RewardResponse {
    id:         number;
    status:     string;
    client_id:  number;
    created_at: string;
    updated_at: string;
    client?:     Client;
}


// Checking Reward
export interface CheckingReward {
    code:    number;
    status:  string;
    message: string;
    rewards: Client;
}

export interface RewardData {
    id:         number;
    name:       string;
    surname:    string;
    email:      string;
    picture:    null;
    gender:     string;
    created_at: string;
    updated_at: string;
    clients:    Client[];
}


export interface Rewards {
    current_page:   number;
    data:           DataRewards[];
    first_page_url: string;
    from:           number;
    last_page:      number;
    last_page_url:  string;
    links:          Link[];
    next_page_url:  null;
    path:           string;
    per_page:       number;
    prev_page_url:  null;
    to:             number;
    total:          number;
}

export interface DataRewards {
    id:         number;
    status:     string;
    client_id:  number;
    created_at: string;
    updated_at: string;
    client:     Client;
}

export interface Client {
    id:         number;
    phone1:     number;
    phone2:     number;
    curp:       string;
    points:     number;
    rewards:    string;
    user_id:    number;
    source_id:  number;
    created_at: string;
    updated_at: string;
    user:       User;
}

export interface User {
    id:         number;
    name:       string;
    surname:    string;
    email:      string;
    picture:    null;
    gender:     string;
    created_at: string;
    updated_at: string;
}

export interface Link {
    url:    null | string;
    label:  string;
    active: boolean;
}
