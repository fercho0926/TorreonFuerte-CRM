using Microsoft.EntityFrameworkCore;
using TorreonFuerte.Application.DTOs;
using TorreonFuerte.Application.Interfaces;
using TorreonFuerte.Domain.Entities;
using TorreonFuerte.Infrastructure.Data;

namespace TorreonFuerte.Infrastructure.Repositories;

public class SedesService(AppDbContext db) : ISedesService
{
    public async Task<List<SedeDto>> GetAllAsync() =>
        await db.Sedes.Select(s => new SedeDto(s.SedeId, s.Nombre, s.Ciudad, s.Pais, s.Activa, s.EsCentral)).ToListAsync();

    public async Task<SedeDto?> GetByIdAsync(int id)
    {
        var s = await db.Sedes.FindAsync(id);
        return s is null ? null : new SedeDto(s.SedeId, s.Nombre, s.Ciudad, s.Pais, s.Activa, s.EsCentral);
    }

    public async Task<SedeDto> CreateAsync(CrearSedeRequest request)
    {
        var sede = new Sede { Nombre = request.Nombre, Ciudad = request.Ciudad, Pais = request.Pais, EsCentral = request.EsCentral };
        db.Sedes.Add(sede);
        await db.SaveChangesAsync();
        return new SedeDto(sede.SedeId, sede.Nombre, sede.Ciudad, sede.Pais, sede.Activa, sede.EsCentral);
    }

    public async Task<bool> ToggleActivaAsync(int id)
    {
        var sede = await db.Sedes.FindAsync(id);
        if (sede is null) return false;
        sede.Activa = !sede.Activa;
        await db.SaveChangesAsync();
        return true;
    }
}
