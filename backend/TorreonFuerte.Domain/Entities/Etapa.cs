namespace TorreonFuerte.Domain.Entities;

public class Etapa
{
    public int EtapaId { get; set; }
    public int SedeId { get; set; }
    public string Nombre { get; set; } = string.Empty;
    public string? Descripcion { get; set; }
    public int Orden { get; set; }

    public Sede Sede { get; set; } = null!;
    public ICollection<MiembroEtapa> MiembroEtapas { get; set; } = [];
}

public class MiembroEtapa
{
    public int Id { get; set; }
    public int MiembroId { get; set; }
    public int EtapaId { get; set; }
    public DateTime FechaInicio { get; set; }
    public DateTime? FechaFin { get; set; }
    public bool Completada { get; set; } = false;

    public Miembro Miembro { get; set; } = null!;
    public Etapa Etapa { get; set; } = null!;
}
