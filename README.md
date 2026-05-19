
# ⚙️ Liberaciones — Backend

---

**Fecha de liberación:** 18 de abril de 2026
**Tipo:** Backend · API / Base de Datos

---

## Nuevo Módulo: Gestión de Comisiones

Se integra la lógica de negocio y endpoints para el módulo de Gestión de Comisiones con los siguientes apartados:

-  **Concentrado**
-  **Cortes**
-  **Nuevos**
-  **Financiamientos**
-  **Toma de Unidad**
-  **Seguros**
-  **Accesorios**

---

## Procedimientos Almacenados

Se agregan nuevos procedimientos almacenados para soporte de consultas del módulo de comisiones:

### Cortes
- Se agrega procedimiento almacenado para la consulta de **Cortes**.

### Concentrado
- Se agrega procedimiento almacenado para la consulta de **Concentrado**.

---

## Módulo de Compras — División de Empresas

### Modificación de Consultas
- Se modifican las consultas del módulo de Compras para soportar la división de empresas.
- Las consultas ahora filtran y segmentan la información según las empresas asignadas a usuarios específicos de compras.

---

## Comisiones — Módulo de Vendedores

Se realizan modificaciones en el apartado de Vendedores dentro del módulo de Comisiones:

### Catálogo de Tipos de Vendedor
- Se agrega catálogo para la gestión de **tipos de vendedor**.

### Catálogo de Departamentos
- Se agrega catálogo para la gestión de **departamentos** asociados a vendedores.

---

## Parque Vehicular — Dispersión de Combustibles

### Lógica de Registro
- Se agrega la lógica de negocio para el registro de dispersión de recursos de combustibles.

---
**Fecha de liberación:** 18 de abril de 2026
## Comisiones — Módulo de concentrado

Se añade soporte para agregar otros conceptos tanto de descuento como comisiones en el concentrado
Se actualiza el sp para obtener comisiones

**Fecha de liberación:** 25 de abril de 2026
## Compras — Cotizaciones
- Se integra el flujo para recotizzacion de compras cuando se encuentren en estado de orden de compra y en surtido

## Comisiones — Seguros, Financiamientos
- Se integra la capacidad de visualizar archivos previamente cargados

**Fecha de liberación:** 30 de abril de 2026
## Compras — Reportes
- Api para la descarga de reporte de concentrados de compras
- Api para la descarga de reporte de flujo de solicitudes de compras


# **Fecha de liberación:** 15 de mayo de 2026
## Compras 
- Se agregan datos a la consulta principal para informar estatus de documentos de orden de compra
## Compras — Reportes
- Api para la descarga de reported se de seguimientos de documentación de compras
## Compras- Documentos ordenes compras
- Cambios en el manejo de estatus en compras de contado
- Se añade soporte para lectura y almacenamiento de datos de facturas
