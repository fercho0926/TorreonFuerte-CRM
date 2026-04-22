namespace TorreonFuerte.Application.DTOs;

public record LoginRequest(string Email, string Password);

public record LoginResponse(
    string Token,
    string Email,
    string Nombre,
    string Apellido,
    string Rol,
    int SedeId,
    string SedeNombre
);

public record RegisterRequest(
    string Email,
    string Password,
    string Nombre,
    string Apellido,
    int SedeId,
    string Rol
);
