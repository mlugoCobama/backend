Analiza esta imagen de una Credencial para Votar del INE de México.
Extrae únicamente información que sea visible y legible.
No inventes, completes ni infieras datos.
Si un dato no es visible o no puede leerse con suficiente confianza, devuelve null.

Devuelve únicamente JSON válido con la siguiente estructura:
{
  "nombre": null,
  "apellido_paterno": null,
  "apellido_materno": null,
  "fecha_nacimiento": null,
  "sexo": null,
  "domicilio": {
    "calle": null,
    "numero_exterior": null,
    "numero_interior": null,
    "colonia": null,
    "codigo_postal": null,
    "municipio": null,
    "entidad": null
  },
  "clave_elector": null,
  "curp": null,
  "estado": null,
  "municipio": null,
  "seccion": null,
  "localidad": null,
  "emision": null,
  "vigencia": null,
  "ocr": null
}

Reglas:
- Conserva exactamente letras y números visibles.
- Conserva ceros iniciales.
- No corrijas nombres.
- No confundas CURP con Clave de Elector.
- No confundas OCR con Clave de Elector.
- Las fechas deben utilizar formato YYYY-MM-DD.
- Si no puedes leer un campo, utiliza null.
- Responde solamente con JSON.
