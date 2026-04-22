export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  token: string;
  email: string;
  nombre: string;
  apellido: string;
  rol: string;
  sedeId: number;
  sedeNombre: string;
}

export interface Usuario {
  email: string;
  nombre: string;
  apellido: string;
  rol: string;
  sedeId: number;
  sedeNombre: string;
}
