namespace TorreonFuerte.Domain.Entities;

public enum NivelLider
{
    Pastor = 1,
    LiderZona = 2,
    LiderCelula = 3
}

public class Lider
{
    public int LiderId { get; set; }
    public int MiembroId { get; set; }
    public int SedeId { get; set; }
    public int? LiderPadreId { get; set; }
    public NivelLider Nivel { get; set; }
    public bool Activo { get; set; } = true;

    public Miembro Miembro { get; set; } = null!;
    public Sede Sede { get; set; } = null!;
    public Lider? LiderPadre { get; set; }
    public ICollection<Lider> SubLideres { get; set; } = [];
    public ICollection<Miembro> MiembrosAsignados { get; set; } = [];
}
