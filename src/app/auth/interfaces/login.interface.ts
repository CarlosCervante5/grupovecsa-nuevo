export interface LoginResponse {
  status:  number;
  message: string;
  data:    Data;
}

export interface RegisterResponse {
  status:  number;
  message: string;
  data:    Data;
}

export interface LogoutResponse {
  status:  number;
  message: string;
  data:    null;
}

export interface ShowProfileResponse {
  status:  number;
  message: string;
  data:    ShowData;
}

export interface UpdateProfileResponse {
  status:  number;
  message: string;
  data:    null;
}

export interface ShowData {
  user:    User;
  role:    string;
  profile: CustomerProfile;
}

export interface Data {
  token: string;
  user:  User;
  role: string;
  profile: CustomerProfile;
}

export interface User {
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
  created_at: Date
}

