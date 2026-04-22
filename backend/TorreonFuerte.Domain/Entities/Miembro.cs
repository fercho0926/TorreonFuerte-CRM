namespace TorreonFuerte.Domain.Entities;

public enum EstadoMiembro
{
    Visitante,
    Activo,
    Inactivo,
    Lider
}

public class Miembro
{
    public int MiembroId { get; set; }
    public int SedeId { get; set; }
    public string Nombre { get; set; } = string.Empty;
    public string Apellido { get; set; } = string.Empty;
    public string? Cedula { get; set; }
    public DateOnly? FechaNacimiento { get; set; }
    public string? Telefono { get; set; }
    public string? Email { get; set; }
    public string? Direccion { get; set; }
    public EstadoMiembro Estado { get; set; } = EstadoMiembro.Visitante;
    public DateOnly FechaIngreso { get; set; } = DateOnly.FromDateTime(DateTime.Today);
    public int? LiderCelulaId { get; set; }
    public string? Notas { get; set; }

    public Sede Sede { get; set; } = null!;
    public Lider? LiderCelula { get; set; }
    public ICollection<MiembroEtapa> Etapas { get; set; } = [];
    public ICollection<EventoAsistencia> Asistencias { get; set; } = [];
}
