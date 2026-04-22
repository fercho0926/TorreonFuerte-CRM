using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using TorreonFuerte.Application.DTOs;
using TorreonFuerte.Application.Interfaces;

namespace TorreonFuerte.API.Controllers;

[ApiController]
[Route("api/miembros")]
[Authorize]
public class MiembrosController(IMiembrosService service) : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> GetAll([FromQuery] int sedeId, [FromQuery] string? busqueda) =>
        Ok(await service.GetBySedeAsync(sedeId, busqueda));

    [HttpGet("{id}")]
    public async Task<IActionResult> GetById(int id)
    {
        var miembro = await service.GetByIdAsync(id);
        return miembro is null ? NotFound() : Ok(miembro);
    }

    [HttpGet("{id}/historial")]
    public async Task<IActionResult> GetHistorial(int id)
    {
        var miembro = await service.GetByIdAsync(id);
        if (miembro is null) return NotFound();
        return Ok(new { miembro.Etapas, miembro.Asistencias });
    }

    [HttpPost]
    public async Task<IActionResult> Create([FromBody] CrearMiembroRequest request)
    {
        var miembro = await service.CreateAsync(request);
        return CreatedAtAction(nameof(GetById), new { id = miembro.MiembroId }, miembro);
    }

    [HttpPut("{id}")]
    public async Task<IActionResult> Update(int id, [FromBody] ActualizarMiembroRequest request)
    {
        var miembro = await service.UpdateAsync(id, request);
        return miembro is null ? NotFound() : Ok(miembro);
    }

    [HttpDelete("{id}")]
    public async Task<IActionResult> Delete(int id)
    {
        var ok = await service.DeleteAsync(id);
        return ok ? NoContent() : NotFound();
    }
}
