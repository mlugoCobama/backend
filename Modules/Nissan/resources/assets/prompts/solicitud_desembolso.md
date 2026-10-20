Analiza el documento PDF adjunto ("Reporte Pre-Desembolso / Solicitud de Desembolso") y extrae la información necesaria para rellenar un formulario de financiamiento vehicular. Devuelve el resultado estrictamente en formato JSON válido, sin texto adicional ni bloques de Markdown fuera del JSON, utilizando las siguientes claves:

{
  "agencia": "Nombre exacto de la agencia (ej. SONI UNIVERSIDAD S.A. DE C.V.)",
  "no_contrato": "Número de contrato Ekip (ej. 0022211640)",
  "fecha_desembolso": "Fecha de compra virtual o inicio en formato aaaa-mm-dd",
  "vendedor": "",
  "numero_factura": "Número de factura (ej. FA45157 o el de la sección de agencia)",
  "monto_financiar": "Monto total a financiar sin símbolos de moneda (ej. 479095.28)",
  "incentivo_dealer": "Suma de incentivos del dealer (ej. 4790.95)",
  "porcentaje_asesor": "70%",
  "comision_asesor_pesos": "incentivo_dealer" * "porcentaje_asesor",
  "observaciones": "Cualquier nota relevante sobre adeudos, plan piso o referencias del documento"

  "fecha_factura": "Fecha de compra o factura en formato aaaa-mm-dd",
  "cliente": "Nombre completo del cliente (ej. FIDELA GATICA GARCIA)",
  "unidad": "Descripción completa de la unidad (ej. SENTRA SR PLATINUM BITONO CVT 26)",
  "serie": "Número de Chasis o serie del vehículo (ej. 3N1AB9AE5TY263711)",
    
  
  "kit_seguridad": "0",
  "sat_finder": "0",
  "garantia_extendida": "Monto de garantía extendida (ej. 4105.28)",
  "seguro_vf3": "0",
  "accesorios_adicionales": "Monto de accesorios (ej. 0)",
  "comision_mantenimiento": "0",

  "comision_garantia_extendida": "0",
  "comision_udi": "Monto UDI por pagar (ej. 389.93)",
  "comision_vf3": "0",
  "sub_x_des": "0",
  
}
