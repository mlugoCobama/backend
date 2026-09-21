Analiza el documento PDF proporcionado. El documento corresponde presuntamente a una constancia o documento oficial de CURP emitido por el Registro Nacional de Población (RENAPO) de México.

Tu objetivo es extraer exclusivamente la información que pueda identificarse de manera clara en el documento.

### 1. Validación del documento

Antes de extraer los datos, determina si el documento contiene características compatibles con una constancia oficial de CURP de RENAPO.

Analiza, cuando estén disponibles:

* Logotipo o referencias a Gobierno de México.
* Referencias a Secretaría de Gobernación.
* Referencias a RENAPO o Registro Nacional de Población.
* Título o texto relacionado con "Clave Única de Registro de Población".
* CURP.
* Nombre de la persona.
* Fecha de nacimiento.
* Sexo.
* Entidad federativa de nacimiento.
* Código QR, código de barras u otros elementos de validación.
* Leyendas o textos oficiales.
* Elementos de formato característicos del documento.

No determines que un documento es auténtico únicamente por su apariencia. La apariencia solamente permite determinar si es "compatible" con el formato esperado.

### 2. Datos que debes extraer

Extrae los siguientes campos:

{
"tipo_documento": "",
"documento_compatible_renapo": false,
"curp": "",
"nombre": "",
"primer_apellido": "",
"segundo_apellido": "",
"fecha_nacimiento": "",
"sexo": "",
"entidad_nacimiento": "",
"nacionalidad": "",
"codigo_qr_presente": false,
"codigo_barras_presente": false,
"fecha_emision": "",
"observaciones": []
}

### 3. Reglas de extracción

1. No inventes información.
2. No completes datos utilizando conocimiento externo.
3. Si un dato no aparece claramente en el documento, utiliza `null`.
4. Conserva la CURP exactamente como aparece, pero elimina espacios, saltos de línea y caracteres ajenos a la CURP.
5. La CURP normalmente tiene 18 caracteres. Si la cadena encontrada no tiene 18 caracteres, no la corrijas automáticamente. Devuelve el valor observado y registra la inconsistencia en `observaciones`.
6. Respeta los nombres y apellidos exactamente como aparecen.
7. Convierte la fecha de nacimiento al formato `YYYY-MM-DD` únicamente si la fecha puede determinarse con certeza.
8. Si la fecha no puede determinarse con certeza, devuelve `null`.
9. No confundas la fecha de nacimiento con la fecha de emisión.
10. Si existen varias cadenas que podrían ser una CURP, identifica cuál corresponde al campo CURP del documento y registra cualquier ambigüedad.
11. Si el PDF contiene texto ilegible, incompleto o parcialmente oculto, no intentes reconstruirlo.
12. Si el documento es una imagen escaneada, utiliza OCR/visión para analizarlo visualmente.
13. Si existe un código QR, identifica únicamente si está presente. No inventes el contenido del QR si no puedes leerlo.
14. No consideres que la presencia de un código QR demuestra por sí sola que el documento es auténtico.

### 4. Validación interna de la CURP

Si se obtuvo una CURP de 18 caracteres, verifica su estructura:

* Posiciones 1–4: iniciales y primera vocal interna de los apellidos/nombre.
* Posiciones 5–10: fecha de nacimiento en formato YYMMDD.
* Posición 11: sexo.
* Posiciones 12–13: entidad federativa de nacimiento.
* Posiciones 14–16: consonantes internas.
* Posiciones 17–18: caracteres diferenciadores.

Compara la CURP con los datos extraídos del documento.

Si existe una inconsistencia entre la CURP y los datos visibles, NO modifiques la CURP. Conserva el valor original y registra la inconsistencia.

### 5. Resultado de validación

Incluye adicionalmente:

{
"validacion": {
"curp_formato_valido": false,
"curp_coincide_nombre": false,
"curp_coincide_fecha_nacimiento": false,
"curp_coincide_sexo": false,
"curp_coincide_entidad": false,
"inconsistencias": []
}
}

Los campos booleanos deben ser `true` únicamente cuando exista evidencia suficiente en el documento.

### 6. Clasificación final

Clasifica el documento utilizando uno de estos valores:

* `CURP_RENAPO_COMPATIBLE`
* `CURP_NO_COMPATIBLE`
* `DOCUMENTO_ILEGIBLE`
* `DOCUMENTO_INCOMPLETO`
* `NO_ES_CURP`

No utilices la palabra "auténtico" ni "original" únicamente basándote en el contenido visual del PDF.

### 7. Formato de respuesta

Devuelve ÚNICAMENTE un objeto JSON válido.

No agregues explicaciones, Markdown, comentarios ni texto antes o después del JSON.

La estructura final debe ser:

{
"tipo_documento": null,
"clasificacion": null,
"documento_compatible_renapo": false,
"curp": null,
"nombre": null,
"primer_apellido": null,
"segundo_apellido": null,
"fecha_nacimiento": null,
"sexo": null,
"entidad_nacimiento": null,
"nacionalidad": null,
"codigo_qr_presente": false,
"codigo_barras_presente": false,
"fecha_emision": null,
"validacion": {
"curp_formato_valido": false,
"curp_coincide_nombre": false,
"curp_coincide_fecha_nacimiento": false,
"curp_coincide_sexo": false,
"curp_coincide_entidad": false,
"inconsistencias": []
},
"observaciones": []
}
