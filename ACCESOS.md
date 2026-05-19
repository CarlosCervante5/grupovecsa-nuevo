# Documento de Accesos — Grupo VECSA

## Git: deploy sandbox (backend + frontend)

En el monorepo, después de hacer `git commit` en la raíz, los pushes de código a los repos `**vecsa-frontend**` y `**vecsa-backend**` deben ir **siempre** a las ramas sandbox (`sandboxRailwayGrupoVecsaFrontend` y `sandboxRailwayGrupoVecsaBackend`). Detalle, comandos y política: `[.kiro/steering/deploy-branches.md](.kiro/steering/deploy-branches.md)`.

**No publicar sandbox con `git push origin main`.** El despliegue a Railway sandbox es solo vía subtrees al remoto `deploy` / `backend-deploy` (ramas anteriores), no empujando el monorepo a `origin/main`.

Atajo para publicar ambos subtrees a sandbox:

```bash
./tools/push-sandbox-subtrees.sh
```

En Railway, el backend sandbox ejecuta `deploy.sh` en cada release (`migrate` + seeders idempotentes con usuarios de prueba). Si el login falla con 401, revisa que el release terminó bien y usa las credenciales de la tabla siguiente.

Login único en la app: `/auth/iniciar-sesion` (la ruta `/auth/login` abre la misma pantalla).

## Usuarios de Prueba


| Email                                                   | Contraseña       | Rol                       | Panel                            |
| ------------------------------------------------------- | ---------------- | ------------------------- | -------------------------------- |
| [dev@vecsa.com](mailto:dev@vecsa.com)                   | Developer%2024%% | developer                 | /admin/developer                 |
| [admin@vecsa.com](mailto:admin@vecsa.com)               | TestUser%2024%%  | administrator             | /admin/administrator             |
| [marketing@vecsa.com](mailto:marketing@vecsa.com)       | TestUser%2024%%  | marketing                 | /admin/marketing                 |
| [staff@vecsa.com](mailto:staff@vecsa.com)               | TestUser%2024%%  | staff                     | /admin/staff                     |
| [gestor@vecsa.com](mailto:gestor@vecsa.com)             | TestUser%2024%%  | gestor                    | /admin/gestor                    |
| [receptionist@vecsa.com](mailto:receptionist@vecsa.com) | TestUser%2024%%  | receptionist              | /admin/receptionist              |
| [valuator@vecsa.com](mailto:valuator@vecsa.com)         | TestUser%2024%%  | valuator                  | /admin/valuator                  |
| [appointments@vecsa.com](mailto:appointments@vecsa.com) | TestUser%2024%%  | appointment_manager       | /admin/appointment_manager       |
| [bodywork@vecsa.com](mailto:bodywork@vecsa.com)         | TestUser%2024%%  | bodywork_paint_technician | /admin/bodywork_paint_technician |
| [parts@vecsa.com](mailto:parts@vecsa.com)               | TestUser%2024%%  | spare_parts               | /admin/spare_parts               |
| [gerente@vecsa.com](mailto:gerente@vecsa.com)           | TestUser%2024%%  | gerente                   | /admin/gerente                   |
| [client@vecsa.com](mailto:client@vecsa.com)             | TestUser%2024%%  | client                    | /auth/mi-cuenta                  |


## Roles y Permisos


| Rol           | Permisos                                                                                                                                                                                                                                                                                                                       |
| ------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| developer     | list users, create users, update users, delete users, access benchmark, access store_management, access marketing, access administrator, access developer, access staff, access gestor, access valuator, access receptionist, access appointment_manager, access bodywork_paint_technician, access spare_parts, access gerente |
| administrator | list users, create users, update users, delete users, access benchmark, access store_management, access marketing, access administrator, access developer, access staff, access gestor, access valuator, access receptionist, access appointment_manager, access bodywork_paint_technician, access spare_parts, access gerente |
| staff         | —                                                                                                                                                                                                                                                                                                                              |
| marketing     | access benchmark                                                                                                                                                                                                                                                                                                               |
| gestor        | access store_management, access benchmark                                                                                                                                                                                                                                                                                      |
| receptionist  | —                                                                                                                                                                                                                                                                                                                              |
| valuator      | —                                                                                                                                                                                                                                                                                                                              |
| spare_parts   | —                                                                                                                                                                                                                                                                                                                              |
| gerente       | access gerente, access gestor, access receptionist, access valuator, access appointment_manager, access staff, access bodywork_paint_technician, access spare_parts, access store_management, access benchmark, access marketing                                                                                               |
| client        | —                                                                                                                                                                                                                                                                                                                              |


## Vistas Administrativas


