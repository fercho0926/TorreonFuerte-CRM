using TorreonFuerte.Application.DTOs;

namespace TorreonFuerte.Application.Interfaces;

public interface IAuthService
{
    Task<LoginResponse> LoginAsync(LoginRequest request);
    Task RegisterAsync(RegisterRequest request);
}
