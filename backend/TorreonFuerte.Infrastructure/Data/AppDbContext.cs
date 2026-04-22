using Microsoft.AspNetCore.Identity.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore;
using TorreonFuerte.Domain.Entities;

namespace TorreonFuerte.Infrastructure.Data;

public class AppDbContext(DbContextOptions<AppDbContext> options) : IdentityDbContext<AppUser>(options)
{
    public DbSet<Sede> Sedes => Set<Sede>();
    public DbSet<Miembro> Miembros => Set<Miembro>();
    public DbSet<Lider> Lideres => Set<Lider>();
    public DbSet<Etapa> Etapas => Set<Etapa>();
    public DbSet<MiembroEtapa> MiembroEtapas => Set<MiembroEtapa>();
    public DbSet<Evento> Eventos => Set<Evento>();
    public DbSet<EventoAsistencia> EventoAsistencias => Set<EventoAsistencia>();

    protected override void OnModelCreating(ModelBuilder builder)
    {
        base.OnModelCreating(builder);

        // ── Lider (árbol self-referencing) ──────────────────────
        builder.Entity<Lider>()
            .HasOne(l => l.LiderPadre)
            .WithMany(l => l.SubLideres)
            .HasForeignKey(l => l.LiderPadreId)
            .OnDelete(DeleteBehavior.Restrict);

        // Lider pertenece a un Miembro — Restrict para evitar ciclo
        // (Sedes→Lideres CASCADE y Lideres→Miembros formarían ciclo con Miembros→Lideres SET NULL)
        builder.Entity<Lider>()
            .HasOne(l => l.Miembro)
            .WithMany()
            .HasForeignKey(l => l.MiembroId)
            .OnDelete(DeleteBehavior.Restrict);

        builder.Entity<Lider>()
            .HasOne(l => l.Sede)
            .WithMany(s => s.Lideres)
            .HasForeignKey(l => l.SedeId)
            .OnDelete(DeleteBehavior.Restrict);

        // ── Miembro ─────────────────────────────────────────────
        builder.Entity<Miembro>()
            .HasOne(m => m.Sede)
            .WithMany(s => s.Miembros)
            .HasForeignKey(m => m.SedeId)
            .OnDelete(DeleteBehavior.Restrict);

        builder.Entity<Miembro>()
            .HasOne(m => m.LiderCelula)
            .WithMany(l => l.MiembrosAsignados)
            .HasForeignKey(m => m.LiderCelulaId)
            .OnDelete(DeleteBehavior.SetNull);

        // ── MiembroEtapa ────────────────────────────────────────
        builder.Entity<MiembroEtapa>()
            .HasOne(me => me.Miembro)
            .WithMany(m => m.Etapas)
            .HasForeignKey(me => me.MiembroId)
            .OnDelete(DeleteBehavior.Restrict);

        builder.Entity<MiembroEtapa>()
            .HasOne(me => me.Etapa)
            .WithMany(e => e.MiembroEtapas)
            .HasForeignKey(me => me.EtapaId)
            .OnDelete(DeleteBehavior.Restrict);

        // ── Evento ──────────────────────────────────────────────
        builder.Entity<Evento>()
            .HasOne(e => e.Sede)
            .WithMany(s => s.Eventos)
            .HasForeignKey(e => e.SedeId)
            .OnDelete(DeleteBehavior.Restrict);

        // ── EventoAsistencia ────────────────────────────────────
        builder.Entity<EventoAsistencia>()
            .HasOne(ea => ea.Evento)
            .WithMany(e => e.Asistencias)
            .HasForeignKey(ea => ea.EventoId)
            .OnDelete(DeleteBehavior.Restrict);

        builder.Entity<EventoAsistencia>()
            .HasOne(ea => ea.Miembro)
            .WithMany(m => m.Asistencias)
            .HasForeignKey(ea => ea.MiembroId)
            .OnDelete(DeleteBehavior.Restrict);

        builder.Entity<EventoAsistencia>()
            .HasIndex(ea => new { ea.EventoId, ea.MiembroId })
            .IsUnique();

        // ── Etapa ───────────────────────────────────────────────
        builder.Entity<Etapa>()
            .HasOne(e => e.Sede)
            .WithMany(s => s.Etapas)
            .HasForeignKey(e => e.SedeId)
            .OnDelete(DeleteBehavior.Restrict);

        // ── AppUser ─────────────────────────────────────────────
        builder.Entity<AppUser>()
            .HasOne(u => u.Sede)
            .WithMany()
            .HasForeignKey(u => u.SedeId)
            .OnDelete(DeleteBehavior.Restrict);

        // ── Conversiones enum → string ──────────────────────────
        builder.Entity<Miembro>().Property(m => m.Estado).HasConversion<string>();
        builder.Entity<Lider>().Property(l => l.Nivel).HasConversion<string>();
        builder.Entity<Evento>().Property(e => e.Tipo).HasConversion<string>();
    }
}
