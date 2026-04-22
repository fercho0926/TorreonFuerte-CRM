using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using TorreonFuerte.Application.DTOs;
using TorreonFuerte.Application.Interfaces;

namespace TorreonFuerte.API.Controllers;

[ApiController]
[Route("api/lideres")]
[Authorize]
public class LideresController(ILideresService service) : ControllerBase
{
    [HttpGet("arbol")]
    public async Task<IActionResult> GetArbol([FromQuery] int sedeId)
    {
        var arbol = await service.GetArbolAsync(sedeId);
        return arbol is null ? NotFound() : Ok(arbol);
    }

    [HttpPost("asignar")]
    public async Task<IActionResult> Asignar([FromBody] AsignarLiderRequest request)
    {
        try
        {
            await service.AsignarMiembroAsync(request);
            return NoContent();
        }
        catch (InvalidOperationException ex)
        {
            return BadRequest(new { message = ex.Message });
        }
    }
}