| Ruta                             | Nombre              | Guard                | Permiso requerido                |
| -------------------------------- | ------------------- | -------------------- | -------------------------------- |
| /admin/developer                 | Panel Developer     | DeveloperGuard       | Rol: developer                   |
| /admin/administrator             | Panel Administrador | AdministradorGuard   | Rol: administrator               |
| /admin/marketing                 | Panel Marketing     | MarketingGuard       | Rol: marketing                   |
| /admin/staff                     | Panel Staff         | StaffGuard           | Rol: staff                       |
| /admin/gestor                    | Panel Gestor        | GestorGuard          | Rol: gestor                      |
| /admin/receptionist              | Panel Recepcionista | —                    | Rol: receptionist                |
| /admin/valuator                  | Panel Valuador      | —                    | Rol: valuator                    |
| /admin/appointment_manager       | Panel Citas         | —                    | Rol: appointment_manager         |
| /admin/bodywork_paint_technician | Panel Hojalatería   | —                    | Rol: bodywork_paint_technician   |
| /admin/spare_parts               | Panel Refacciones   | —                    | Rol: spare_parts                 |
| /admin/gerente                   | Panel Gerente       | GerenteGuard         | Rol: gerente                     |
| /admin/benchmark                 | Benchmark ADS       | BenchmarkGuard       | Permiso: access benchmark        |
| /admin/store                     | Panel Tienda        | StoreManagementGuard | Permiso: access store_management |


## Vistas Públicas


| Ruta                    | Nombre                        |
| ----------------------- | ----------------------------- |
| /                       | Home                          |
| /compra-tu-auto         | Vehículos                     |
| /boutique               | Boutique (catálogo)           |
| /boutique/shop          | Boutique (tienda con filtros) |
| /boutique/product/:uuid | Detalle de producto           |
| /boutique/cart          | Carrito                       |
| /boutique/checkout      | Checkout                      |
| /rewards                | Rewards                       |
| /experience             | Experience                    |
| /carcare                | Car Care                      |
| /promociones            | Promociones                   |
| /auth/iniciar-sesion    | Login (único; `/auth/login` es alias) |
| /auth/registro          | Registro                      |
| /auth/mi-cuenta         | Perfil cliente                |


## Vistas del Panel Developer

El panel developer (/admin/developer) incluye CRUDs para:

- Usuarios (con filtro por rol y sucursal)
- Productos Boutique
- Categorías Boutique
- Pedidos Boutique
- Marcas de vehículos
- Vehículos
- Roles
- Permisos
- Sucursales
- Clientes
- Rewards
- Home Slides
- Testimonios
- Valuaciones
- Citas
- Refacciones (Valuación)

Herramientas:

- Matriz Rol-Permisos (asignar permisos a roles)
- Benchmark ADS (enlace a /admin/benchmark)

## Vistas del Panel Tienda

El panel tienda (/admin/store) incluye:

- Dashboard (métricas, gráficas de pedidos)
- Pedidos (lista, detalle, cambio de estado, guía de envío)
- Envíos (tracking, carriers)
- Clientes (perfil, historial, puntos, cupones)
- Puntos (balance, ajuste manual)
- Cupones (CRUD completo)
- Redenciones (aprobar/rechazar)

## API Endpoints

### Autenticación

- POST /api/auth/login
- POST /api/auth/register
- POST /api/auth/logout

### Store Management (requiere: auth + access store_management)

- POST /api/store-management/metrics
- POST /api/store-management/orders/search
- POST /api/store-management/orders/detail
- POST /api/store-management/orders/update_status
- POST /api/store-management/orders/generate_label
- POST /api/store-management/shipments/search
- POST /api/store-management/customers/search
- POST /api/store-management/customers/detail
- POST /api/store-management/customers/orders
- POST /api/store-management/points/search
- POST /api/store-management/points/adjust
- POST /api/store-management/points/customer_balance
- POST /api/store-management/coupons/search
- POST /api/store-management/coupons/store
- POST /api/store-management/coupons/update
- POST /api/store-management/coupons/delete
- POST /api/store-management/redemptions/search
- POST /api/store-management/redemptions/update_status

### Benchmark ADS (requiere: auth + access benchmark)

- POST /api/benchmark/scan
- GET /api/benchmark/competitors
- GET /api/benchmark/history
- GET /api/benchmark/reports

### Asistente Virtual (público)

- POST /api/assistant/chat

## Notas

- Los permisos se gestionan desde la Matriz Rol-Permisos en el panel developer
- Los usuarios necesitan re-loguearse después de cambiar permisos para que se actualicen en localStorage
- El panel developer y el panel tienda ocultan el nav global (hideChrome)
- El asistente virtual (chat flotante) aparece en todas las vistas públicas

