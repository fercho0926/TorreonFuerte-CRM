namespace TorreonFuerte.Domain.Entities;

public class Sede
{
    public int SedeId { get; set; }
    public string Nombre { get; set; } = string.Empty;
    public string Ciudad { get; set; } = string.Empty;
    public string Pais { get; set; } = string.Empty;
    public bool Activa { get; set; } = true;
    public bool EsCentral { get; set; } = false;

    public ICollection<Miembro> Miembros { get; set; } = [];
    public ICollection<Lider> Lideres { get; set; } = [];
    public ICollection<Etapa> Etapas { get; set; } = [];
    public ICollection<Evento> Eventos { get; set; } = [];
}
