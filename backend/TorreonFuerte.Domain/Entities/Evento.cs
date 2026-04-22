namespace TorreonFuerte.Domain.Entities;

public enum TipoEvento
{
    Encuentro,
    Retiro,
    Conferencia,
    Culto,
    Otro
}

public class Evento
{
    public int EventoId { get; set; }
    public int SedeId { get; set; }
    public string Nombre { get; set; } = string.Empty;
    public TipoEvento Tipo { get; set; }
    public DateTime Fecha { get; set; }
    public string? Lugar { get; set; }
    public int? CupoMaximo { get; set; }

    public Sede Sede { get; set; } = null!;
    public ICollection<EventoAsistencia> Asistencias { get; set; } = [];
}

public class EventoAsistencia
{
    public int Id { get; set; }
    public int EventoId { get; set; }
    public int MiembroId { get; set; }
    public bool Inscrito { get; set; } = true;
    public bool? Asistio { get; set; }

    public Evento Evento { get; set; } = null!;
    public Miembro Miembro { get; set; } = null!;
}
