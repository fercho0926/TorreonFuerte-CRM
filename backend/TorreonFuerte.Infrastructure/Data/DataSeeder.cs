using Microsoft.AspNetCore.Identity;
using Microsoft.EntityFrameworkCore;
using TorreonFuerte.Domain.Entities;

namespace TorreonFuerte.Infrastructure.Data;

public static class DataSeeder
{
    public static async Task SeedAsync(AppDbContext db, UserManager<AppUser> userManager)
    {
        await db.Database.MigrateAsync();

        if (await db.Sedes.AnyAsync()) return;

        // ── Sedes ──────────────────────────────────────────────────────────
        var sedeMed = new Sede { Nombre = "Torreón Fuerte Medellín", Ciudad = "Medellín", Pais = "Colombia", Activa = true, EsCentral = true };
        var sedeBog = new Sede { Nombre = "Torreón Fuerte Bogotá", Ciudad = "Bogotá", Pais = "Colombia", Activa = true, EsCentral = false };
        var sedeCal = new Sede { Nombre = "Torreón Fuerte Cali", Ciudad = "Cali", Pais = "Colombia", Activa = true, EsCentral = false };
        db.Sedes.AddRange(sedeMed, sedeBog, sedeCal);
        await db.SaveChangesAsync();

        // ── Admin user ─────────────────────────────────────────────────────
        var admin = new AppUser
        {
            UserName = "admin@admin.com", Email = "admin@admin.com",
            Nombre = "Super", Apellido = "Admin",
            SedeId = sedeMed.SedeId, Rol = "SuperAdmin", EmailConfirmed = true,
        };
        await userManager.CreateAsync(admin, "admin");

        // ── Etapas de preparación ──────────────────────────────────────────
        var etapas = new List<Etapa>
        {
            new() { SedeId = sedeMed.SedeId, Nombre = "Primer Paso",         Descripcion = "Clase de bienvenida e integración",          Orden = 1 },
            new() { SedeId = sedeMed.SedeId, Nombre = "Encuentro con Dios",  Descripcion = "Retiro espiritual de 3 días",                 Orden = 2 },
            new() { SedeId = sedeMed.SedeId, Nombre = "Escuela de Vida",     Descripcion = "Formación bíblica fundamental (12 semanas)", Orden = 3 },
            new() { SedeId = sedeMed.SedeId, Nombre = "Consolidación",       Descripcion = "Integración a célula y servicio",            Orden = 4 },
            new() { SedeId = sedeMed.SedeId, Nombre = "Escuela de Líderes",  Descripcion = "Formación para multiplicadores",             Orden = 5 },
        };
        db.Etapas.AddRange(etapas);
        await db.SaveChangesAsync();

        // ── Miembros ───────────────────────────────────────────────────────
        var miembros = new List<Miembro>
        {
            // Pastores / líderes de zona
            new() { SedeId = sedeMed.SedeId, Nombre = "Walter",   Apellido = "Pérez",      Cedula = "71234567", Telefono = "3001234567", Email = "walter@torreon.com",   Estado = EstadoMiembro.Lider,    FechaIngreso = new DateOnly(1991, 3, 15) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Alba",     Apellido = "Pérez",      Cedula = "42345678", Telefono = "3012345678", Email = "alba@torreon.com",     Estado = EstadoMiembro.Lider,    FechaIngreso = new DateOnly(1991, 3, 15) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Carlos",   Apellido = "Montoya",    Cedula = "98234512", Telefono = "3023456789", Email = "carlos.m@correo.com",  Estado = EstadoMiembro.Lider,    FechaIngreso = new DateOnly(2005, 6, 10) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Patricia", Apellido = "Gómez",      Cedula = "43567890", Telefono = "3034567890", Email = "patricia.g@correo.com",Estado = EstadoMiembro.Lider,    FechaIngreso = new DateOnly(2008, 2, 20) },
            // Líderes de célula
            new() { SedeId = sedeMed.SedeId, Nombre = "Andrés",   Apellido = "Herrera",    Cedula = "1020304050",Telefono="3045678901", Email = "andres.h@correo.com",  Estado = EstadoMiembro.Lider,    FechaIngreso = new DateOnly(2012, 9, 1)  },
            new() { SedeId = sedeMed.SedeId, Nombre = "Luisa",    Apellido = "Vargas",     Cedula = "1030405060",Telefono="3056789012", Email = "luisa.v@correo.com",   Estado = EstadoMiembro.Lider,    FechaIngreso = new DateOnly(2013, 4, 14) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Sebastián",Apellido = "Ríos",       Cedula = "1040506070",Telefono="3067890123", Email = "sebas.r@correo.com",   Estado = EstadoMiembro.Lider,    FechaIngreso = new DateOnly(2015, 7, 22) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Valentina",Apellido = "Castro",     Cedula = "1050607080",Telefono="3078901234", Email = "vale.c@correo.com",    Estado = EstadoMiembro.Lider,    FechaIngreso = new DateOnly(2016, 1, 5)  },
            // Miembros activos
            new() { SedeId = sedeMed.SedeId, Nombre = "Miguel",   Apellido = "Torres",     Cedula = "1060708090",Telefono="3089012345", Email = "miguel.t@correo.com",  Estado = EstadoMiembro.Activo,   FechaIngreso = new DateOnly(2018, 3, 11) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Camila",   Apellido = "Sánchez",    Cedula = "1070809010",Telefono="3090123456", Email = "camila.s@correo.com",  Estado = EstadoMiembro.Activo,   FechaIngreso = new DateOnly(2019, 8, 30) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Daniel",   Apellido = "Ospina",     Cedula = "1080910111",Telefono="3101234567", Email = "daniel.o@correo.com",  Estado = EstadoMiembro.Activo,   FechaIngreso = new DateOnly(2020, 1, 18) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Natalia",  Apellido = "Mejía",      Cedula = "1091011121",Telefono="3112345678", Email = "natalia.m@correo.com", Estado = EstadoMiembro.Activo,   FechaIngreso = new DateOnly(2020, 5, 7)  },
            new() { SedeId = sedeMed.SedeId, Nombre = "Juan",     Apellido = "Cardona",    Cedula = "1101112131",Telefono="3123456789", Email = "juan.c@correo.com",    Estado = EstadoMiembro.Activo,   FechaIngreso = new DateOnly(2021, 2, 14) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Isabella", Apellido = "Restrepo",   Cedula = "1111213141",Telefono="3134567890", Email = "isa.r@correo.com",     Estado = EstadoMiembro.Activo,   FechaIngreso = new DateOnly(2021, 9, 3)  },
            new() { SedeId = sedeMed.SedeId, Nombre = "Santiago", Apellido = "Vélez",      Cedula = "1121314151",Telefono="3145678901", Email = "santi.v@correo.com",   Estado = EstadoMiembro.Activo,   FechaIngreso = new DateOnly(2022, 6, 19) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Sofía",    Apellido = "Muñoz",      Cedula = "1131415161",Telefono="3156789012", Email = "sofia.m@correo.com",   Estado = EstadoMiembro.Activo,   FechaIngreso = new DateOnly(2022, 11, 8) },
            // Visitantes
            new() { SedeId = sedeMed.SedeId, Nombre = "Alejandro",Apellido = "Patiño",     Telefono = "3167890123",                                                    Estado = EstadoMiembro.Visitante, FechaIngreso = new DateOnly(2024, 8, 4) },
            new() { SedeId = sedeMed.SedeId, Nombre = "María",    Apellido = "Londoño",    Telefono = "3178901234",                                                    Estado = EstadoMiembro.Visitante, FechaIngreso = new DateOnly(2024, 9, 15) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Felipe",   Apellido = "Arango",     Telefono = "3189012345",                                                    Estado = EstadoMiembro.Visitante, FechaIngreso = new DateOnly(2025, 1, 22) },
            // Inactivos
            new() { SedeId = sedeMed.SedeId, Nombre = "Gabriela", Apellido = "Salazar",    Telefono = "3190123456",                                                    Estado = EstadoMiembro.Inactivo,  FechaIngreso = new DateOnly(2017, 4, 10) },
            new() { SedeId = sedeMed.SedeId, Nombre = "Ricardo",  Apellido = "Cano",       Telefono = "3201234567",                                                    Estado = EstadoMiembro.Inactivo,  FechaIngreso = new DateOnly(2016, 7, 28) },
            // Bogotá
            new() { SedeId = sedeBog.SedeId, Nombre = "Laura",    Apellido = "Morales",    Cedula = "52345678",  Telefono = "3212345678", Email = "laura.m@correo.com", Estado = EstadoMiembro.Lider,    FechaIngreso = new DateOnly(2010, 3, 5) },
            new() { SedeId = sedeBog.SedeId, Nombre = "Hernando", Apellido = "Bermúdez",   Cedula = "79234567",  Telefono = "3223456789", Email = "hernan.b@correo.com",Estado = EstadoMiembro.Activo,   FechaIngreso = new DateOnly(2019, 5, 12) },
            new() { SedeId = sedeBog.SedeId, Nombre = "Marcela",  Apellido = "Fonseca",    Cedula = "53456789",  Telefono = "3234567890", Email = "marce.f@correo.com", Estado = EstadoMiembro.Activo,   FechaIngreso = new DateOnly(2021, 8, 17) },
            // Cali
            new() { SedeId = sedeCal.SedeId, Nombre = "Jhon",     Apellido = "Caicedo",    Cedula = "94567890",  Telefono = "3245678901", Email = "jhon.c@correo.com",  Estado = EstadoMiembro.Lider,    FechaIngreso = new DateOnly(2014, 11, 3) },
            new() { SedeId = sedeCal.SedeId, Nombre = "Paola",    Apellido = "Guerrero",   Cedula = "31234567",  Telefono = "3256789012", Email = "paola.g@correo.com", Estado = EstadoMiembro.Activo,   FechaIngreso = new DateOnly(2020, 2, 25) },
        };
        db.Miembros.AddRange(miembros);
        await db.SaveChangesAsync();

        // ── Árbol de liderazgo (Medellín) ──────────────────────────────────
        // Pastor principal
        var pastor = new Lider { MiembroId = miembros[0].MiembroId, SedeId = sedeMed.SedeId, Nivel = NivelLider.Pastor, LiderPadreId = null };
        db.Lideres.Add(pastor);
        await db.SaveChangesAsync();

        // Líderes de zona (reportan al pastor)
        var zona1 = new Lider { MiembroId = miembros[2].MiembroId, SedeId = sedeMed.SedeId, Nivel = NivelLider.LiderZona, LiderPadreId = pastor.LiderId };
        var zona2 = new Lider { MiembroId = miembros[3].MiembroId, SedeId = sedeMed.SedeId, Nivel = NivelLider.LiderZona, LiderPadreId = pastor.LiderId };
        db.Lideres.AddRange(zona1, zona2);
        await db.SaveChangesAsync();

        // Líderes de célula (reportan a zona)
        var cel1 = new Lider { MiembroId = miembros[4].MiembroId, SedeId = sedeMed.SedeId, Nivel = NivelLider.LiderCelula, LiderPadreId = zona1.LiderId };
        var cel2 = new Lider { MiembroId = miembros[5].MiembroId, SedeId = sedeMed.SedeId, Nivel = NivelLider.LiderCelula, LiderPadreId = zona1.LiderId };
        var cel3 = new Lider { MiembroId = miembros[6].MiembroId, SedeId = sedeMed.SedeId, Nivel = NivelLider.LiderCelula, LiderPadreId = zona2.LiderId };
        var cel4 = new Lider { MiembroId = miembros[7].MiembroId, SedeId = sedeMed.SedeId, Nivel = NivelLider.LiderCelula, LiderPadreId = zona2.LiderId };
        db.Lideres.AddRange(cel1, cel2, cel3, cel4);
        await db.SaveChangesAsync();

        // Asignar miembros a células
        miembros[8].LiderCelulaId  = cel1.LiderId; miembros[9].LiderCelulaId  = cel1.LiderId;
        miembros[10].LiderCelulaId = cel2.LiderId; miembros[11].LiderCelulaId = cel2.LiderId;
        miembros[12].LiderCelulaId = cel3.LiderId; miembros[13].LiderCelulaId = cel3.LiderId;
        miembros[14].LiderCelulaId = cel4.LiderId; miembros[15].LiderCelulaId = cel4.LiderId;
        await db.SaveChangesAsync();

        // Pastor Bogotá
        var pastorBog = new Lider { MiembroId = miembros[21].MiembroId, SedeId = sedeBog.SedeId, Nivel = NivelLider.Pastor };
        // Pastor Cali
        var pastorCal = new Lider { MiembroId = miembros[24].MiembroId, SedeId = sedeCal.SedeId, Nivel = NivelLider.Pastor };
        db.Lideres.AddRange(pastorBog, pastorCal);
        await db.SaveChangesAsync();

        // ── Avances en etapas ──────────────────────────────────────────────
        var hoy = DateTime.Now;
        var miembroEtapas = new List<MiembroEtapa>
        {
            // Miguel (idx 8) — completó etapas 1 y 2
            new() { MiembroId = miembros[8].MiembroId,  EtapaId = etapas[0].EtapaId, FechaInicio = hoy.AddMonths(-10), FechaFin = hoy.AddMonths(-9),  Completada = true  },
            new() { MiembroId = miembros[8].MiembroId,  EtapaId = etapas[1].EtapaId, FechaInicio = hoy.AddMonths(-8),  FechaFin = hoy.AddMonths(-7),  Completada = true  },
            new() { MiembroId = miembros[8].MiembroId,  EtapaId = etapas[2].EtapaId, FechaInicio = hoy.AddMonths(-6),  FechaFin = null,               Completada = false },
            // Camila (idx 9) — completó etapas 1,2,3
            new() { MiembroId = miembros[9].MiembroId,  EtapaId = etapas[0].EtapaId, FechaInicio = hoy.AddMonths(-14), FechaFin = hoy.AddMonths(-13), Completada = true  },
            new() { MiembroId = miembros[9].MiembroId,  EtapaId = etapas[1].EtapaId, FechaInicio = hoy.AddMonths(-12), FechaFin = hoy.AddMonths(-11), Completada = true  },
            new() { MiembroId = miembros[9].MiembroId,  EtapaId = etapas[2].EtapaId, FechaInicio = hoy.AddMonths(-10), FechaFin = hoy.AddMonths(-7),  Completada = true  },
            // Daniel (idx 10) — solo etapa 1
            new() { MiembroId = miembros[10].MiembroId, EtapaId = etapas[0].EtapaId, FechaInicio = hoy.AddMonths(-4),  FechaFin = hoy.AddMonths(-3),  Completada = true  },
            // Natalia (idx 11) — etapa 1 en curso
            new() { MiembroId = miembros[11].MiembroId, EtapaId = etapas[0].EtapaId, FechaInicio = hoy.AddMonths(-2),  FechaFin = null,               Completada = false },
            // Juan (idx 12) — completó todas
            new() { MiembroId = miembros[12].MiembroId, EtapaId = etapas[0].EtapaId, FechaInicio = hoy.AddMonths(-24), FechaFin = hoy.AddMonths(-23), Completada = true  },
            new() { MiembroId = miembros[12].MiembroId, EtapaId = etapas[1].EtapaId, FechaInicio = hoy.AddMonths(-22), FechaFin = hoy.AddMonths(-20), Completada = true  },
            new() { MiembroId = miembros[12].MiembroId, EtapaId = etapas[2].EtapaId, FechaInicio = hoy.AddMonths(-18), FechaFin = hoy.AddMonths(-14), Completada = true  },
            new() { MiembroId = miembros[12].MiembroId, EtapaId = etapas[3].EtapaId, FechaInicio = hoy.AddMonths(-12), FechaFin = hoy.AddMonths(-8),  Completada = true  },
            new() { MiembroId = miembros[12].MiembroId, EtapaId = etapas[4].EtapaId, FechaInicio = hoy.AddMonths(-6),  FechaFin = hoy.AddMonths(-2),  Completada = true  },
        };
        db.MiembroEtapas.AddRange(miembroEtapas);
        await db.SaveChangesAsync();

        // ── Eventos ────────────────────────────────────────────────────────
        var eventos = new List<Evento>
        {
            new() { SedeId = sedeMed.SedeId, Nombre = "Encuentro con Dios — Enero 2025",   Tipo = TipoEvento.Encuentro,   Fecha = hoy.AddMonths(-3),  Lugar = "Centro de Convenciones Medellín", CupoMaximo = 80  },
            new() { SedeId = sedeMed.SedeId, Nombre = "Retiro Familiar 2025",              Tipo = TipoEvento.Retiro,      Fecha = hoy.AddMonths(-2),  Lugar = "Finca Villa de Paz, Envigado",    CupoMaximo = 50  },
            new() { SedeId = sedeMed.SedeId, Nombre = "Conferencia Profética 2025",        Tipo = TipoEvento.Conferencia, Fecha = hoy.AddMonths(-1),  Lugar = "Iglesia Central",                 CupoMaximo = 200 },
            new() { SedeId = sedeMed.SedeId, Nombre = "Culto de Celebración — Abril",     Tipo = TipoEvento.Culto,       Fecha = hoy.AddDays(-7),    Lugar = "Auditorio Principal",             CupoMaximo = 300 },
            new() { SedeId = sedeMed.SedeId, Nombre = "Encuentro con Dios — Mayo 2025",   Tipo = TipoEvento.Encuentro,   Fecha = hoy.AddDays(15),    Lugar = "Centro de Convenciones Medellín", CupoMaximo = 80  },
            new() { SedeId = sedeMed.SedeId, Nombre = "Retiro de Líderes 2025",           Tipo = TipoEvento.Retiro,      Fecha = hoy.AddDays(30),    Lugar = "Hotel Dann Carlton, Medellín",    CupoMaximo = 40  },
            new() { SedeId = sedeBog.SedeId, Nombre = "Culto de Apertura Bogotá",         Tipo = TipoEvento.Culto,       Fecha = hoy.AddMonths(-1),  Lugar = "Iglesia Bogotá Norte",            CupoMaximo = 150 },
        };
        db.Eventos.AddRange(eventos);
        await db.SaveChangesAsync();

        // ── Asistencias ────────────────────────────────────────────────────
        var miembrosMed = miembros.Where(m => m.SedeId == sedeMed.SedeId).ToList();
        var asistencias = new List<EventoAsistencia>();
        var rnd = new Random(42);

        // Eventos pasados: asistencia aleatoria
        foreach (var evento in eventos.Where(e => e.Fecha < hoy && e.SedeId == sedeMed.SedeId))
        {
            foreach (var m in miembrosMed.Where(m => m.Estado != EstadoMiembro.Visitante))
            {
                if (rnd.NextDouble() < 0.75)
                    asistencias.Add(new EventoAsistencia { EventoId = evento.EventoId, MiembroId = m.MiembroId, Inscrito = true, Asistio = rnd.NextDouble() < 0.85 });
            }
        }

        // Eventos futuros: solo inscripciones
        foreach (var evento in eventos.Where(e => e.Fecha >= hoy && e.SedeId == sedeMed.SedeId))
        {
            foreach (var m in miembrosMed.Where(m => m.Estado == EstadoMiembro.Activo || m.Estado == EstadoMiembro.Lider))
            {
                if (rnd.NextDouble() < 0.60)
                    asistencias.Add(new EventoAsistencia { EventoId = evento.EventoId, MiembroId = m.MiembroId, Inscrito = true, Asistio = null });
            }
        }

        db.EventoAsistencias.AddRange(asistencias);
        await db.SaveChangesAsync();
    }
}
