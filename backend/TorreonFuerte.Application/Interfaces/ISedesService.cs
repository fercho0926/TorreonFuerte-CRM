using TorreonFuerte.Application.DTOs;

namespace TorreonFuerte.Application.Interfaces;

public interface ISedesService
{
    Task<List<SedeDto>> GetAllAsync();
    Task<SedeDto?> GetByIdAsync(int id);
    Task<SedeDto> CreateAsync(CrearSedeRequest request);
    Task<bool> ToggleActivaAsync(int id);
}
