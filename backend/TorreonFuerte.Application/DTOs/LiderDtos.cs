using TorreonFuerte.Domain.Entities;

namespace TorreonFuerte.Application.DTOs;

public record LiderNodoDto(
    int LiderId,
    int MiembroId,
    string Nombre,
    string Apellido,
    NivelLider Nivel,
    int TotalMiembros,
    List<LiderNodoDto> SubLideres
);

public class AsignarLiderRequest
{
    public int MiembroId { get; set; }
    public int LiderCelulaId { get; set; }
}
