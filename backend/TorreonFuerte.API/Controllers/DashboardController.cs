using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using TorreonFuerte.Application.Interfaces;

namespace TorreonFuerte.API.Controllers;

[ApiController]
[Route("api/dashboard")]
[Authorize]
public class DashboardController(IDashboardService service) : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> Get([FromQuery] int sedeId) =>
        Ok(await service.GetAsync(sedeId));
}
