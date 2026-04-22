using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using TorreonFuerte.Application.DTOs;
using TorreonFuerte.Application.Interfaces;

namespace TorreonFuerte.API.Controllers;

[ApiController]
[Route("api/sedes")]
[Authorize]
public class SedesController(ISedesService service) : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> GetAll() => Ok(await service.GetAllAsync());

    [HttpGet("{id}")]
    public async Task<IActionResult> GetById(int id)
    {
        var sede = await service.GetByIdAsync(id);
        return sede is null ? NotFound() : Ok(sede);
    }

    [HttpPost]
    public async Task<IActionResult> Create([FromBody] CrearSedeRequest request)
    {
        var sede = await service.CreateAsync(request);
        return CreatedAtAction(nameof(GetById), new { id = sede.SedeId }, sede);
    }

    [HttpPatch("{id}/toggle")]
    public async Task<IActionResult> Toggle(int id)
    {
        var ok = await service.ToggleActivaAsync(id);
        return ok ? NoContent() : NotFound();
    }
}
