using Microsoft.AspNetCore.Identity;
using TorreonFuerte.Domain.Entities;

namespace TorreonFuerte.Infrastructure.Data;

public class AppUser : IdentityUser
{
    public int SedeId { get; set; }
    public string Nombre { get; set; } = string.Empty;
    public string Apellido { get; set; } = string.Empty;
    public string Rol { get; set; } = "Lider";

    public Sede Sede { get; set; } = null!;
}
