using Microsoft.EntityFrameworkCore;
using TorreonFuerte.Application.DTOs;
using TorreonFuerte.Application.Interfaces;
using TorreonFuerte.Domain.Entities;
using TorreonFuerte.Infrastructure.Data;

namespace TorreonFuerte.Infrastructure.Repositories;

public class LideresService(AppDbContext db) : ILideresService
{
    public async Task<LiderNodoDto?> GetArbolAsync(int sedeId)
    {
        var lideres = await db.Lideres
            .Include(l => l.Miembro)
            .Include(l => l.MiembrosAsignados)
            .Where(l => l.SedeId == sedeId && l.Activo)
            .ToListAsync();

        var raiz = lideres.FirstOrDefault(l => l.LiderPadreId == null && l.Nivel == NivelLider.Pastor);
        if (raiz is null) return null;

        return BuildNodo(raiz, lideres);
    }

    public async Task AsignarMiembroAsync(AsignarLiderRequest request)
    {
        var miembro = await db.Miembros.FindAsync(request.MiembroId)
            ?? throw new InvalidOperationException("Miembro no encontrado.");
        miembro.LiderCelulaId = request.LiderCelulaId;
        await db.SaveChangesAsync();
    }

    private static LiderNodoDto BuildNodo(Lider lider, List<Lider> todos)
    {
        var hijos = todos.Where(l => l.LiderPadreId == lider.LiderId).ToList();
        return new LiderNodoDto(
            lider.LiderId,
            lider.MiembroId,
            lider.Miembro.Nombre,
            lider.Miembro.Apellido,
            lider.Nivel,
            lider.MiembrosAsignados.Count,
            hijos.Select(h => BuildNodo(h, todos)).ToList()
        );
    }
}
