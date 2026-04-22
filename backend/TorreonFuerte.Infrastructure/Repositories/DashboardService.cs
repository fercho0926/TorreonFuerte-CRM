using Microsoft.EntityFrameworkCore;
using TorreonFuerte.Application.DTOs;
using TorreonFuerte.Application.Interfaces;
using TorreonFuerte.Domain.Entities;
using TorreonFuerte.Infrastructure.Data;

namespace TorreonFuerte.Infrastructure.Repositories;

public class DashboardService(AppDbContext db) : IDashboardService
{
    public async Task<DashboardDto> GetAsync(int sedeId)
    {
        var miembros = await db.Miembros.Where(m => m.SedeId == sedeId).ToListAsync();

        var etapas = await db.MiembroEtapas
            .Include(me => me.Etapa)
            .Where(me => me.Miembro.SedeId == sedeId && !me.Completada)
            .GroupBy(me => me.Etapa.Nombre)
            .Select(g => new EtapaConteoDto(g.Key, g.Count()))
            .ToListAsync();

        var proximos = await db.Eventos
            .Where(e => e.SedeId == sedeId && e.Fecha >= DateTime.Today)
            .OrderBy(e => e.Fecha)
            .Take(5)
            .Select(e => new EventoProximoDto(e.EventoId, e.Nombre, e.Fecha, e.Tipo.ToString()))
            .ToListAsync();

        return new DashboardDto(
            miembros.Count,
            miembros.Count(m => m.Estado == EstadoMiembro.Visitante),
            miembros.Count(m => m.Estado == EstadoMiembro.Activo),
            miembros.Count(m => m.Estado == EstadoMiembro.Inactivo),
            miembros.Count(m => m.Estado == EstadoMiembro.Lider),
            etapas,
            proximos
        );
    }
}
