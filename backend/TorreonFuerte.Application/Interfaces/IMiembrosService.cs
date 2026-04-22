using TorreonFuerte.Application.DTOs;

namespace TorreonFuerte.Application.Interfaces;

public interface IMiembrosService
{
    Task<List<MiembroListDto>> GetBySedeAsync(int sedeId, string? busqueda = null);
    Task<MiembroDetalleDto?> GetByIdAsync(int id);
    Task<MiembroDetalleDto> CreateAsync(CrearMiembroRequest request);
    Task<MiembroDetalleDto?> UpdateAsync(int id, ActualizarMiembroRequest request);
    Task<bool> DeleteAsync(int id);
}
