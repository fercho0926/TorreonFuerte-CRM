using TorreonFuerte.Application.DTOs;

namespace TorreonFuerte.Application.Interfaces;

public interface IDashboardService
{
    Task<DashboardDto> GetAsync(int sedeId);
}
