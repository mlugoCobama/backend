Analiza el PDF de una Constancia de Situación Fiscal emitida por el SAT de México.

Extrae únicamente información que aparezca explícitamente en el documento.

NO inventes, completes, corrijas ni infieras información.
Si un campo no aparece, está vacío o no puede leerse correctamente, devuelve null.
Si el documento contiene varias páginas, analiza todas las páginas antes de generar la respuesta.

Devuelve ÚNICAMENTE un JSON válido.
No agregues explicaciones, comentarios ni Markdown.

Estructura:

{
"tipo_documento": "Constancia de Situación Fiscal",
"lugar_fecha_emision": null,

"identificacion_fiscal": {
"rfc": null,
"curp": null,
"nombre_razon_social": null,
"regimen_capital": null,
"nombre_comercial": null,
"fecha_inicio_operaciones": null,
"estatus_padron": null,
"fecha_ultimo_cambio_estado": null
},

"domicilio_fiscal": {
"codigo_postal": null,
"tipo_vialidad": null,
"nombre_vialidad": null,
"numero_exterior": null,
"numero_interior": null,
"nombre_colonia": null,
"nombre_localidad": null,
"nombre_municipio": null,
"nombre_entidad_federativa": null,
"entre_calle": null,
"y_calle": null
},

"actividades_economicas": [],

"regimenes_fiscales": [],

"obligaciones_fiscales": [],

"representante_legal": {
"rfc": null,
"curp": null,
"nombre": null
},

"cif": {
"id_cif": null
},

"control_documento": {
"codigo_qr": null,
"codigo_barras": null
}
}

REGLAS DE EXTRACCIÓN:

1. RFC:
Extrae el RFC exactamente como aparece.
Conserva letras y números.
No agregues espacios ni guiones.

2. CURP:
Extrae la CURP exactamente como aparece.
No la construyas a partir del nombre o fecha de nacimiento.

3. NOMBRE O RAZÓN SOCIAL:
Extrae exactamente el texto mostrado en el documento.
No cambies mayúsculas, palabras ni caracteres.

4. DOMICILIO:
Extrae cada componente en su campo correspondiente.
No mezcles código postal, municipio, localidad y entidad federativa.

5. ACTIVIDADES ECONÓMICAS:
Si aparecen varias actividades, devuelve un arreglo.
Cada elemento debe conservar la información correspondiente.

6. REGÍMENES FISCALES:
Si existen varios, devuelve todos.
Para cada régimen conserva:
- régimen
- fecha_inicio
- fecha_fin

7. OBLIGACIONES FISCALES:
Si existen varias obligaciones, devuelve todas.
Para cada obligación conserva:
- descripción
- descripción_vencimiento
- fecha_inicio
- fecha_fin

8. REPRESENTANTE LEGAL:
Extráelo solamente si aparece explícitamente.
Si no aparece, utiliza null.

9. FECHAS:
Devuelve las fechas en formato YYYY-MM-DD cuando sea posible.
Si una fecha no puede determinarse correctamente, devuelve null.

10. CÓDIGOS:
Conserva ceros iniciales.
No modifiques RFC, CURP, códigos postales, claves o números.

11. TABLAS:
Respeta la relación entre encabezados y valores.
No mezcles datos de diferentes filas.

12. INFORMACIÓN AUSENTE:
Si un campo no aparece en el documento, devuelve null.
Para listas sin información devuelve [].

13. VALIDACIÓN:
Antes de responder verifica que:
- el RFC tenga una estructura plausible;
- el código postal tenga 5 dígitos cuando exista;
- las fechas sean válidas;
- no hayas confundido RFC con CURP;
- no hayas confundido régimen fiscal con actividad económica;
- no hayas mezclado información entre páginas.

RESPONDE ÚNICAMENTE CON EL JSON.
