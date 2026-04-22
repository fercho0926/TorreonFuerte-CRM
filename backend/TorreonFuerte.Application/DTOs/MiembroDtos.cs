using TorreonFuerte.Domain.Entities;

namespace TorreonFuerte.Application.DTOs;

public record MiembroListDto(
    int MiembroId,
    string Nombre,
    string Apellido,
    string? Telefono,
    string? Email,
    EstadoMiembro Estado,
    DateOnly FechaIngreso,
    string? LiderNombre,
    string? EtapaActual
);

public record MiembroDetalleDto(
    int MiembroId,
    int SedeId,
    string Nombre,
    string Apellido,
    string? Cedula,
    DateOnly? FechaNacimiento,
    string? Telefono,
    string? Email,
    string? Direccion,
    EstadoMiembro Estado,
    DateOnly FechaIngreso,
    int? LiderCelulaId,
    string? LiderNombre,
    string? Notas,
    List<MiembroEtapaDto> Etapas,
    List<EventoAsistenciaDto> Asistencias
);

public record MiembroEtapaDto(
    int EtapaId,
    string EtapaNombre,
    DateTime FechaInicio,
    DateTime? FechaFin,
    bool Completada
);

public record EventoAsistenciaDto(
    int EventoId,
    string EventoNombre,
    DateTime Fecha,
    bool Inscrito,
    bool? Asistio
);

public class CrearMiembroRequest
{
    public int SedeId { get; set; }
    public string Nombre { get; set; } = string.Empty;
    public string Apellido { get; set; } = string.Empty;
    public string? Cedula { get; set; }
    public DateOnly? FechaNacimiento { get; set; }
    public string? Telefono { get; set; }
    public string? Email { get; set; }
    public string? Direccion { get; set; }
    public EstadoMiembro Estado { get; set; } = EstadoMiembro.Visitante;
    public int? LiderCelulaId { get; set; }
    public string? Notas { get; set; }
}

public class ActualizarMiembroRequest : CrearMiembroRequest { }
