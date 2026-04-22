using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Design;

namespace TorreonFuerte.Infrastructure.Data;

public class DesignTimeDbContextFactory : IDesignTimeDbContextFactory<AppDbContext>
{
    public AppDbContext CreateDbContext(string[] args)
    {
        var opts = new DbContextOptionsBuilder<AppDbContext>()
            .UseSqlServer("Server=DESKTOP-HGCC3TI;Database=TorreonFuerteCRM;Trusted_Connection=True;TrustServerCertificate=True;")
            .Options;

        return new AppDbContext(opts);
    }
}
