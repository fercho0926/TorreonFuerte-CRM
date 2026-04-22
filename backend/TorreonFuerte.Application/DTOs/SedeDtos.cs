namespace TorreonFuerte.Application.DTOs;

public record SedeDto(int SedeId, string Nombre, string Ciudad, string Pais, bool Activa, bool EsCentral);

public class CrearSedeRequest
{
    public string Nombre { get; set; } = string.Empty;
    public string Ciudad { get; set; } = string.Empty;
    public string Pais { get; set; } = string.Empty;
    public bool EsCentral { get; set; }
}
