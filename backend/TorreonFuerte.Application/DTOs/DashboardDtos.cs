namespace TorreonFuerte.Application.DTOs;

public record DashboardDto(
    int TotalMiembros,
    int TotalVisitantes,
    int TotalActivos,
    int TotalInactivos,
    int TotalLideres,
    List<EtapaConteoDto> MiembrosPorEtapa,
    List<EventoProximoDto> ProximosEventos
);

public record EtapaConteoDto(string Etapa, int Conteo);
public record EventoProximoDto(int EventoId, string Nombre, DateTime Fecha, string Tipo);
