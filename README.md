
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

# **Fecha de liberación:** 27 de mayo de 2026
## Compras-Paque vehicular
- Se agrega el calculo de distancias recorridas de las unidades integrándolo mediante al gps

---
## **Fecha de liberación:** 13 de junio de 2026
## Parque vehicular - Toka
- Se modifican los datos datos de registro para dispersiones de toka, se agrega datos de contexto como periodo, fecha de solicitud, folio de solicitud, y empresa
- Se genera una notificacion por correo a compras y la plata cada que se genera un solicitude de diesel 
## Compras - Catalogo de tarjetas Toka
- Se genera un catalogo de tarjetas de toka de las empresas 
## Compras - Dispersiones de diesel
- Se genera un nuevo sp para recupera dispersiones basada en una solicitud de dispersion
- Se genera una notification por correo cuando se realiza una dispersion de combustible
## Compras - Almacen
- Se agrupan las salidas de almacén en entregas, y se realiza foliado

---
## **Fecha de liberación:** 3 de julio de 2026
## Compras - Dispersiones de diesel
- Se integra un nuevo estado en el apartado de dispersiones de diesel para identificar con mayor facilidad, solicitudes pendientes, guardadas y realizadas ( notificadas ).
- Se modifica el SP para corregir errores de visualizacion de informacion
## Compras - Catalogo de Tags
- Se genera un catalogo de tarjetas de tags para las empresas
## UCOIP 
- Asignación de sistemas y almacenado de credenciales
- Asignación de recursos de red
- Asignación de licencias de software

---
## **Fecha de liberación:**- 17 de julio de 2026

### Parque vehicular
* **Segmentación del Parque Vehicular:** Se dividió el parque vehicular en las categorías de **Vehículos Operativos** y **Vehículos Utilitarios** para mejorar el control analítico y la asignación de recursos.
* **Actualización del Catálogo de Tags:** Se ajustó y homologó el catálogo de TAGs vehiculares tomando como base la estructura y datos del layout en Excel proporcionado por el equipo de Compras.

### Comisiones
* **Optimización de Recuperación de Ventas:** Se actualizó y corrigió el comando encargado de consultar y recuperar el flujo de datos de ventas de autos nuevos.


### UCOIP
* **Ampliación del Catálogo de Software:** Se extendió el alcance del catálogo para brindar soporte nativo al registro y seguimiento de **Hardware de Infraestructura**.
* **Módulo de Intercambios Inter-compañías:** Se implementó el registro y trazabilidad de intercambios de hardware entre las diferentes empresas del grupo.
* **Catálogo de Puestos:** Se generó e integró formalmente el catálogo de puestos específicos por Marca.
* **Control de Asignación de Compras:** Se revisó y optimizó la lógica de asignación de compras, asegurando la correcta vinculación de insumos tanto a nivel de Equipos como a nivel de Usuarios individuales.

---

### UCOIP
* **Baja de registros** Se agrega soporte par aeliminar regitros por medio de borrado logico.
* **Mantenimiento** Se integra la estrutura para llevar el registro de mantenimientos.
### RENAULT
* **Citas de servicio**  Se integra soporte soporte para carga de distintos tipos de archivo y se coloca visible el nombre de agencia
---
## **Fecha de liberación:**- 11 de agosto de 2026

### Volumtricos
* **Convertir excel a json:** Se intrega la funcionalidad de poder convertir un formato de excel a un json valido
Se agrengan nuevos valores, como un nombre de empresa legibe y fecha de periodo reportado
Se integra ctualizacion de registros y archivos ademas de borrado fisico

