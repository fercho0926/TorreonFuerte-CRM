using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using System.Text;
using Microsoft.AspNetCore.Identity;
using Microsoft.Extensions.Configuration;
using Microsoft.IdentityModel.Tokens;
using TorreonFuerte.Application.DTOs;
using TorreonFuerte.Application.Interfaces;
using TorreonFuerte.Domain.Entities;
using TorreonFuerte.Infrastructure.Data;
using Microsoft.EntityFrameworkCore;

namespace TorreonFuerte.Infrastructure.Repositories;

public class AuthService(
    UserManager<AppUser> userManager,
    AppDbContext db,
    IConfiguration config) : IAuthService
{
    public async Task<LoginResponse> LoginAsync(LoginRequest request)
    {
        var user = await userManager.FindByEmailAsync(request.Email)
            ?? throw new UnauthorizedAccessException("Credenciales inválidas.");

        if (!await userManager.CheckPasswordAsync(user, request.Password))
            throw new UnauthorizedAccessException("Credenciales inválidas.");

        var sede = await db.Sedes.FindAsync(user.SedeId)
            ?? throw new InvalidOperationException("Sede no encontrada.");

        var token = GenerateToken(user);
        return new LoginResponse(token, user.Email!, user.Nombre, user.Apellido, user.Rol, user.SedeId, sede.Nombre);
    }

    public async Task RegisterAsync(RegisterRequest request)
    {
        var user = new AppUser
        {
            UserName = request.Email,
            Email = request.Email,
            Nombre = request.Nombre,
            Apellido = request.Apellido,
            SedeId = request.SedeId,
            Rol = request.Rol
        };

        var result = await userManager.CreateAsync(user, request.Password);
        if (!result.Succeeded)
            throw new InvalidOperationException(string.Join(", ", result.Errors.Select(e => e.Description)));
    }

    private string GenerateToken(AppUser user)
    {
        var key = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(config["Jwt:Key"]!));
        var creds = new SigningCredentials(key, SecurityAlgorithms.HmacSha256);

        var claims = new[]
        {
            new Claim(ClaimTypes.NameIdentifier, user.Id),
            new Claim(ClaimTypes.Email, user.Email!),
            new Claim(ClaimTypes.Name, $"{user.Nombre} {user.Apellido}"),
            new Claim("rol", user.Rol),
            new Claim("sedeId", user.SedeId.ToString())
        };

        var token = new JwtSecurityToken(
            issuer: config["Jwt:Issuer"],
            audience: config["Jwt:Audience"],
            claims: claims,
            expires: DateTime.UtcNow.AddHours(8),
            signingCredentials: creds);

        return new JwtSecurityTokenHandler().WriteToken(token);
    }
}
