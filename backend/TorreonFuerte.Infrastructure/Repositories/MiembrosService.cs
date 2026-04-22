using Microsoft.EntityFrameworkCore;
using TorreonFuerte.Application.DTOs;
using TorreonFuerte.Application.Interfaces;
using TorreonFuerte.Domain.Entities;
using TorreonFuerte.Infrastructure.Data;

namespace TorreonFuerte.Infrastructure.Repositories;

public class MiembrosService(AppDbContext db) : IMiembrosService
{
    public async Task<List<MiembroListDto>> GetBySedeAsync(int sedeId, string? busqueda = null)
    {
        var query = db.Miembros
            .Include(m => m.LiderCelula).ThenInclude(l => l!.Miembro)
            .Include(m => m.Etapas).ThenInclude(e => e.Etapa)
            .Where(m => m.SedeId == sedeId);

        if (!string.IsNullOrWhiteSpace(busqueda))
            query = query.Where(m =>
                m.Nombre.Contains(busqueda) ||
                m.Apellido.Contains(busqueda) ||
                (m.Cedula != null && m.Cedula.Contains(busqueda)));

        return await query.Select(m => new MiembroListDto(
            m.MiembroId,
            m.Nombre,
            m.Apellido,
            m.Telefono,
            m.Email,
            m.Estado,
            m.FechaIngreso,
            m.LiderCelula != null ? $"{m.LiderCelula.Miembro.Nombre} {m.LiderCelula.Miembro.Apellido}" : null,
            m.Etapas.Where(e => !e.Completada).OrderByDescending(e => e.FechaInicio).Select(e => e.Etapa.Nombre).FirstOrDefault()
        )).ToListAsync();
    }

    public async Task<MiembroDetalleDto?> GetByIdAsync(int id)
    {
        var m = await db.Miembros
            .Include(m => m.LiderCelula).ThenInclude(l => l!.Miembro)
            .Include(m => m.Etapas).ThenInclude(e => e.Etapa)
            .Include(m => m.Asistencias).ThenInclude(a => a.Evento)
            .FirstOrDefaultAsync(m => m.MiembroId == id);

        if (m is null) return null;
        return MapDetalle(m);
    }

    public async Task<MiembroDetalleDto> CreateAsync(CrearMiembroRequest req)
    {
        var miembro = new Miembro
        {
            SedeId = req.SedeId,
            Nombre = req.Nombre,
            Apellido = req.Apellido,
            Cedula = req.Cedula,
            FechaNacimiento = req.FechaNacimiento,
            Telefono = req.Telefono,
            Email = req.Email,
            Direccion = req.Direccion,
            Estado = req.Estado,
            LiderCelulaId = req.LiderCelulaId,
            Notas = req.Notas
        };
        db.Miembros.Add(miembro);
        await db.SaveChangesAsync();
        return (await GetByIdAsync(miembro.MiembroId))!;
    }

    public async Task<MiembroDetalleDto?> UpdateAsync(int id, ActualizarMiembroRequest req)
    {
        var miembro = await db.Miembros.FindAsync(id);
        if (miembro is null) return null;

        miembro.Nombre = req.Nombre;
        miembro.Apellido = req.Apellido;
        miembro.Cedula = req.Cedula;
        miembro.FechaNacimiento = req.FechaNacimiento;
        miembro.Telefono = req.Telefono;
        miembro.Email = req.Email;
        miembro.Direccion = req.Direccion;
        miembro.Estado = req.Estado;
        miembro.LiderCelulaId = req.LiderCelulaId;
        miembro.Notas = req.Notas;

        await db.SaveChangesAsync();
        return await GetByIdAsync(id);
    }

    public async Task<bool> DeleteAsync(int id)
    {
        var miembro = await db.Miembros.FindAsync(id);
        if (miembro is null) return false;
        db.Miembros.Remove(miembro);
        await db.SaveChangesAsync();
        return true;
    }

    private static MiembroDetalleDto MapDetalle(Miembro m) => new(
        m.MiembroId, m.SedeId, m.Nombre, m.Apellido, m.Cedula,
        m.FechaNacimiento, m.Telefono, m.Email, m.Direccion, m.Estado,
        m.FechaIngreso, m.LiderCelulaId,
        m.LiderCelula != null ? $"{m.LiderCelula.Miembro.Nombre} {m.LiderCelula.Miembro.Apellido}" : null,
        m.Notas,
        m.Etapas.Select(e => new MiembroEtapaDto(e.EtapaId, e.Etapa.Nombre, e.FechaInicio, e.FechaFin, e.Completada)).ToList(),
        m.Asistencias.Select(a => new EventoAsistenciaDto(a.EventoId, a.Evento.Nombre, a.Evento.Fecha, a.Inscrito, a.Asistio)).ToList()
    );
}
