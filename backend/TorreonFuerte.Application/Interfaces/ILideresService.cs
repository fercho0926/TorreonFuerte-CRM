using TorreonFuerte.Application.DTOs;

namespace TorreonFuerte.Application.Interfaces;

public interface ILideresService
{
    Task<LiderNodoDto?> GetArbolAsync(int sedeId);
    Task AsignarMiembroAsync(AsignarLiderRequest request);
}
